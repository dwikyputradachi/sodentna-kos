<?php

namespace App\Support\Finance;

use App\Support\BillingSchedule;
use App\Support\Owner\Fmt;
use Carbon\Carbon;
use Illuminate\Http\Request;

/** Reporting period from the query string: ?bulan=2026-11 (default: this month) or ?tahun=2026. */
class Period
{
    public static function resolve(Request $r): array
    {
        $tz = BillingSchedule::TZ;

        if (preg_match('/^\d{4}$/', (string) $r->query('tahun'))) {
            $y = (int) $r->query('tahun');
            $from = Carbon::create($y, 1, 1, 0, 0, 0, $tz);

            return ['mode' => 'year', 'from' => $from, 'to' => $from->copy()->endOfYear()->startOfDay(), 'label' => 'Tahun ' . $y, 'key' => (string) $y,
                'prev' => ['tahun' => $y - 1], 'next' => ['tahun' => $y + 1]];
        }

        $m = preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', (string) $r->query('bulan')) ? $r->query('bulan') : Carbon::today($tz)->format('Y-m');
        $from = Carbon::createFromFormat('Y-m-d', $m . '-01', $tz)->startOfDay();

        return ['mode' => 'month', 'from' => $from, 'to' => $from->copy()->endOfMonth()->startOfDay(), 'label' => Fmt::date($from, 'F Y'), 'key' => $m,
            'prev' => ['bulan' => $from->copy()->subMonthNoOverflow()->format('Y-m')], 'next' => ['bulan' => $from->copy()->addMonthNoOverflow()->format('Y-m')]];
    }
}
