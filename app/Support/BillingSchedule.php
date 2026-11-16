<?php

namespace App\Support;

use Carbon\Carbon;
use Carbon\CarbonInterface;

/**
 * Monthly due dates follow the move-in day. Move-in on 29/30/31 falls on the last day of shorter months
 * (anchor 31: Jan 31 -> Feb 28 -> Mar 31). TODO: make this rule owner-configurable.
 */
class BillingSchedule
{
    public const TZ = 'Asia/Jakarta';

    public static function inMonth(int $year, int $month, int $anchor): Carbon
    {
        $first = Carbon::create($year, $month, 1, 0, 0, 0, self::TZ);

        return $first->copy()->day(min($anchor, $first->daysInMonth));
    }

    /** The due date one month after $from. */
    public static function nextAfter(CarbonInterface $from, int $anchor): Carbon
    {
        $next = Carbon::create($from->year, $from->month, 1, 0, 0, 0, self::TZ)->addMonthNoOverflow();

        return self::inMonth($next->year, $next->month, $anchor);
    }

    /** First due date strictly after today (used for tenants already living in the kos). */
    public static function upcoming(CarbonInterface $today, int $anchor): Carbon
    {
        $d = self::inMonth($today->year, $today->month, $anchor);

        return $d->lte($today) ? self::nextAfter($d, $anchor) : $d;
    }
}
