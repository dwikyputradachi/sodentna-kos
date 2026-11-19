<?php

namespace Database\Seeders;

use App\Actions\ApproveApplication;
use App\Actions\CreateTenantManually;
use App\Actions\GenerateInvoices;
use App\Actions\RecordPayment;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Property;
use App\Models\Rental;
use App\Models\RentalApplication;
use App\Models\Room;
use App\Models\RoomStatusLog;
use App\Models\RoomType;
use App\Models\Tenant;
use App\Models\User;
use App\Models\WaitingListEntry;
use App\Support\Settings;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * LOCAL DEMO DATA ONLY (names, numbers and amounts are made up). Never run in production.
 * Dates are relative to today so every billing state is visible: H-2, due today, overdue, paid, not yet due.
 *
 * Run:  php artisan migrate:fresh --seed --seeder=DemoSeeder
 * Optional in .env: DEMO_PHONE=0812xxxxxxxx  -> the "due today" tenant uses YOUR number, so you can test the WhatsApp buttons/bot.
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->isLocal()) {
            $this->command?->warn('DemoSeeder hanya untuk lingkungan lokal (APP_ENV=local).');
            return;
        }

        $this->call(DevSeeder::class); // property, owner, floors, rooms, room type

        $p = Property::where('slug', 'kartini-6')->firstOrFail();
        if (Tenant::withoutGlobalScopes()->where('property_id', $p->id)->exists()) {
            $this->command?->warn('Data demo sudah ada. Untuk mengulang: php artisan migrate:fresh --seed --seeder=DemoSeeder');
            return;
        }

        $owner = User::where('phone', '6281300000001')->firstOrFail();
        $type = RoomType::where('property_id', $p->id)->where('name', 'Standar')->firstOrFail();
        $type->update(['allows_daily' => true, 'daily_rate' => 120000]); // demo daily rate so the "Harian" card is active

        $today = Carbon::today('Asia/Jakarta');
        $room = fn (string $code) => Room::where('property_id', $p->id)->where('code', $code)->firstOrFail();
        $create = app(CreateTenantManually::class);
        $pay = app(RecordPayment::class);

        // name, co-occupant, phone, room, occupants, rate, due offset (days from today), cycle k (renewals since move-in)
        $people = [
            ['Alma Rikhafinata', null, '081234567001', '101', 1, 1500000, 2, 4, true],                        // H-2 (+ akun portal)
            ['Rizki Ramadhan', null, env('DEMO_PHONE') ?: '081234567002', '105', 1, 1500000, 0, 3, false],   // jatuh tempo hari ini
            ['Rina Kusuma', null, '081234567003', '201', 1, 1500000, -3, 5, false],                           // terlambat 3 hari
            ['Dewi Anggraini', 'Rani Saputra', '081234567004', '102', 2, 2000000, -6, 3, false],             // sudah lunas
            ['Fajar Nugroho', null, '081234567006', '204', 1, 1500000, 15, 2, false],                         // belum ada tagihan
            ['Putri Wulandari', null, '081234567007', '206', 1, 1500000, 5, 4, false],                        // belum jatuh tempo
            ['Bayu Prasetyo', 'Dimas Arya', '081234567005', '203', 2, 2000000, 20, 6, false],                 // jatuh tempo masih jauh
        ];

        $rentals = [];
        foreach ($people as [$name, $name2, $phone, $code, $occ, $rate, $offset, $k, $account]) {
            $due = $today->copy()->addDays($offset);
            $start = $due->copy()->subMonthsNoOverflow($k);

            [, $rental] = $create->handle([
                'name' => $name, 'name_2' => $name2, 'phone' => $phone, 'room_id' => $room($code)->id, 'occupants' => $occ,
                'start_date' => $start->toDateString(), 'next_due_date' => $due->toDateString(), 'rate' => $rate, 'deposit_amount' => 500000,
                'id_number' => '31710000000' . str_pad((string) random_int(10000, 99999), 5, '0'), 'birth_place' => 'Batam', 'birth_date' => '2000-05-17',
                'address' => 'Jl. Contoh No. 1, Batam', 'job' => $occ === 1 ? 'Karyawan' : 'Mahasiswa', 'institution' => 'Instansi Contoh',
                'emergency_name' => 'Orang Tua ' . explode(' ', $name)[0], 'emergency_relation' => 'Orang tua', 'emergency_phone' => '081200000000',
                'create_account' => $account ? 1 : 0,
            ], $owner);

            $this->history($rental, $k - 1, $owner, $pay); // earlier months already paid
            $rentals[$name] = $rental;
        }

        app(GenerateInvoices::class)->run(); // creates the open invoices (H-2, due, overdue, not yet due)

        // Dewi & Rani already paid this cycle
        $open = Invoice::withoutGlobalScopes()->where('rental_id', $rentals['Dewi Anggraini']->id)->where('status', 'unpaid')->first();
        if ($open) $pay->handle($open, ['paid_on' => $today->copy()->subDays(5)->toDateString(), 'method' => 'transfer'], $owner);

        // Alumni: Ahmad Fauzi (room 103), left 40 days ago, full payment history kept
        $ahmad = Tenant::create(['property_id' => $p->id, 'name' => 'Ahmad Fauzi', 'phone' => '6281234567011', 'status' => 'former', 'job' => 'Karyawan']);
        $aStart = $today->copy()->subMonths(8);
        $aRental = Rental::create(['property_id' => $p->id, 'tenant_id' => $ahmad->id, 'room_id' => $room('103')->id, 'mode' => 'monthly', 'occupants' => 1,
            'start_date' => $aStart, 'end_date' => $today->copy()->subDays(40), 'rate' => 1500000, 'deposit_amount' => 500000, 'billing_anchor_day' => $aStart->day, 'status' => 'ended']);
        $this->history($aRental, 6, $owner, $pay);

        // Approved application waiting for check-in: Sari Ramadhani, room 106 (reserved + first invoice rent + deposit)
        $sari = $this->application($p, $type, $room('106'), 'Sari Ramadhani', '081234567008', 1, $today->copy()->addDays(4));
        app(ApproveApplication::class)->handle($sari, $room('106')->id, $owner);

        // Two pending applications to review
        $this->application($p, $type, $room('202'), 'Budi Santoso', '081234567009', 1, $today->copy()->addDays(7));
        $this->application($p, $type, $room('205'), 'Tono Wijaya', '081234567010', 1, $today->copy()->addDays(2), daily: true);

        // Waiting list
        WaitingListEntry::create(['property_id' => $p->id, 'code' => 'DT-10001', 'name' => 'Citra Lestari', 'phone' => '6281234567021', 'room_type_id' => $type->id,
            'occupants' => 1, 'desired_start' => $today->copy()->addDays(14), 'status' => 'waiting']);
        WaitingListEntry::create(['property_id' => $p->id, 'code' => 'DT-10002', 'name' => 'Dimas Pratama', 'phone' => '6281234567022', 'room_type_id' => $type->id,
            'occupants' => 2, 'desired_start' => $today->copy()->addDays(30), 'notes' => 'Berdua dengan teman kerja', 'status' => 'waiting']);

        // Room 104 under maintenance
        $r104 = $room('104');
        $r104->update(['status' => 'maintenance']);
        RoomStatusLog::create(['property_id' => $p->id, 'room_id' => $r104->id, 'from_status' => 'available', 'to_status' => 'maintenance', 'note' => 'AC rusak (data demo)', 'user_id' => $owner->id]);

        // Finance demo: opening balance + this month's and last month's expenses
        Settings::set($p->id, 'opening_date', $today->copy()->subMonths(6)->startOfMonth()->toDateString());
        Settings::set($p->id, 'opening_balance', '3000000');
        foreach ([[-2, 'Listrik & air', 1850000, 'transfer', 'Listrik & air bulan ini', null], [-3, 'Kebersihan', 700000, 'cash', 'Kebersihan bulanan', null],
                  [-5, 'Perbaikan & pemeliharaan', 150000, 'cash', 'Ganti keran kamar mandi', '101'], [-8, 'Internet', 350000, 'transfer', 'Wi-Fi bulanan', null],
                  [-9, 'Perlengkapan', 250000, 'cash', 'Lampu LED 10 pcs', null], [-12, 'Upah / gaji', 1200000, 'cash', 'Penjaga kos', null],
                  [-35, 'Listrik & air', 1790000, 'transfer', 'Listrik & air bulan lalu', null], [-40, 'Kebersihan', 700000, 'cash', 'Kebersihan bulan lalu', null]] as [$off, $cat, $amt, $method, $desc, $rc]) {
            Expense::create(['property_id' => $p->id, 'spent_on' => $today->copy()->addDays($off), 'category' => $cat, 'amount' => $amt, 'method' => $method,
                'description' => $desc, 'room_id' => $rc ? $room($rc)->id : null, 'recorded_by' => $owner->id]);
        }

        $this->command?->info('Data demo siap. Login pemilik: 081300000001 / sodentna-dev. Login penghuni: 081234567001 / sodentna-dev.');
        if (env('DEMO_PHONE')) $this->command?->info('Penghuni "Rizki Ramadhan" (jatuh tempo hari ini) memakai nomor DEMO_PHONE Anda.');
    }

    /** Paid monthly invoices for cycles 1..$count after move-in, each with payment + receipt. */
    private function history(Rental $rental, int $count, User $owner, RecordPayment $pay): void
    {
        for ($i = 1; $i <= $count; $i++) {
            $due = $rental->start_date->copy()->addMonthsNoOverflow($i);
            $next = $rental->start_date->copy()->addMonthsNoOverflow($i + 1);

            $inv = Invoice::withoutGlobalScopes()->create([
                'property_id' => $rental->property_id, 'rental_id' => $rental->id, 'kind' => 'monthly', 'period_start' => $due->toDateString(),
                'period_end' => $next->copy()->subDay()->toDateString(), 'due_date' => $due->toDateString(),
                'rent_amount' => $rental->rate, 'deposit_amount' => 0, 'total_amount' => $rental->rate, 'status' => 'unpaid',
            ]);
            $inv->update(['number' => 'INV-' . $due->format('ym') . '-' . str_pad((string) $inv->id, 4, '0', STR_PAD_LEFT)]);

            $pay->handle($inv, ['paid_on' => $due->copy()->subDay()->toDateString(), 'method' => $i % 2 ? 'transfer' : 'cash'], $owner);
        }
    }

    private function application(Property $p, RoomType $type, Room $room, string $name, string $phone, int $occ, Carbon $start, bool $daily = false): RentalApplication
    {
        return RentalApplication::create([
            'property_id' => $p->id, 'code' => 'SDN-' . random_int(10000, 99999), 'room_type_id' => $type->id, 'room_id' => $room->id,
            'mode' => $daily ? 'daily' : 'monthly', 'occupants' => $occ, 'start_date' => $start, 'end_date' => $daily ? $start->copy()->addDays(3) : null,
            'payment_method' => 'transfer', 'name' => $name, 'phone' => '62' . substr($phone, 1), 'address' => 'Jl. Contoh No. 2, Batam', 'id_number' => '3171000000000' . random_int(100, 999),
            'birth_place' => $daily ? null : 'Batam', 'birth_date' => $daily ? null : '2001-03-10', 'job' => $daily ? null : 'Mahasiswa', 'institution' => $daily ? null : 'Politeknik Contoh',
            'emergency_name' => 'Keluarga ' . explode(' ', $name)[0], 'emergency_relation' => 'Keluarga', 'emergency_phone' => '6281200000000', 'emergency_address' => $daily ? null : 'Jl. Contoh No. 3',
            'id_doc_path' => 'identitas/demo.jpg', // placeholder: the file does not exist, "Buka berkas" will be empty in demo
            'consented_at' => now(), 'status' => 'pending',
        ]);
    }
}
