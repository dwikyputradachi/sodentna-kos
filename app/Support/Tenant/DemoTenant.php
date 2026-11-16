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
            'name' => 'Alma Rikhafinata', 'email' => 'alma@example.com', 'phone' => '081234567001',
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
            'no' => $no, 'tenant' => 'Alma Rikhafinata', 'room' => '101', 'phone' => '081234567001', 'amount' => $amt,
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

    /** Source: printed "Formulir Data Penghuni & Peraturan Kost Sodentna". TODO: move to editable Settings. */
    public static function rules(): array
    {
        return [
            'Penghuni wajib menyerahkan fotokopi KTP yang masih berlaku.',
            'Teman tidak diperkenankan menginap di kos.',
            'Tamu yang menginap wajib dilaporkan kepada pengelola kos.',
            'Keluarga inti boleh menginap maksimal 2 hari, lebih dari itu dikenakan biaya Rp125.000 per hari.',
            'Tidak diperkenankan membawa hewan peliharaan.',
            'Dilarang membuat keributan atau kegaduhan.',
            'Tidak mengganggu kenyamanan penghuni lain, termasuk suara musik, TV, atau berbicara keras.',
            'Pengelola kos tidak bertanggung jawab atas kehilangan atau kerusakan barang pribadi penghuni.',
            'Dilarang membawa, menyimpan, atau menggunakan narkoba, obat terlarang, minuman keras dan berjudi dalam bentuk apa pun.',
            'Dilarang membawa atau menyimpan barang berbahaya yang mudah terbakar atau meledak.',
            'Matikan lampu, alat listrik, dan kran air saat tidak digunakan atau meninggalkan kamar.',
            'Dilarang merusak atau mengubah fasilitas kos, termasuk mengecat atau melubangi dinding, lantai, atau plafon, serta memodifikasi instalasi listrik (menambah kabel, bor, dll) tanpa izin pengelola. Jika terjadi kerusakan akibat kelalaian penghuni, wajib dikembalikan ke kondisi semula.',
            'Setiap penghuni wajib membayar deposit sebesar Rp500.000. Deposit dikembalikan utuh saat keluar kos bila tidak ada kerusakan fasilitas akibat kelalaian penghuni (poin 12). Bila ada kerusakan, deposit dipotong sesuai biaya perbaikan.',
            'Menjaga kebersihan dan fasilitas kos bersama.',
            'Membuang sampah pada tempat yang telah disediakan.',
            'Dilarang membuang tisu atau pembalut ke dalam closet.',
            'Berperilaku sopan dan menjaga ketertiban di lingkungan kos dan warga sekitar (RT/RW).',
            'Penghuni berkendaraan wajib menjaga ketertiban dan kerapian parkir dan mengunci kendaraan. Keamanan kendaraan menjadi tanggung jawab penuh pemilik kendaraan.',
        ];
    }
}
