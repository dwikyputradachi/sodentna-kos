<?php

namespace App\Support;

use App\Models\Invoice;
use Carbon\Carbon;
use Carbon\CarbonInterface;

class InvoiceState
{
    /** @return array{key:string,label:string,action:?string} action = reminder|due|overdue|null */
    public static function of(Invoice $i, ?CarbonInterface $today = null): array
    {
        $today ??= Carbon::today(BillingSchedule::TZ);

        if ($i->status === 'paid') return ['key' => 'paid', 'label' => 'Lunas', 'action' => null];
        if ($i->status === 'void') return ['key' => 'void', 'label' => 'Dibatalkan', 'action' => null];

        $due = Carbon::parse($i->due_date->toDateString(), BillingSchedule::TZ);
        $days = (int) $today->diffInDays($due, false); // due - today

        return match (true) {
            $days < 0 => ['key' => 'overdue', 'label' => 'Terlambat', 'action' => 'overdue'],
            $days === 0 => ['key' => 'due', 'label' => 'Jatuh Tempo', 'action' => 'due'],
            $days <= 2 => ['key' => 'h2', 'label' => 'H-2', 'action' => 'reminder'],
            default => ['key' => 'later', 'label' => 'Belum Jatuh Tempo', 'action' => null],
        };
    }
}
