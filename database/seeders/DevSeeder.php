<?php

namespace Database\Seeders;

use App\Models\Floor;
use App\Models\Property;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\User;
use Illuminate\Database\Seeder;

/** LOCAL DEV ONLY. Never run in production. */
class DevSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->isLocal()) {
            $this->command?->warn('DevSeeder hanya untuk lingkungan lokal.');
            return;
        }

        $p = Property::updateOrCreate(['slug' => 'kartini-6'], [
            'name' => 'Kos Sodentna Kartini 6', 'address' => 'Jl. Kartini VI No. A4, Sungai Harapan, Sekupang, Kota Batam',
        ]);

        $owner = User::updateOrCreate(['phone' => '6281300000001'], ['name' => 'Ibu Hesty (dev)', 'role' => User::OWNER, 'password' => 'sodentna-dev']);
        $owner->properties()->syncWithoutDetaching([$p->id]);
        User::updateOrCreate(['phone' => '6281234567001'], ['name' => 'Alma Rikhafinata (dev)', 'role' => User::TENANT, 'password' => 'sodentna-dev']);

        // Rates are DEFAULT demo values; the owner edits them in Kamar & Lantai.
        $type = RoomType::updateOrCreate(['property_id' => $p->id, 'name' => 'Standar'], [
            'size' => '3 × 4 m', 'capacity' => 2, 'rate_one' => 1500000, 'rate_two' => 2000000, 'deposit_amount' => 500000,
        ]);

        foreach ([1 => ['101', '102', '103', '104', '105', '106'], 2 => ['201', '202', '203', '204', '205', '206']] as $n => $codes) {
            $floor = Floor::updateOrCreate(['property_id' => $p->id, 'name' => "Lantai $n"], ['sort_order' => $n]);
            foreach ($codes as $code) {
                Room::firstOrCreate(['property_id' => $p->id, 'code' => $code], ['floor_id' => $floor->id, 'room_type_id' => $type->id, 'status' => 'available']);
            }
        }
    }
}
