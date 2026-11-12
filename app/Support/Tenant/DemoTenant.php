<?php

namespace App\Support\Tenant;

use App\Support\Owner\DemoData;
use Carbon\Carbon;

/** DEV-ONLY demo data for the tenant portal. Replace with queries scoped to the authenticated tenant. */
class DemoTenant
{
    public const OWNER_WA = '081364449270';

    public static function profile(): array
    {
        return [
            'name' => 'Alma Rikhafinata', 'email' => 'alma@example.com', 'phone' => '082285455631',
            'emergency_name' => 'Ibu Rina', 'emergency_phone' => '081200000011', 'emergency_rel' => 'Ibu',
            'ktp_masked' => '3171••••••••0001',
        ];
    }

    public static function rental(): array
    {
        $t = DemoData::today();
        return [
            'room' => '101', 'floor' => 'Lantai 1', 'type' => 'Standar', 'mode' => 'Bulanan', 'rent' => 1500000, 'deposit' => 500000,
            'start' => $t->copy()->subMonths(3)->startOfMonth(), 'end' => $t->copy()->addMonths(9)->endOfMonth(),
        ];
    }

    public static function invoices(): array
    {
        $t = DemoData::today();
        $i = fn ($no, $off, $db, $amt = 1500000) => [
            'no' => $no, 'tenant' => 'Alma Rikhafinata', 'room' => '101', 'phone' => '082285455631', 'amount' => $amt,
            'due' => $t->copy()->addDays($off), 'period' => \App\Support\Owner\Fmt::date($t->copy()->addDays($off), 'F Y'), 'db' => $db,
        ];
        return [$i('INV-2611-101', 2, 'unpaid')];
    }

    public static function payments(): array
    {
        $t = DemoData::today();
        $p = fn ($m, $off, $no) => ['period' => \App\Support\Owner\Fmt::date($t->copy()->subMonths($m), 'F Y'), 'paid' => $t->copy()->subMonths($m)->subDays($off), 'amount' => 1500000, 'receipt' => $no, 'method' => 'Transfer'];
        return [$p(1, 3, '110/KSA/10/2026'), $p(2, 2, '098/KSA/09/2026'), $p(3, 4, '085/KSA/08/2026')];
    }

    public static function rules(): array
    {
        return [
            'Jumlah penghuni tidak boleh melebihi kapasitas kamar.',
            'Sewa harian: maksimal satu orang per kamar. Kamar isi dua orang tidak untuk pasangan suami istri.',
            'Dilarang membawa hewan peliharaan. Jaga kebersihan kamar dan fasilitas bersama.',
            'Jaga ketenangan; tidak membuat gangguan atau kebisingan.',
            'Dilarang mengubah fasilitas kamar atau instalasi listrik tanpa izin pengelola.',
            'Tamu menginap wajib dilaporkan kepada pengelola sesuai ketentuan yang berlaku.',
            'Kerusakan akibat kelalaian penghuni dapat dibebankan sesuai biaya perbaikan dan dipotong dari deposit, disertai rincian dan bukti.',
            'Dilarang narkoba, perjudian, minuman keras, serta barang mudah terbakar atau meledak.',
            'Matikan lampu, perangkat listrik, dan keran air bila tidak digunakan. Patuhi aturan parkir dan lingkungan.',
        ];
    }
}
