<?php

namespace App\Actions;

use App\Models\Invoice;
use App\Models\Rental;
use App\Support\BillingSchedule;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/** Idempotent: safe to run from the daily scheduler AND whenever a billing page opens. */
class GenerateInvoices
{
    public const LEAD_DAYS = 7; // invoices appear a week before they are due, so H-2 reminders always have one

    public function run(?int $propertyId = null): int
    {
        $today = Carbon::today(BillingSchedule::TZ);
        $horizon = $today->copy()->addDays(self::LEAD_DAYS)->toDateString();
        $created = 0;

        $rentals = Rental::withoutGlobalScopes()->where('status', 'active')->where('mode', 'monthly')->whereNotNull('next_due_date')
            ->whereDate('next_due_date', '<=', $horizon)->when($propertyId, fn ($q) => $q->where('property_id', $propertyId))->get();

        foreach ($rentals as $rental) {
            DB::transaction(function () use ($rental, $horizon, &$created) {
                $rental = Rental::withoutGlobalScopes()->lockForUpdate()->find($rental->id);

                for ($i = 0; $i < 12 && $rental->next_due_date && $rental->next_due_date->toDateString() <= $horizon; $i++) {
                    $due = Carbon::parse($rental->next_due_date->toDateString(), BillingSchedule::TZ);
                    if ($rental->end_date && $due->gt($rental->end_date)) break;

                    $next = BillingSchedule::nextAfter($due, (int) ($rental->billing_anchor_day ?: $due->day));
                    $exists = Invoice::withoutGlobalScopes()->where('rental_id', $rental->id)->where('kind', 'monthly')->whereDate('period_start', $due->toDateString())->exists();
                    if (! $exists) {
                        $invoice = Invoice::withoutGlobalScopes()->create([
                            'property_id' => $rental->property_id, 'rental_id' => $rental->id, 'kind' => 'monthly', 'period_start' => $due->toDateString(),
                            'period_end' => $next->copy()->subDay()->toDateString(), 'due_date' => $due->toDateString(),
                            'rent_amount' => $rental->rate, 'deposit_amount' => 0, 'total_amount' => $rental->rate,
                        ]);
                        $this->number($invoice);
                        $created++;
                    }

                    $rental->update(['next_due_date' => $next->toDateString()]);
                }
            });
        }

        return $created;
    }

    /** First bill after an application is approved: first month's rent + deposit (monthly) or the whole stay (daily). */
    public function firstInvoice(Rental $rental): Invoice
    {
        $monthly = $rental->mode === 'monthly';
        $nights = $monthly ? 0 : max(1, (int) $rental->start_date->diffInDays($rental->end_date));
        $rent = $monthly ? $rental->rate : $rental->rate * $nights;
        $deposit = $monthly ? $rental->deposit_amount : 0; // daily deposit policy is still an owner decision

        $kind = $monthly ? 'first' : 'daily';
        $existing = Invoice::withoutGlobalScopes()->where('rental_id', $rental->id)->where('kind', $kind)->whereDate('period_start', $rental->start_date->toDateString())->first();
        if ($existing) return $existing;

        $invoice = Invoice::withoutGlobalScopes()->create([
            'property_id' => $rental->property_id, 'rental_id' => $rental->id, 'kind' => $kind, 'period_start' => $rental->start_date->toDateString(),
            'period_end' => $monthly ? null : $rental->end_date?->toDateString(), 'due_date' => $rental->start_date->toDateString(),
            'rent_amount' => $rent, 'deposit_amount' => $deposit, 'total_amount' => $rent + $deposit,
        ]);
        $this->number($invoice);

        return $invoice;
    }

    private function number(Invoice $invoice): void
    {
        $invoice->update(['number' => 'INV-' . $invoice->period_start->format('ym') . '-' . str_pad((string) $invoice->id, 4, '0', STR_PAD_LEFT)]);
    }
}
