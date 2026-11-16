<?php

namespace Tests\Feature;

use App\Actions\GenerateInvoices;
use App\Models\Floor;
use App\Models\Invoice;
use App\Models\MessageLog;
use App\Models\Payment;
use App\Models\Property;
use App\Models\Receipt;
use App\Models\Rental;
use App\Models\RentalApplication;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\Tenant;
use App\Models\User;
use App\Support\BillingSchedule;
use App\Support\InvoiceState;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BillingTest extends TestCase
{
    use RefreshDatabase;

    private function world(): array
    {
        $p = Property::create(['name' => 'Kos A', 'slug' => 'a', 'address' => 'Jl. A']);
        $owner = User::create(['name' => 'Pemilik', 'phone' => '6281200000060', 'role' => 'owner', 'password' => 'rahasia123']);
        $owner->properties()->attach($p->id);
        $f = Floor::create(['property_id' => $p->id, 'name' => 'Lantai 1']);
        $t = RoomType::create(['property_id' => $p->id, 'name' => 'Standar', 'capacity' => 2, 'rate_one' => 1500000, 'rate_two' => 2000000, 'deposit_amount' => 500000]);
        $room = Room::create(['property_id' => $p->id, 'floor_id' => $f->id, 'room_type_id' => $t->id, 'code' => '101', 'status' => 'occupied']);

        return [$p, $owner, $room, $t];
    }

    private function rental(Property $p, Room $room, string $name = 'Alma', string $phone = '6281300000101', array $o = []): Rental
    {
        $tenant = Tenant::create(['property_id' => $p->id, 'name' => $name, 'phone' => $phone, 'id_number' => '3171000000000001', 'status' => 'active']);

        return Rental::create($o + ['property_id' => $p->id, 'tenant_id' => $tenant->id, 'room_id' => $room->id, 'mode' => 'monthly', 'occupants' => 1,
            'start_date' => '2026-08-05', 'rate' => 1500000, 'deposit_amount' => 500000, 'billing_anchor_day' => 5, 'status' => 'active']);
    }

    private function invoice(Rental $r, int $offsetDays, array $o = []): Invoice
    {
        $due = Carbon::today('Asia/Jakarta')->addDays($offsetDays);

        return Invoice::create($o + ['property_id' => $r->property_id, 'rental_id' => $r->id, 'number' => 'INV-T-' . random_int(10000, 99999), 'kind' => 'monthly',
            'period_start' => $due->toDateString(), 'period_end' => $due->copy()->addMonth()->subDay()->toDateString(), 'due_date' => $due->toDateString(),
            'rent_amount' => 1500000, 'deposit_amount' => 0, 'total_amount' => 1500000, 'status' => 'unpaid']);
    }

    public function test_due_dates_follow_the_move_in_day_even_for_short_months(): void
    {
        $this->assertSame('2027-02-28', BillingSchedule::nextAfter(Carbon::parse('2027-01-31'), 31)->toDateString());
        $this->assertSame('2027-03-31', BillingSchedule::nextAfter(Carbon::parse('2027-02-28'), 31)->toDateString());
        $this->assertSame('2027-02-28', BillingSchedule::nextAfter(Carbon::parse('2027-01-30'), 30)->toDateString());
        $this->assertSame('2028-02-29', BillingSchedule::nextAfter(Carbon::parse('2028-01-31'), 31)->toDateString()); // leap year

        $today = Carbon::parse('2026-11-14', 'Asia/Jakarta');
        $this->assertSame('2026-12-01', BillingSchedule::upcoming($today, 1)->toDateString());
        $this->assertSame('2026-12-14', BillingSchedule::upcoming($today, 14)->toDateString()); // strictly after today
        $this->assertSame('2026-11-20', BillingSchedule::upcoming($today, 20)->toDateString());
    }

    public function test_generator_creates_each_period_once_and_advances_the_schedule(): void
    {
        [$p, , $room] = $this->world();
        $due = Carbon::today('Asia/Jakarta')->addDays(3);
        $r = $this->rental($p, $room, 'Alma', '6281300000101', ['next_due_date' => $due->toDateString(), 'billing_anchor_day' => $due->day]);

        $this->assertSame(1, app(GenerateInvoices::class)->run());
        $this->assertSame(0, app(GenerateInvoices::class)->run()); // idempotent

        $inv = Invoice::firstOrFail();
        $this->assertSame($due->toDateString(), $inv->due_date->toDateString());
        $this->assertSame(1500000, (int) $inv->total_amount);
        $this->assertStringStartsWith('INV-', $inv->number);
        $this->assertSame(BillingSchedule::nextAfter($due, $due->day)->toDateString(), $r->fresh()->next_due_date->toDateString());
    }

    public function test_generator_catches_up_missed_months(): void
    {
        [$p, , $room] = $this->world();
        $due = Carbon::today('Asia/Jakarta')->subDays(40);
        $this->rental($p, $room, 'Alma', '6281300000101', ['next_due_date' => $due->toDateString(), 'billing_anchor_day' => $due->day]);

        $this->assertSame(2, app(GenerateInvoices::class)->run());
    }

    public function test_invoice_states(): void
    {
        [$p, , $room] = $this->world();
        $r = $this->rental($p, $room);

        $this->assertSame('Belum Jatuh Tempo', InvoiceState::of($this->invoice($r, 5))['label']);
        $this->assertSame('H-2', InvoiceState::of($this->invoice($r, 2))['label']);
        $this->assertSame('H-2', InvoiceState::of($this->invoice($r, 1))['label']);
        $this->assertSame('reminder', InvoiceState::of($this->invoice($r, 2, ['kind' => 'daily']))['action']);
        $this->assertSame('Jatuh Tempo', InvoiceState::of($this->invoice($r, 0))['label']);
        $this->assertSame('Terlambat', InvoiceState::of($this->invoice($r, -3))['label']);
        $this->assertSame('Lunas', InvoiceState::of($this->invoice($r, -9, ['status' => 'paid']))['label']);
        $this->assertNull(InvoiceState::of($this->invoice($r, -12, ['status' => 'void']))['action']);
    }

    public function test_approving_an_application_creates_the_first_invoice_with_deposit(): void
    {
        [$p, $owner, $room, $t] = $this->world();
        $room->update(['status' => 'available']);
        $app = RentalApplication::create([
            'property_id' => $p->id, 'code' => 'SDN-11111', 'room_type_id' => $t->id, 'room_id' => $room->id, 'mode' => 'monthly', 'occupants' => 2,
            'start_date' => '2030-01-31', 'payment_method' => 'transfer', 'name' => 'Budi', 'phone' => '6281234567890', 'birth_place' => 'Batam', 'birth_date' => '2000-01-01',
            'address' => 'Jl. A', 'id_number' => '3171000000000001', 'job' => 'Karyawan', 'institution' => 'PT A', 'emergency_name' => 'Ibu', 'emergency_relation' => 'Ibu',
            'emergency_phone' => '6281200000099', 'emergency_address' => 'Jl. B', 'id_doc_path' => 'identitas/x.jpg', 'consented_at' => now(), 'status' => 'pending',
        ]);

        $this->actingAs($owner)->post(route('owner.applications.approve', $app), [])->assertSessionHasNoErrors();

        $inv = Invoice::firstOrFail();
        $this->assertSame('first', $inv->kind);
        $this->assertSame(2500000, (int) $inv->total_amount); // 2 persons: Rp2.000.000 + Rp500.000 deposit
        $this->assertSame(500000, (int) $inv->deposit_amount);
        $this->assertSame('2030-02-28', Rental::firstOrFail()->next_due_date->toDateString()); // move-in on the 31st
    }

    public function test_recording_a_payment_creates_receipt_once(): void
    {
        [$p, $owner, $room] = $this->world();
        $inv = $this->invoice($this->rental($p, $room), 0, ['kind' => 'first', 'rent_amount' => 1500000, 'deposit_amount' => 500000, 'total_amount' => 2000000]);
        $this->actingAs($owner);
        $today = Carbon::today('Asia/Jakarta')->toDateString();

        $this->post(route('owner.billing.pay', $inv), ['paid_on' => $today, 'method' => 'cash'])->assertSessionHasNoErrors();
        $this->post(route('owner.billing.pay', $inv), ['paid_on' => $today, 'method' => 'cash'])->assertSessionHasErrors('paid_on'); // double click

        $this->assertSame('paid', $inv->fresh()->status);
        $this->assertDatabaseCount('payments', 1);
        $this->assertDatabaseCount('receipts', 1);
        $pay = Payment::firstOrFail();
        $this->assertSame([2000000, 1500000, 500000], [(int) $pay->amount, (int) $pay->rent_amount, (int) $pay->deposit_amount]); // deposit kept apart from rent
        $this->assertMatchesRegularExpression('#^\d{3}/KSA/\d{2}/\d{4}$#', Receipt::firstOrFail()->number);
    }

    public function test_payment_date_cannot_be_in_the_future(): void
    {
        [$p, $owner, $room] = $this->world();
        $inv = $this->invoice($this->rental($p, $room), 0);

        $this->actingAs($owner)->post(route('owner.billing.pay', $inv), ['paid_on' => Carbon::today('Asia/Jakarta')->addDays(3)->toDateString(), 'method' => 'transfer'])->assertSessionHasErrors('paid_on');
        $this->assertDatabaseCount('payments', 0);
    }

    public function test_cancelled_invoice_cannot_be_paid_and_paid_one_cannot_be_cancelled(): void
    {
        [$p, $owner, $room] = $this->world();
        $r = $this->rental($p, $room);
        $a = $this->invoice($r, 3);
        $b = $this->invoice($r, -4);
        $today = Carbon::today('Asia/Jakarta')->toDateString();
        $this->actingAs($owner);

        $this->post(route('owner.billing.void', $a), ['reason' => 'Salah tagih'])->assertSessionHas('ok');
        $this->post(route('owner.billing.pay', $a), ['paid_on' => $today, 'method' => 'cash'])->assertSessionHasErrors('paid_on');

        $this->post(route('owner.billing.pay', $b), ['paid_on' => $today, 'method' => 'cash']);
        $this->post(route('owner.billing.void', $b), ['reason' => 'Coba'])->assertSessionHas('err');
        $this->assertSame('paid', $b->fresh()->status);
    }

    public function test_whatsapp_button_logs_the_click_and_only_allows_the_current_stage(): void
    {
        [$p, $owner, $room] = $this->world();
        $r = $this->rental($p, $room, 'Alma Rikhafinata', '081300000101');
        $due = $this->invoice($r, 0);
        $this->actingAs($owner);

        $this->post(route('owner.billing.send', [$due, 'due']))->assertRedirectContains('https://wa.me/6281300000101?text=')->assertRedirectContains('Alma');
        $this->post(route('owner.billing.send', [$due, 'overdue']))->assertStatus(422);   // wrong stage
        $this->post(route('owner.billing.send', [$due, 'receipt']))->assertStatus(422);   // not paid yet

        $log = MessageLog::firstOrFail();
        $this->assertSame(['due', 'opened', 'whatsapp_link', '6281300000101'], [$log->kind, $log->status, $log->channel, $log->to_phone]);
        $this->assertStringContainsString('Alma Rikhafinata', $log->body);
    }

    public function test_receipt_link_page_is_public_but_private_data_stays_hidden(): void
    {
        [$p, $owner, $room] = $this->world();
        $inv = $this->invoice($this->rental($p, $room, 'Alma Rikhafinata', '6281300000101'), 0);
        $this->actingAs($owner)->post(route('owner.billing.pay', $inv), ['paid_on' => Carbon::today('Asia/Jakarta')->toDateString(), 'method' => 'transfer']);
        $receipt = Receipt::firstOrFail();

        $this->post(route('owner.billing.send', [$inv, 'receipt']))->assertRedirectContains('wa.me');
        $this->assertStringContainsString('/kuitansi/' . $receipt->token, MessageLog::firstOrFail()->body);

        auth()->logout();
        $this->get('/kuitansi/' . $receipt->token)->assertOk()->assertSee('KUITANSI PEMBAYARAN')->assertSee($receipt->number)->assertSee('Alma Rikhafinata')
            ->assertDontSee('6281300000101')->assertDontSee('3171000000000001');
        $this->get('/kuitansi/token-yang-salah')->assertNotFound();
    }

    public function test_billing_page_shows_states_and_generates_upcoming_invoices(): void
    {
        [$p, $owner, $room] = $this->world();
        $due = Carbon::today('Asia/Jakarta')->addDays(2);
        $this->rental($p, $room, 'Alma', '6281300000101', ['next_due_date' => $due->toDateString(), 'billing_anchor_day' => $due->day]);

        $this->actingAs($owner)->get(route('owner.billing'))->assertOk()->assertSee('H-2')->assertSee('Kirim Pengingat')->assertSee('Catat pembayaran');
        $this->assertDatabaseCount('invoices', 1);
    }

    public function test_manual_tenant_with_overdue_due_date_gets_an_overdue_invoice(): void
    {
        [$p, $owner, $room] = $this->world();
        $room->update(['status' => 'available']);

        $this->actingAs($owner)->post(route('owner.tenants.store'), [
            'name' => 'Ahmad', 'phone' => '081211112222', 'room_id' => $room->id, 'occupants' => 1, 'start_date' => '2026-08-01', 'rate' => 1500000, 'deposit_amount' => 500000,
            'next_due_date' => Carbon::today('Asia/Jakarta')->subDays(5)->toDateString(),
        ])->assertSessionHasNoErrors();

        $this->get(route('owner.billing'))->assertSee('Terlambat')->assertSee('Kirim Pengingat Keterlambatan');
    }

    public function test_other_property_invoice_is_not_reachable(): void
    {
        [, $owner] = $this->world();
        $b = Property::create(['name' => 'Kos B', 'slug' => 'b']);
        $f = Floor::create(['property_id' => $b->id, 'name' => 'L1']);
        $t = RoomType::create(['property_id' => $b->id, 'name' => 'B', 'capacity' => 1, 'rate_one' => 1]);
        $room = Room::create(['property_id' => $b->id, 'floor_id' => $f->id, 'room_type_id' => $t->id, 'code' => '901', 'status' => 'occupied']);
        $other = $this->invoice($this->rental($b, $room, 'Orang Lain', '6281999999999'), 0);
        $this->actingAs($owner);

        $this->post(route('owner.billing.pay', $other), ['paid_on' => Carbon::today('Asia/Jakarta')->toDateString(), 'method' => 'cash'])->assertNotFound();
        $this->post(route('owner.billing.send', [$other, 'due']))->assertNotFound();
        $this->get(route('owner.billing', ['tab' => 'semua']))->assertDontSee('Orang Lain');
    }

    public function test_tenant_portal_shows_only_own_bills_and_receipts(): void
    {
        [$p, $owner, $room, $t] = $this->world();
        $mine = $this->rental($p, $room, 'Alma', '6281300000101');
        $room2 = Room::create(['property_id' => $p->id, 'floor_id' => $room->floor_id, 'room_type_id' => $t->id, 'code' => 'K-77', 'status' => 'occupied']);
        $theirs = $this->rental($p, $room2, 'Budi Santoso', '6281300000202');
        $user = User::create(['name' => 'Alma', 'phone' => '6281300000101', 'role' => 'tenant', 'password' => 'rahasia123']);
        $mine->tenant->update(['user_id' => $user->id]);

        $a = $this->invoice($mine, 3, ['number' => 'INV-MINE-0001']);
        $this->invoice($theirs, 3, ['number' => 'INV-THEIRS-0001']);
        $paid = $this->invoice($mine, -30, ['number' => 'INV-MINE-0002']);
        $this->actingAs($owner)->post(route('owner.billing.pay', $paid), ['paid_on' => Carbon::today('Asia/Jakarta')->toDateString(), 'method' => 'cash']);
        auth()->logout();

        $this->actingAs($user)->get('/penghuni/tagihan')->assertOk()->assertSee('INV-MINE-0001')->assertDontSee('INV-THEIRS-0001')->assertDontSee('Budi');
        $this->get('/penghuni/riwayat-pembayaran')->assertOk()->assertSee(Receipt::firstOrFail()->number);
        $this->get('/penghuni')->assertOk()->assertSee('Rp 1.500.000');
    }

    public function test_tenant_role_cannot_open_owner_billing(): void
    {
        $this->world();
        $u = User::create(['name' => 'T', 'phone' => '6281200000070', 'role' => 'tenant', 'password' => 'rahasia123']);
        $this->actingAs($u)->get(route('owner.billing'))->assertForbidden();
    }
}
