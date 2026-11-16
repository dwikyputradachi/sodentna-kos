<?php

namespace Tests\Feature;

use App\Models\Floor;
use App\Models\Invoice;
use App\Models\MessageLog;
use App\Models\Property;
use App\Models\Rental;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\Tenant;
use App\Models\User;
use App\Support\Settings;
use App\Support\Whatsapp\AutoMessenger;
use App\Support\Whatsapp\BotSender;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WhatsappAutomationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::parse('2026-11-20 10:00:00', 'Asia/Jakarta'));
        config(['whatsapp.bot.url' => 'https://bot.test/api/kirim-pesan', 'whatsapp.bot.key' => 'rahasia-key', 'whatsapp.gap_seconds' => [0, 0]]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function world(): array
    {
        $p = Property::create(['name' => 'Kos A', 'slug' => 'a', 'address' => 'Jl. A']);
        $owner = User::create(['name' => 'Pemilik', 'phone' => '6281200000080', 'role' => 'owner', 'password' => 'rahasia123']);
        $owner->properties()->attach($p->id);
        $f = Floor::create(['property_id' => $p->id, 'name' => 'Lantai 1']);
        $t = RoomType::create(['property_id' => $p->id, 'name' => 'Standar', 'capacity' => 2, 'rate_one' => 1500000, 'rate_two' => 2000000, 'deposit_amount' => 500000]);
        $room = Room::create(['property_id' => $p->id, 'floor_id' => $f->id, 'room_type_id' => $t->id, 'code' => '101', 'status' => 'occupied']);
        $tenant = Tenant::create(['property_id' => $p->id, 'name' => 'Alma Rikhafinata', 'phone' => '6281300000101', 'status' => 'active']);
        $rental = Rental::create(['property_id' => $p->id, 'tenant_id' => $tenant->id, 'room_id' => $room->id, 'mode' => 'monthly', 'occupants' => 1,
            'start_date' => '2026-08-05', 'rate' => 1500000, 'deposit_amount' => 500000, 'billing_anchor_day' => 5, 'status' => 'active']);

        return [$p, $owner, $rental];
    }

    private function invoice(Rental $r, int $offsetDays): Invoice
    {
        $due = Carbon::today('Asia/Jakarta')->addDays($offsetDays);

        return Invoice::create(['property_id' => $r->property_id, 'rental_id' => $r->id, 'number' => 'INV-T-' . random_int(10000, 99999), 'kind' => 'monthly',
            'period_start' => $due->toDateString(), 'period_end' => $due->copy()->addMonth()->subDay()->toDateString(), 'due_date' => $due->toDateString(),
            'rent_amount' => 1500000, 'deposit_amount' => 0, 'total_amount' => 1500000, 'status' => 'unpaid']);
    }

    private function enable(Property $p, array $stages = ['reminder', 'due', 'overdue', 'receipt']): void
    {
        Settings::set($p->id, 'wa_auto', true);
        Settings::set($p->id, 'wa_stages', $stages);
        Settings::set($p->id, 'wa_auto_since', now()->subDay()->toDateTimeString());
    }

    private function ok(): void
    {
        Http::fake(['*' => Http::response(['status' => 'success'], 200)]);
    }

    public function test_bot_sender_posts_the_expected_request(): void
    {
        $this->ok();
        $r = app(BotSender::class)->send('6281300000101', 'Halo');

        $this->assertTrue($r->ok);
        Http::assertSent(fn ($req) => $req->url() === 'https://bot.test/api/kirim-pesan' && $req->hasHeader('x-api-key', 'rahasia-key') && $req['pesan'] === 'Halo' && $req['nomor'] === '6281300000101');
    }

    public function test_bot_sender_reports_every_kind_of_failure_without_leaking_the_key(): void
    {
        // One fake with a sequence: stubs registered earlier would otherwise win over later ones.
        Http::fake(['*' => Http::sequence()
            ->push('boom', 500)
            ->push(['success' => false, 'message' => 'nomor tidak valid'], 200)
            ->pushFailedConnection('timeout')]);

        $a = app(BotSender::class)->send('6281300000101', 'x');
        $this->assertFalse($a->ok);
        $this->assertStringContainsString('HTTP 500', $a->error);
        $this->assertStringNotContainsString('rahasia-key', $a->error);

        $b = app(BotSender::class)->send('6281300000101', 'x');
        $this->assertFalse($b->ok);
        $this->assertStringContainsString('nomor tidak valid', $b->error);

        $c = app(BotSender::class)->send('6281300000101', 'x');
        $this->assertFalse($c->ok);
        $this->assertStringContainsString('Tidak dapat terhubung', $c->error);

        config(['whatsapp.bot.key' => null]);
        $this->assertStringContainsString('belum dikonfigurasi', app(BotSender::class)->send('6281300000101', 'x')->error);
    }

    public function test_nothing_is_sent_unless_the_owner_switched_it_on(): void
    {
        [, , $r] = $this->world();
        $this->invoice($r, 0);
        $this->ok();

        $this->assertSame(['sent' => 0, 'failed' => 0], app(AutoMessenger::class)->run());
        Http::assertNothingSent();
    }

    public function test_due_message_is_sent_once_and_logged(): void
    {
        [$p, , $r] = $this->world();
        $this->invoice($r, 0);
        $this->enable($p);
        $this->ok();

        $this->assertSame(1, app(AutoMessenger::class)->run()['sent']);
        $this->assertSame(0, app(AutoMessenger::class)->run()['sent']); // never twice
        Http::assertSentCount(1);

        $log = MessageLog::firstOrFail();
        $this->assertSame(['due', 'whatsapp_bot', 'sent', '6281300000101'], [$log->kind, $log->channel, $log->status, $log->to_phone]);
        $this->assertStringContainsString('Alma Rikhafinata', $log->body);
    }

    public function test_a_manual_click_counts_as_already_handled(): void
    {
        [$p, , $r] = $this->world();
        $inv = $this->invoice($r, 0);
        $this->enable($p);
        $this->ok();
        MessageLog::create(['property_id' => $p->id, 'invoice_id' => $inv->id, 'kind' => 'due', 'channel' => 'whatsapp_link', 'status' => 'opened', 'to_phone' => '6281300000101', 'body' => 'x']);

        app(AutoMessenger::class)->run();
        Http::assertNothingSent();
    }

    public function test_only_sends_between_8_and_20(): void
    {
        [$p, , $r] = $this->world();
        $this->invoice($r, 0);
        $this->enable($p);
        $this->ok();

        Carbon::setTestNow(Carbon::parse('2026-11-20 21:30:00', 'Asia/Jakarta'));
        $this->assertSame(0, app(AutoMessenger::class)->run()['sent']);
        Carbon::setTestNow(Carbon::parse('2026-11-20 07:59:00', 'Asia/Jakarta'));
        $this->assertSame(0, app(AutoMessenger::class)->run()['sent']);
        $this->assertSame(1, app(AutoMessenger::class)->run(ignoreWindow: true)['sent']);
    }

    public function test_overdue_notice_only_in_the_first_three_days_and_stages_are_respected(): void
    {
        [$p, , $r] = $this->world();
        $this->invoice($r, -2);   // 2 days late -> notice
        $this->invoice($r, -10);  // old arrears -> never auto
        $this->enable($p);
        $this->ok();

        $this->assertSame(1, app(AutoMessenger::class)->run()['sent']);
        $this->assertSame('overdue', MessageLog::firstOrFail()->kind);

        MessageLog::query()->delete();
        $this->enable($p, ['receipt']); // owner only wants receipts
        $this->assertSame(0, app(AutoMessenger::class)->run()['sent']);
    }

    public function test_failures_are_retried_later_and_stop_after_three_attempts(): void
    {
        [$p, , $r] = $this->world();
        $this->invoice($r, 0);
        $this->enable($p);
        Http::fake(['*' => Http::response('down', 503)]);

        $this->assertSame(1, app(AutoMessenger::class)->run()['failed']);
        $this->assertSame(0, app(AutoMessenger::class)->run()['failed']); // too soon
        Carbon::setTestNow(now()->addMinutes(61));
        $this->assertSame(1, app(AutoMessenger::class)->run()['failed']);
        Carbon::setTestNow(now()->addMinutes(61));
        $this->assertSame(1, app(AutoMessenger::class)->run()['failed']);
        Carbon::setTestNow(now()->addMinutes(61));
        $this->assertSame(0, app(AutoMessenger::class)->run()['failed']); // gave up: manual button from here

        Http::assertSentCount(3);
        $this->assertSame(3, MessageLog::where('status', 'failed')->count());
    }

    public function test_receipt_is_sent_right_after_payment_when_enabled(): void
    {
        [$p, $owner, $r] = $this->world();
        $inv = $this->invoice($r, 0);
        $this->enable($p);
        $this->ok();

        $this->actingAs($owner)->post(route('owner.billing.pay', $inv), ['paid_on' => '2026-11-20', 'method' => 'cash'])->assertSessionHas('ok', fn ($m) => str_contains($m, 'terkirim otomatis'));

        $log = MessageLog::firstOrFail();
        $this->assertSame(['receipt', 'sent'], [$log->kind, $log->status]);
        $this->assertStringContainsString('/kuitansi/', $log->body);
    }

    public function test_payment_still_succeeds_when_the_bot_is_down(): void
    {
        [$p, $owner, $r] = $this->world();
        $inv = $this->invoice($r, 0);
        $this->enable($p);
        Http::fake(['*' => Http::response('down', 500)]);

        $this->actingAs($owner)->post(route('owner.billing.pay', $inv), ['paid_on' => '2026-11-20', 'method' => 'cash'])->assertSessionHas('ok', fn ($m) => str_contains($m, 'gagal dikirim otomatis'));

        $this->assertSame('paid', $inv->fresh()->status);
        $this->assertSame('failed', MessageLog::firstOrFail()->status);
        $this->get(route('owner.billing', ['tab' => 'lunas']))->assertSee('Gagal kirim otomatis'); // owner sees it and can use the button
    }

    public function test_without_the_bot_the_receipt_message_stays_manual(): void
    {
        [, $owner, $r] = $this->world();
        $inv = $this->invoice($r, 0);
        $this->ok();

        $this->actingAs($owner)->post(route('owner.billing.pay', $inv), ['paid_on' => '2026-11-20', 'method' => 'cash'])->assertSessionHas('ok', fn ($m) => str_contains($m, 'Anda bisa mengirim kuitansi'));
        Http::assertNothingSent();
    }

    public function test_receipt_paid_outside_hours_goes_out_the_next_morning_and_old_ones_never(): void
    {
        [$p, $owner, $r] = $this->world();
        $before = $this->invoice($r, -30);
        $after = $this->invoice($r, 0);
        $this->ok();
        $this->actingAs($owner);

        // Paid while the bot was still off: must NOT be sent when the bot is switched on later.
        $this->post(route('owner.billing.pay', $before), ['paid_on' => '2026-11-20', 'method' => 'cash']);
        Carbon::setTestNow(now()->addMinutes(5));
        $this->put(route('owner.whatsapp.update'), ['wa_auto' => 1, 'stages' => ['receipt']])->assertSessionHasNoErrors();
        $this->assertSame(0, app(AutoMessenger::class)->run()['sent']);

        // Paid at night: waits for the morning window.
        Carbon::setTestNow(Carbon::parse('2026-11-20 21:30:00', 'Asia/Jakarta'));
        $this->post(route('owner.billing.pay', $after), ['paid_on' => '2026-11-20', 'method' => 'transfer']);
        Http::assertNothingSent();
        Carbon::setTestNow(Carbon::parse('2026-11-21 09:00:00', 'Asia/Jakarta'));
        $this->assertSame(1, app(AutoMessenger::class)->run()['sent']);
        Http::assertSentCount(1);
    }

    public function test_the_artisan_command_runs_the_same_logic(): void
    {
        [$p, , $r] = $this->world();
        $this->invoice($r, 0);
        $this->enable($p);
        $this->ok();

        $this->artisan('billing:remind')->expectsOutputToContain('Terkirim: 1, gagal: 0.')->assertExitCode(0);
    }

    public function test_settings_page_toggle_test_message_and_access(): void
    {
        [$p, $owner] = $this->world();
        $this->ok();
        $this->actingAs($owner);

        $this->get(route('owner.whatsapp'))->assertOk()->assertSee('Tombol manual');
        $this->put(route('owner.whatsapp.update'), ['wa_auto' => 1, 'stages' => ['due', 'receipt']])->assertSessionHasNoErrors();
        $this->assertTrue(Settings::bool($p->id, 'wa_auto'));
        $this->assertSame(['due', 'receipt'], Settings::json($p->id, 'wa_stages'));

        $this->post(route('owner.whatsapp.test'), ['phone' => '0812-3456-7890'])->assertSessionHas('ok');
        $this->assertSame(['test', 'sent', '6281234567890'], [MessageLog::firstOrFail()->kind, MessageLog::firstOrFail()->status, MessageLog::firstOrFail()->to_phone]);
        $this->post(route('owner.whatsapp.test'), ['phone' => 'abc'])->assertSessionHasErrors('phone');

        $this->put(route('owner.whatsapp.update'), [])->assertSessionHasNoErrors();
        $this->assertFalse(Settings::bool($p->id, 'wa_auto'));
        $this->get(route('owner.whatsapp'))->assertSee('Pesan uji');
    }

    public function test_cannot_switch_on_without_bot_configuration_and_tenants_are_locked_out(): void
    {
        [$p, $owner] = $this->world();
        config(['whatsapp.bot.url' => null, 'whatsapp.bot.key' => null]);

        $this->actingAs($owner)->put(route('owner.whatsapp.update'), ['wa_auto' => 1])->assertSessionHasErrors('wa_auto');
        $this->assertFalse(Settings::bool($p->id, 'wa_auto'));

        auth()->logout();
        $t = User::create(['name' => 'T', 'phone' => '6281200000090', 'role' => 'tenant', 'password' => 'rahasia123']);
        $this->actingAs($t)->get(route('owner.whatsapp'))->assertForbidden();
    }

    public function test_settings_are_per_property(): void
    {
        [$p] = $this->world();
        $other = Property::create(['name' => 'Kos B', 'slug' => 'b']);
        $this->enable($p);

        $this->assertTrue(AutoMessenger::enabled($p->id));
        $this->assertFalse(AutoMessenger::enabled($other->id));
    }
}
