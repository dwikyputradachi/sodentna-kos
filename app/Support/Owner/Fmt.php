<?php

namespace App\Support\Owner;

use Carbon\CarbonInterface;

class Fmt
{
    public static function rp(int|float $n): string
    {
        return ($n < 0 ? '-' : '') . 'Rp ' . number_format(abs($n), 0, ',', '.');
    }

    public static function date(?CarbonInterface $d, string $f = 'j M Y'): string
    {
        return $d ? $d->copy()->locale('id')->translatedFormat($f) : '—';
    }
}
