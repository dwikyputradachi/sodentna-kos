<?php

namespace App\Support\Whatsapp;

use App\Models\Invoice;
use App\Models\MessageLog;
use App\Models\Payment;
use App\Models\Property;
use App\Support\BillingSchedule;
use App\Support\InvoiceState;
use App\Support\Owner\Billing;
use App\Support\Settings;
use Carbon\Carbon;

/**
 * Automatic WhatsApp messages (reminder H-2, due, overdue, receipt).
 * Safety rules: off by default; one message per invoice+stage; an owner's manual click counts as "sent";
 * 3 failed attempts max, 60 minutes apart; only 08:00-20:00; overdue notice only in the first 3 days late.
 */
class AutoMessenger
{
    public const MAX_ATTEMPTS = 3;
    public const RETRY_AFTER_MIN = 60;
    public const OVERDUE_WINDOW_DAYS = 3;
    public const RECEIPT_LOOKBACK_DAYS = 3;
    public const STAGES = ['reminder' => 'Pengingat H-2', 'due' => 'Tagihan jatuh tempo', 'overdue' => 'Pengingat keterlambatan', 'receipt' => 'Kuitansi setelah lunas'];

    public function __construct(private BotSender $sender)
    {
    }

    public static function enabled(int $propertyId): bool
    {
        return Settings::bool($propertyId, 'wa_auto') && BotSender::configured();
    }

    public static function stages(int $propertyId): array
    {
        return Settings::json($propertyId, 'wa_stages', ['reminder', 'due', 'receipt']);
    }

    public static function inWindow(?Carbon $now = null): bool
    {
        $h = (int) ($now ?? Carbon::now())->copy()->timezone(BillingSchedule::TZ)->format('G');

        return $h >= (int) config('whatsapp.window.start', 8) && $h < (int) config('whatsapp.window.end', 20);
    }

    /** @return array{sent:int,failed:int} */
    public function run(bool $ignoreWindow = false, ?int $propertyId = null): array
    {
        $sent = $failed = 0;
        if (! $ignoreWindow && ! self::inWindow()) return compact('sent', 'failed');

        $budget = (int) config('whatsapp.max_per_run', 10);
        $props = Property::where('is_active', true)->when($propertyId, fn ($q) => $q->whereKey($propertyId))->get();

        foreach ($props as $p) {
            if (! self::enabled($p->id)) continue;

            foreach ($this->candidates($p->id) as [$invoice, $kind]) {
                if ($budget <= 0) break 2;

                $ok = $this->deliver($invoice, $kind);
                if ($ok === null) continue;

                $budget--;
                $ok ? $sent++ : $failed++;
                $gap = (array) config('whatsapp.gap_seconds', [0, 0]);
                if (($gap[1] ?? 0) > 0) usleep(random_int((int) $gap[0], (int) $gap[1]) * 1_000_000);
            }
        }

        return compact('sent', 'failed');
    }

    /** Right after a payment is recorded. Returns true=sent, false=failed, null=not applicable. */
    public function receiptFor(Payment $payment): ?bool
    {
        $pid = (int) $payment->property_id;
        if (! self::enabled($pid) || ! in_array('receipt', self::stages($pid), true) || ! self::inWindow()) return null;

        $invoice = Invoice::withoutGlobalScopes()->find($payment->invoice_id);

        return $invoice ? $this->deliver($invoice, 'receipt') : null;
    }

    private function candidates(int $pid): array
    {
        $stages = self::stages($pid);
        $today = Carbon::today(BillingSchedule::TZ);
        $out = [];

        Invoice::withoutGlobalScopes()->where('property_id', $pid)->where('status', 'unpaid')->orderBy('due_date')->get()
            ->each(function (Invoice $i) use ($stages, $today, &$out) {
                $kind = InvoiceState::of($i, $today)['action'];
                if (! $kind || ! in_array($kind, $stages, true)) return;
                if ($kind === 'overdue') {
                    $late = (int) Carbon::parse($i->due_date->toDateString(), BillingSchedule::TZ)->diffInDays($today, false);
                    if ($late > self::OVERDUE_WINDOW_DAYS) return; // never blast old arrears the moment the bot is switched on
                }
                $out[] = [$i, $kind];
            });

        if (in_array('receipt', $stages, true)) {
            $since = max(
                Carbon::now()->subDays(self::RECEIPT_LOOKBACK_DAYS),
                ($s = Settings::get($pid, 'wa_auto_since')) ? Carbon::parse($s) : Carbon::now()->subDays(self::RECEIPT_LOOKBACK_DAYS)
            );
            $ids = Payment::withoutGlobalScopes()->where('property_id', $pid)->where('created_at', '>=', $since)->pluck('invoice_id');
            Invoice::withoutGlobalScopes()->whereIn('id', $ids)->where('status', 'paid')->get()->each(function (Invoice $i) use (&$out) {
                $out[] = [$i, 'receipt'];
            });
        }

        return $out;
    }

    /** @return bool|null true=sent, false=failed, null=skipped */
    private function deliver(Invoice $invoice, string $kind): ?bool
    {
        $logs = MessageLog::withoutGlobalScopes()->where('invoice_id', $invoice->id)->where('kind', $kind)->get();
        if ($logs->contains(fn ($l) => in_array($l->status, ['sent', 'opened'], true))) return null; // already handled (bot or owner)

        $failed = $logs->where('status', 'failed');
        if ($failed->count() >= self::MAX_ATTEMPTS) return null;
        if ($failed->isNotEmpty() && $failed->max('created_at')->gt(Carbon::now()->subMinutes(self::RETRY_AFTER_MIN))) return null;

        $invoice->load(['rental' => fn ($q) => $q->withoutGlobalScopes()->with(['tenant' => fn ($t) => $t->withoutGlobalScopes(), 'room' => fn ($r) => $r->withoutGlobalScopes()])]);
        $property = Property::find($invoice->property_id);
        $tenant = $invoice->rental->tenant;
        $phone = Billing::phone((string) $tenant->phone);
        if (strlen($phone) < 10) return null;

        if ($kind === 'receipt') {
            $payment = Payment::withoutGlobalScopes()->with(['receipt' => fn ($q) => $q->withoutGlobalScopes()])->where('invoice_id', $invoice->id)->first();
            if (! $payment?->receipt) return null;
            $body = Messages::receipt($payment->receipt, $invoice, $property->name);
        } else {
            $body = Messages::invoice($kind, $invoice, $property->name);
        }

        $res = $this->sender->send($phone, $body);
        MessageLog::withoutGlobalScopes()->create([
            'property_id' => $invoice->property_id, 'tenant_id' => $tenant->id, 'invoice_id' => $invoice->id, 'kind' => $kind, 'channel' => 'whatsapp_bot',
            'status' => $res->ok ? 'sent' : 'failed', 'to_phone' => $phone, 'body' => $body, 'error' => $res->error,
        ]);

        return $res->ok;
    }
}
