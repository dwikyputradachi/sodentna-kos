<?php

namespace App\Support\Owner;

use Carbon\Carbon;

/**
 * DEV-ONLY demo data so the owner pages render before the database phases exist.
 * Replace each method with Eloquent queries (scoped by property_id) phase by phase.
 */
class DemoData
{
    public const PROPERTY = 'Kos Sodentna Kartini 6';

    public static function today(): Carbon
    {
        return Carbon::today('Asia/Jakarta');
    }

    public static function floors(): array
    {
        $r = fn ($c, $s, $p, $w = null) => ['code' => $c, 'st' => $s, 'price' => $p, 'who' => $w];
        return [
            'Lantai 1' => [
                $r('101', 'Terisi', 1500000, 'Alma Rikhafinata'), $r('102', 'Terisi', 2000000, 'Dewi & Rani Saputra'),
                $r('103', 'Tersedia', 1500000), $r('104', 'Dalam Perbaikan', 1500000),
                $r('105', 'Terisi', 1500000, 'Rizki Ramadhan'), $r('106', 'Dipesan', 2000000, 'Calon: Sari'),
            ],
            'Lantai 2' => [
                $r('201', 'Terisi', 1500000, 'Rina Kusuma'), $r('202', 'Tersedia', 1500000),
                $r('203', 'Terisi', 2000000, 'Dian & Bayu'), $r('204', 'Terisi', 1500000, 'Fajar Nugroho'),
                $r('205', 'Tersedia', 2000000), $r('206', 'Terisi', 1500000, 'Putri Anggraini'),
            ],
        ];
    }

    public static function roomCounts(): array
    {
        $c = ['Tersedia' => 0, 'Dipesan' => 0, 'Terisi' => 0, 'Dalam Perbaikan' => 0];
        foreach (self::floors() as $rooms) foreach ($rooms as $r) $c[$r['st']]++;
        return $c;
    }

    public static function invoices(): array
    {
        $t = self::today();
        $i = fn ($no, $who, $room, $ph, $amt, $off, $db) => [
            'no' => $no, 'tenant' => $who, 'room' => $room, 'phone' => $ph, 'amount' => $amt,
            'due' => $t->copy()->addDays($off), 'period' => Fmt::date($t, 'F Y'), 'db' => $db,
        ];
        return [
            $i('INV-2611-101', 'Alma Rikhafinata', '101', '082285455631', 1500000, 2, 'unpaid'),
            $i('INV-2611-105', 'Dwiky Putra Dachi', '105', '082285455631', 1500000, 0, 'unpaid'),
            $i('INV-2611-201', 'Rina Kusuma', '201', '081234567003', 1500000, -3, 'unpaid'),
            $i('INV-2611-102', 'Dewi & Rani Saputra', '102', '081234567004', 2000000, -1, 'submitted'),
            $i('INV-2611-203', 'Dian & Bayu', '203', '081234567005', 2000000, 5, 'rejected'),
            $i('INV-2611-204', 'Fajar Nugroho', '204', '081234567006', 1500000, -6, 'paid'),
            $i('INV-2611-206', 'Putri Anggraini', '206', '081234567007', 1500000, 12, 'unpaid'),
        ];
    }

    public static function tenants(): array
    {
        $t = self::today();
        $n = fn ($name, $room, $ph, $end, $rent, $st) => compact('name', 'room', 'ph', 'rent', 'st') + ['end' => $t->copy()->addDays($end)];
        return [
            $n('Alma Rikhafinata', '101', '082285455631', 200, 1500000, 'Aktif'),
            $n('Dewi & Rani Saputra', '102', '081234567004', 90, 2000000, 'Aktif'),
            $n('Rizki Ramadhan', '105', '081234567002', 25, 1500000, 'Aktif'),
            $n('Rina Kusuma', '201', '081234567003', 150, 1500000, 'Aktif'),
            $n('Dian & Bayu', '203', '081234567005', 14, 2000000, 'Proses Keluar'),
            $n('Fajar Nugroho', '204', '081234567006', 320, 1500000, 'Aktif'),
            $n('Putri Anggraini', '206', '081234567007', 60, 1500000, 'Aktif'),
            $n('Sari Ramadhani', '106', '081234567008', 0, 2000000, 'Calon Penghuni'),
        ];
    }

    public static function ledger(): array
    {
        $t = self::today();
        $e = fn ($off, $desc, $room, $type, $amt, $m) => ['date' => $t->copy()->subDays($off), 'desc' => $desc, 'room' => $room, 'type' => $type, 'amt' => $amt, 'method' => $m];
        return [
            $e(1, 'Sewa — Fajar Nugroho', '204', 'Sewa', 1500000, 'Transfer BCA'),
            $e(2, 'Deposit — Sari Ramadhani', '106', 'Deposit diterima', 500000, 'Transfer BCA'),
            $e(3, 'Perbaikan keran kamar mandi', '105', 'Pengeluaran', -150000, 'Tunai'),
            $e(5, 'Listrik & air', '—', 'Pengeluaran', -1850000, 'Transfer BCA'),
            $e(6, 'Sewa — Alma Rikhafinata', '101', 'Sewa', 1500000, 'Transfer BCA'),
            $e(8, 'Refund deposit — penghuni lama', '110', 'Refund deposit', -455000, 'Transfer BCA'),
            $e(9, 'Kebersihan bulanan', '—', 'Pengeluaran', -700000, 'Tunai'),
            $e(10, 'Sewa — Dewi & Rani Saputra', '102', 'Sewa', 2000000, 'Transfer BCA'),
        ];
    }

    /** Totals follow the brief: deposits are a liability, never rental income. */
    public static function financeTotals(): array
    {
        $s = ['Sewa' => 0, 'Deposit diterima' => 0, 'Pengeluaran' => 0, 'Refund deposit' => 0];
        foreach (self::ledger() as $l) $s[$l['type']] += abs($l['amt']);
        $in = $s['Sewa'] + $s['Deposit diterima'];
        $out = $s['Pengeluaran'] + $s['Refund deposit'];
        return $s + ['in' => $in, 'out' => $out, 'net' => $in - $out, 'operating' => $s['Sewa'] - $s['Pengeluaran'], 'opening' => 12000000];
    }

    public static function deposits(): array
    {
        $t = self::today();
        $d = fn ($who, $room, $init, $ded, $st, $off) => compact('who', 'room', 'init', 'ded', 'st') + ['refund' => $init - $ded, 'date' => $t->copy()->subDays($off)];
        return [
            $d('Alma Rikhafinata', '101', 500000, 0, 'Ditahan', 200),
            $d('Dewi & Rani Saputra', '102', 500000, 0, 'Ditahan', 90),
            $d('Rizki Ramadhan', '105', 500000, 0, 'Ditahan', 120),
            $d('Dian & Bayu', '203', 500000, 45000, 'Proses', 330),
            $d('Penghuni lama', '110', 500000, 45000, 'Dikembalikan', 8),
        ];
    }

    public static function tickets(): array
    {
        $t = self::today();
        $k = fn ($no, $room, $issue, $off, $who, $cost, $st) => compact('no', 'room', 'issue', 'who', 'cost', 'st') + ['date' => $t->copy()->subDays($off)];
        return [
            $k('MNT-041', '104', 'AC tidak dingin', 1, 'Pak Hendra', 450000, 'Dikerjakan'),
            $k('MNT-042', '101', 'Keran kamar mandi menetes', 1, 'Belum ditugaskan', null, 'Menunggu'),
            $k('MNT-040', '205', 'Pengecatan dinding', 2, 'Pak Yudi', 600000, 'Dikerjakan'),
            $k('MNT-039', '204', 'Perbaikan pintu', 3, 'Pak Dadi', 250000, 'Dikerjakan'),
            $k('MNT-038', '103', 'Lampu kamar mati', 4, 'Belum ditugaskan', null, 'Menunggu'),
            $k('MNT-037', '203', 'Penggantian shower', 6, 'Pak Hendra', 150000, 'Selesai'),
        ];
    }

    public static function receipts(): array
    {
        $t = self::today();
        $r = fn ($no, $who, $room, $amt, $off, $st) => compact('no', 'who', 'room', 'amt', 'st') + ['date' => $t->copy()->subDays($off)];
        return [
            $r('112/KSA/11/2026', 'Fajar Nugroho', '204', 1500000, 1, 'Lunas'),
            $r('111/KSA/11/2026', 'Sari Ramadhani', '106', 500000, 2, 'Lunas'),
            $r('110/KSA/11/2026', 'Alma Rikhafinata', '101', 1500000, 6, 'Lunas'),
            $r('109/KSA/11/2026', 'Dewi & Rani Saputra', '102', 2000000, 10, 'Lunas'),
        ];
    }
}
