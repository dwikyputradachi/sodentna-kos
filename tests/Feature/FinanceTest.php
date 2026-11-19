<?php

namespace Tests\Feature;

use App\Actions\RecordPayment;
use App\Models\Expense;
use App\Models\Floor;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Property;
use App\Models\Receipt;
use App\Models\Rental;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\Tenant;
use App\Models\User;
use App\Support\Finance\Ledger;
use App\Support\Settings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use ZipArchive;

class FinanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::parse('2026-11-20 10:00:00', 'Asia/Jakarta'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function world(): array
    {
        $p = Property::create(['name' => 'Kos A', 'slug' => 'a', 'address' => 'Jl. A']);
        $owner = User::create(['name' => 'Pemilik', 'phone' => '6281200000100', 'role' => 'owner', 'password' => 'rahasia123']);
        $owner->properties()->attach($p->id);
        $f = Floor::create(['property_id' => $p->id, 'name' => 'Lantai 1']);
        $t = RoomType::create(['property_id' => $p->id, 'name' => 'Standar', 'capacity' => 2, 'rate_one' => 1500000, 'rate_two' => 2000000, 'deposit_amount' => 500000]);
        $room = Room::create(['property_id' => $p->id, 'floor_id' => $f->id, 'room_type_id' => $t->id, 'code' => '101', 'status' => 'occupied']);
        $tenant = Tenant::create(['property_id' => $p->id, 'name' => 'Alma Rikhafinata', 'phone' => '6281300000101', 'status' => 'active']);
        $rental = Rental::create(['property_id' => $p->id, 'tenant_id' => $tenant->id, 'room_id' => $room->id, 'mode' => 'monthly', 'occupants' => 1,
            'start_date' => '2026-08-05', 'rate' => 1500000, 'deposit_amount' => 500000, 'billing_anchor_day' => 5, 'status' => 'active']);

        return [$p, $owner, $rental];
    }

    private function inv(Rental $r, string $due, int $rent = 1500000, int $deposit = 0, string $kind = 'monthly'): Invoice
    {
        return Invoice::create(['property_id' => $r->property_id, 'rental_id' => $r->id, 'number' => 'INV-T-' . random_int(10000, 99999), 'kind' => $kind,
            'period_start' => $due, 'period_end' => Carbon::parse($due)->addMonth()->subDay()->toDateString(), 'due_date' => $due,
            'rent_amount' => $rent, 'deposit_amount' => $deposit, 'total_amount' => $rent + $deposit, 'status' => 'unpaid']);
    }

    private function pay(Invoice $i, string $on, User $by, string $method = 'transfer'): Payment
    {
        return app(RecordPayment::class)->handle($i, ['paid_on' => $on, 'method' => $method], $by);
    }

    private function november(Property $p): array
    {
        return Ledger::statement($p->id, Carbon::parse('2026-11-01', 'Asia/Jakarta'), Carbon::parse('2026-11-30', 'Asia/Jakarta'));
    }

    public function test_rent_and_deposit_are_kept_apart(): void
    {
        [$p, $owner, $r] = $this->world();
        $this->pay($this->inv($r, '2026-11-05', 1500000, 500000, 'first'), '2026-11-05', $owner);

        $st = $this->november($p);
        $this->assertSame([1500000, 500000, 2000000, 1500000, 2000000], [$st['totals']['rent'], $st['totals']['deposit'], $st['totals']['in'], $st['totals']['operating'], $st['closing']]);
        $this->assertSame(['rent', 'deposit'], $st['entries']->pluck('kind')->all());
    }

    public function test_expenses_and_running_balance_with_opening_balance(): void
    {
        [$p, $owner, $r] = $this->world();
        Settings::set($p->id, 'opening_balance', '1000000');
        Settings::set($p->id, 'opening_date', '2026-11-01');
        $this->pay($this->inv($r, '2026-11-05'), '2026-11-05', $owner);

        $this->actingAs($owner)->post(route('owner.expenses.store'), ['rid' => 'a1', 'spent_on' => '2026-11-06', 'category' => 'Kebersihan', 'amount' => 300000, 'method' => 'cash', 'description' => 'Kebersihan bulanan'])
            ->assertSessionHasNoErrors();

        $st = $this->november($p);
        $this->assertSame([1000000, 2200000], [$st['opening'], $st['closing']]);
        $this->assertSame([2500000, 2200000], $st['entries']->pluck('balance')->all());
        $this->assertSame([300000, 1200000], [$st['totals']['expense'], $st['totals']['operating']]);
    }

    public function test_history_before_the_opening_date_is_not_counted_twice(): void
    {
        [$p, $owner, $r] = $this->world();
        $this->pay($this->inv($r, '2026-10-05'), '2026-10-20', $owner);
        $this->pay($this->inv($r, '2026-11-05'), '2026-11-03', $owner);
        Settings::set($p->id, 'opening_balance', '500000');
        Settings::set($p->id, 'opening_date', '2026-11-01');

        $st = $this->november($p);
        $this->assertSame([500000, 2000000], [$st['opening'], $st['closing']]); // October payment is already inside the opening balance
        $this->assertCount(1, $st['entries']);
    }

    public function test_the_same_expense_form_cannot_post_twice(): void
    {
        [, $owner] = $this->world();
        $this->actingAs($owner);
        $payload = ['rid' => 'dbl-1', 'spent_on' => '2026-11-06', 'category' => 'Internet', 'amount' => 350000, 'method' => 'transfer'];

        $this->post(route('owner.expenses.store'), $payload)->assertSessionHasNoErrors();
        $this->post(route('owner.expenses.store'), $payload)->assertSessionHas('ok', fn ($m) => str_contains($m, 'sudah tercatat'));
        $this->assertSame(1, Expense::count());
    }

    public function test_expense_validation_and_cancellation(): void
    {
        [$p, $owner] = $this->world();
        $this->actingAs($owner);
        $ok = ['spent_on' => '2026-11-06', 'category' => 'Perlengkapan', 'amount' => 250000, 'method' => 'cash', 'description' => 'Lampu'];

        $this->post(route('owner.expenses.store'), ['category' => 'Bukan kategori'] + $ok)->assertSessionHasErrors('category');
        $this->post(route('owner.expenses.store'), ['spent_on' => '2026-11-25'] + $ok)->assertSessionHasErrors('spent_on');
        $this->post(route('owner.expenses.store'), ['amount' => 0] + $ok)->assertSessionHasErrors('amount');
        $this->assertSame(0, Expense::count());

        $this->post(route('owner.expenses.store'), $ok);
        $e = Expense::firstOrFail();
        $this->post(route('owner.expenses.void', $e), [])->assertSessionHasErrors('reason');
        $this->post(route('owner.expenses.void', $e), ['reason' => 'Salah catat'])->assertSessionHas('ok');

        $st = $this->november($p);
        $this->assertSame(0, $st['totals']['expense']);                       // not counted
        $this->assertTrue($st['entries']->first()['void']);                   // but kept for audit
        $this->post(route('owner.expenses.void', $e), ['reason' => 'lagi'])->assertSessionHas('err');
    }

    public function test_expense_photo_is_stored_privately_for_the_owner_only(): void
    {
        Storage::fake();
        [, $owner] = $this->world();

        $this->actingAs($owner)->post(route('owner.expenses.store'), ['spent_on' => '2026-11-06', 'category' => 'Perlengkapan', 'amount' => 100000, 'method' => 'cash',
            'photo' => UploadedFile::fake()->create('struk.pdf', 50, 'application/pdf')])->assertSessionHasNoErrors();

        $e = Expense::firstOrFail();
        Storage::assertExists($e->receipt_path);
        $this->get(route('owner.expenses.file', $e))->assertOk();
        auth()->logout();
        $this->get(route('owner.expenses.file', $e))->assertRedirect(route('login'));
    }

    public function test_cancelling_a_payment_reopens_the_invoice_and_keeps_the_audit_trail(): void
    {
        [$p, $owner, $r] = $this->world();
        $inv = $this->inv($r, '2026-11-05');
        $this->actingAs($owner);

        $this->post(route('owner.billing.pay', $inv), ['paid_on' => '2026-11-05', 'method' => 'cash']);
        $first = Payment::firstOrFail();
        $token = Receipt::firstOrFail()->token;

        $this->post(route('owner.payments.void', $first), [])->assertSessionHasErrors('reason');
        $this->post(route('owner.payments.void', $first), ['reason' => 'Salah nominal'])->assertSessionHas('ok');
        $this->post(route('owner.payments.void', $first), ['reason' => 'lagi'])->assertSessionHas('err');

        $this->assertSame('unpaid', $inv->fresh()->status);
        $this->assertNotNull($first->fresh()->voided_at);
        $this->assertSame(0, $this->november($p)['totals']['rent']);
        auth()->logout();
        $this->get('/kuitansi/' . $token)->assertOk()->assertSee('DIBATALKAN');

        $this->actingAs($owner)->post(route('owner.billing.pay', $inv), ['paid_on' => '2026-11-06', 'method' => 'transfer'])->assertSessionHasNoErrors();
        $this->assertSame(2, Payment::count());
        $this->assertSame(2, Receipt::count());
        $this->assertSame(1500000, $this->november($p)['totals']['rent']);                 // counted once
        $this->assertNotSame($first->id, $inv->fresh()->payment->id);                      // the live payment is the new one
    }

    public function test_report_page_and_excel_export_match_the_ledger(): void
    {
        [$p, $owner, $r] = $this->world();
        $this->pay($this->inv($r, '2026-11-05', 1500000, 500000, 'first'), '2026-11-05', $owner);
        Expense::create(['property_id' => $p->id, 'spent_on' => '2026-11-06', 'category' => 'Kebersihan', 'amount' => 300000, 'method' => 'cash', 'description' => 'Kebersihan']);
        $this->actingAs($owner);

        $this->get(route('owner.reports', ['bulan' => '2026-11']))->assertOk()->assertSee('Hasil operasional')->assertSee('Rp 1.200.000')->assertSee('Rp 1.700.000'); // operating, closing
        $this->get(route('owner.reports.print', ['bulan' => '2026-11']))->assertOk()->assertSee('Laporan Keuangan');

        if (! class_exists(ZipArchive::class)) $this->markTestSkipped('Ekstensi zip PHP tidak aktif.');
        $res = $this->get(route('owner.reports.excel', ['bulan' => '2026-11']))->assertOk();
        $zip = new ZipArchive();
        $this->assertTrue($zip->open($res->baseResponse->getFile()->getPathname()));
        $summary = $zip->getFromName('xl/worksheets/sheet1.xml');
        $ledger = $zip->getFromName('xl/worksheets/sheet2.xml');
        $this->assertStringContainsString('Pendapatan sewa', $summary);
        $this->assertStringContainsString('<v>1200000</v>', $summary);   // operating result is a real number Excel can sum
        $this->assertStringContainsString('Sewa — Alma Rikhafinata', $ledger);
        $this->assertStringContainsString('<v>300000</v>', $ledger);
    }

    public function test_documents_page_lists_receipts_and_searches(): void
    {
        [, $owner, $r] = $this->world();
        $pay = $this->pay($this->inv($r, '2026-11-05'), '2026-11-05', $owner);
        $this->actingAs($owner);

        $this->get(route('owner.documents'))->assertOk()->assertSee($pay->receipt->number)->assertSee('Alma Rikhafinata')->assertSee('Kirim via WhatsApp');
        $this->get(route('owner.documents', ['q' => 'Alma']))->assertSee($pay->receipt->number);
        $this->get(route('owner.documents', ['q' => 'tidak-ada']))->assertSee('Belum ada kuitansi');
    }

    public function test_dashboard_uses_real_numbers(): void
    {
        [$p, $owner, $r] = $this->world();
        $this->inv($r, '2026-11-17');                                   // 3 days late
        $this->inv($r, '2026-11-22');                                   // H-2
        $this->pay($this->inv($r, '2026-11-05'), '2026-11-05', $owner);

        $this->actingAs($owner)->get(route('owner.dashboard'))->assertOk()->assertSee('Perlu ditagih')->assertSee('Alma Rikhafinata')->assertSee('Terlambat')->assertSee('H-2')
            ->assertSee('Saldo kas')->assertSee('Rp 1.500.000');
    }

    public function test_period_tabs_and_search(): void
    {
        [$p, $owner, $r] = $this->world();
        $this->pay($this->inv($r, '2026-11-05'), '2026-11-05', $owner);
        Expense::create(['property_id' => $p->id, 'spent_on' => '2026-11-06', 'category' => 'Kebersihan', 'amount' => 300000, 'method' => 'cash', 'description' => 'Sapu dan pel']);
        $this->actingAs($owner);

        $this->get(route('owner.finance', ['tahun' => 2026]))->assertOk()->assertSee('Tahun 2026');
        $this->get(route('owner.finance', ['bulan' => '2026-13']))->assertOk()->assertSee('November 2026'); // invalid month falls back to this month
        $this->get(route('owner.finance', ['tab' => 'keluar']))->assertSee('Sapu dan pel')->assertDontSee('Sewa — Alma');
        $this->get(route('owner.finance', ['tab' => 'masuk']))->assertSee('Sewa — Alma')->assertDontSee('Sapu dan pel');
        $this->get(route('owner.finance', ['q' => 'sapu']))->assertSee('Sapu dan pel')->assertDontSee('Sewa — Alma');
    }

    public function test_finance_is_isolated_per_property_and_locked_for_tenants(): void
    {
        [, $owner] = $this->world();
        $b = Property::create(['name' => 'Kos B', 'slug' => 'b']);
        $other = Expense::create(['property_id' => $b->id, 'spent_on' => '2026-11-06', 'category' => 'Lainnya', 'amount' => 99000, 'method' => 'cash', 'description' => 'Rahasia Kos B']);

        $this->actingAs($owner)->get(route('owner.finance'))->assertOk()->assertDontSee('Rahasia Kos B');
        $this->post(route('owner.expenses.void', $other), ['reason' => 'x'])->assertNotFound();

        auth()->logout();
        $t = User::create(['name' => 'T', 'phone' => '6281200000110', 'role' => 'tenant', 'password' => 'rahasia123']);
        $this->actingAs($t);
        foreach (['owner.finance', 'owner.reports', 'owner.documents', 'owner.dashboard'] as $route) {
            $this->get(route($route))->assertForbidden();
        }
    }
}
