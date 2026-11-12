<?php

namespace App\Support\Site;

/** DEV-ONLY demo content. Prices, capacity and rental modes must come from room_types (owner-configurable). */
class DemoSite
{
    public const ADDRESS = 'Jl. Kartini VI No. A4, Sungai Harapan, Sekupang, Kota Batam';
    public const WA = '081364449270';

    public static function facilities(): array
    {
        return ['AC', 'Kasur', 'Lemari', 'Ventilasi & jendela', 'Kamar mandi dalam', 'Wi-Fi', 'CCTV', 'Parkir kendaraan'];
    }

    public static function types(): array
    {
        return [
            'standar' => [
                'name' => 'Kamar Standar', 'size' => '3 × 4 m', 'cap' => 1, 'monthly' => 1500000, 'daily' => 120000, 'deposit' => 500000,
                'note' => 'Satu penghuni. Tersedia sewa bulanan dan harian.', 'rule' => 'Sewa harian maksimal satu orang per kamar.',
            ],
            'double' => [
                'name' => 'Kamar Double', 'size' => '3 × 4 m', 'cap' => 2, 'monthly' => 2000000, 'daily' => null, 'deposit' => 500000,
                'note' => 'Untuk dua penghuni. Hanya sewa bulanan.', 'rule' => 'Tidak diperuntukkan bagi pasangan suami istri.',
            ],
        ];
    }

    /** Public view: status only, never tenant names. */
    public static function floors(): array
    {
        $out = [];
        foreach (\App\Support\Owner\DemoData::floors() as $floor => $rooms) {
            $out[$floor] = array_map(fn ($r) => ['code' => $r['code'], 'st' => $r['st']], $rooms);
        }
        return $out;
    }
}
