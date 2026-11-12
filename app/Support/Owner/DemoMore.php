<?php

namespace App\Support\Owner;

/** DEV-ONLY demo data for applications, inventory, settings. */
class DemoMore
{
    public static function applications(): array
    {
        $t = DemoData::today();
        $a = fn ($code, $name, $type, $mode, $occ, $off, $st, $note = '') => [
            'code' => $code, 'name' => $name, 'type' => $type, 'mode' => $mode, 'occ' => $occ, 'status' => $st, 'note' => $note,
            'start' => Fmt::date($t->copy()->addDays($off)), 'job' => 'Karyawan', 'wa' => '0812••••' . rand(1000, 9999), 'ktp' => '3171••••••••' . rand(1000, 9999),
        ];
        return [
            $a('SDN-1004', 'Sari Ramadhani', 'standar', 'bulanan', 1, 5, 'Menunggu'),
            $a('SDN-1005', 'Budi Santoso', 'double', 'bulanan', 2, 10, 'Menunggu'),
            $a('SDN-1006', 'Tono Wijaya', 'standar', 'harian', 1, 2, 'Menunggu'),
            $a('SDN-1007', 'Lina & Rudi', 'standar', 'bulanan', 2, 7, 'Menunggu'),
            $a('SDN-1002', 'Nia Pratiwi', 'standar', 'bulanan', 1, -4, 'Disetujui'),
            $a('SDN-1003', 'Joko Susilo', 'double', 'harian', 1, -6, 'Ditolak'),
        ];
    }

    public static function inventory(): array
    {
        $i = fn ($n, $unit, $stock, $min, $cost, $loc) => compact('n', 'unit', 'stock', 'min', 'cost', 'loc');
        return [
            $i('Lampu LED 12W', 'pcs', 9, 5, 25000, 'Gudang'),
            $i('Keran air', 'pcs', 2, 3, 85000, 'Gudang'),
            $i('Saklar', 'pcs', 6, 4, 18000, 'Gudang'),
            $i('Cat tembok putih', 'kaleng', 0, 2, 220000, 'Gudang'),
            $i('Sapu', 'pcs', 4, 2, 30000, 'Lantai 1'),
            $i('Pel lantai', 'pcs', 3, 2, 45000, 'Lantai 2'),
        ];
    }

    public static function movements(): array
    {
        $t = DemoData::today();
        $m = fn ($off, $item, $type, $qty, $ref) => compact('item', 'type', 'qty', 'ref') + ['date' => $t->copy()->subDays($off)];
        return [
            $m(1, 'Keran air', 'Keluar', 1, 'Kamar 101 · MNT-042'),
            $m(3, 'Lampu LED 12W', 'Keluar', 1, 'Kamar 103 · MNT-038'),
            $m(5, 'Lampu LED 12W', 'Masuk', 10, 'Pembelian'),
            $m(7, 'Cat tembok putih', 'Keluar', 2, 'Kamar 205 · MNT-040'),
        ];
    }

    /** Open owner decisions from the brief (section 12). */
    public static function decisions(): array
    {
        return [
            'Pembayaran sewa harian: di muka, saat check-in, atau lainnya.',
            'Aturan larangan pasutri: hanya kamar double, atau semua jenis sewa.',
            'Tanggal jatuh tempo untuk mulai sewa tanggal 29, 30, 31.',
            'Biaya tamu menginap (keluarga maks. 2 hari, Rp125.000/hari berikutnya): masih berlaku atau tidak.',
            'Kolom agama pada form: masih diperlukan atau dihapus.',
            'Rentang minimum dan maksimum lama sewa harian.',
        ];
    }
}
