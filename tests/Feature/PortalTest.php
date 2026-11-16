<?php

namespace Tests\Feature;

use App\Models\Floor;
use App\Models\Property;
use App\Models\Rental;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PortalTest extends TestCase
{
    use RefreshDatabase;

    private function world(): array
    {
        $p = Property::create(['name' => 'Kos A', 'slug' => 'a']);
        $f = Floor::create(['property_id' => $p->id, 'name' => 'Lantai 1']);
        $t = RoomType::create(['property_id' => $p->id, 'name' => 'Standar', 'capacity' => 2, 'rate_one' => 1500000, 'rate_two' => 2000000, 'deposit_amount' => 500000]);

        return [$p, $f, $t];
    }

    private function tenantWithRental(Property $p, Floor $f, RoomType $t, string $code, string $phone, string $name, string $status = 'reserved'): array
    {
        $user = User::create(['name' => $name, 'phone' => $phone, 'role' => 'tenant', 'password' => 'rahasia123']);
        $tenant = Tenant::create(['property_id' => $p->id, 'user_id' => $user->id, 'name' => $name, 'phone' => $phone, 'id_number' => '3171000000000001',
            'emergency_name' => 'Ibu', 'emergency_relation' => 'Ibu', 'emergency_phone' => '6281200000099']);
        $room = Room::create(['property_id' => $p->id, 'floor_id' => $f->id, 'room_type_id' => $t->id, 'code' => $code, 'status' => 'reserved']);
        Rental::create(['property_id' => $p->id, 'tenant_id' => $tenant->id, 'room_id' => $room->id, 'mode' => 'monthly', 'occupants' => 1,
            'start_date' => '2030-01-05', 'rate' => 1500000, 'deposit_amount' => 500000, 'billing_anchor_day' => 5, 'status' => $status]);

        return [$user, $tenant, $room];
    }

    public function test_tenant_sees_own_rental_and_first_payment(): void
    {
        [$p, $f, $t] = $this->world();
        [$user] = $this->tenantWithRental($p, $f, $t, '101', '6281300000101', 'Alma Rikhafinata');

        $this->actingAs($user)->get('/penghuni')->assertOk()->assertSee('Halo, Alma.')->assertSee('101')->assertSee('Rp 1.500.000')->assertSee('Rp 2.000.000')->assertSee('Dipesan');
        $this->get('/penghuni/tagihan')->assertOk()->assertSee('Pembayaran awal');
    }

    public function test_tenant_never_sees_another_tenants_data(): void
    {
        [$p, $f, $t] = $this->world();
        [$a] = $this->tenantWithRental($p, $f, $t, '101', '6281300000101', 'Alma Rikhafinata');
        $this->tenantWithRental($p, $f, $t, 'K-77', '6281300000202', 'Budi Santoso');

        $this->actingAs($a)->get('/penghuni')->assertSee('101')->assertDontSee('K-77')->assertDontSee('Budi');
        $this->get('/penghuni/profil')->assertOk()->assertSee('Alma')->assertDontSee('Budi');
    }

    public function test_user_without_tenant_record_gets_friendly_page(): void
    {
        $user = User::create(['name' => 'X', 'phone' => '6281300000999', 'role' => 'tenant', 'password' => 'rahasia123']);
        $this->actingAs($user)->get('/penghuni')->assertOk()->assertSee('Data penghuni belum tersedia');
    }

    public function test_tenant_without_active_rental_sees_message(): void
    {
        [$p, $f, $t] = $this->world();
        [$user] = $this->tenantWithRental($p, $f, $t, '101', '6281300000101', 'Alma', 'ended');

        $this->actingAs($user)->get('/penghuni')->assertOk()->assertSee('Belum ada sewa aktif');
    }

    public function test_profile_update_changes_only_emergency_contact_of_own_record(): void
    {
        [$p, $f, $t] = $this->world();
        [$a, $ta] = $this->tenantWithRental($p, $f, $t, '101', '6281300000101', 'Alma');
        [, $tb] = $this->tenantWithRental($p, $f, $t, 'K-77', '6281300000202', 'Budi');

        $this->actingAs($a)->put('/penghuni/profil', [
            'name' => 'Diganti', 'phone' => '6289999999999', 'emergency_name' => 'Ayah', 'emergency_relation' => 'Ayah', 'emergency_phone' => '0812-0000-1111', 'email' => 'alma@example.com',
        ])->assertSessionHasNoErrors();

        $ta->refresh();
        $this->assertSame('Ayah', $ta->emergency_name);
        $this->assertSame('6281200001111', $ta->emergency_phone);
        $this->assertSame('Alma', $ta->name);                // name stays manager-controlled
        $this->assertSame('6281300000101', $ta->phone);      // login number stays
        $this->assertSame('Ibu', $tb->fresh()->emergency_name); // other tenant untouched
        $this->assertSame('alma@example.com', $a->fresh()->email);
    }

    public function test_password_change_needs_current_password(): void
    {
        [$p, $f, $t] = $this->world();
        [$user] = $this->tenantWithRental($p, $f, $t, '101', '6281300000101', 'Alma');
        $this->actingAs($user);

        $this->put('/penghuni/profil/kata-sandi', ['current_password' => 'salah', 'password' => 'baru-12345', 'password_confirmation' => 'baru-12345'])->assertSessionHasErrors('current_password', null, 'password');
        $this->put('/penghuni/profil/kata-sandi', ['current_password' => 'rahasia123', 'password' => 'baru-12345', 'password_confirmation' => 'baru-12345'])->assertSessionHasNoErrors();

        $this->assertTrue(Hash::check('baru-12345', $user->fresh()->password));
    }

    public function test_rules_page_lists_the_house_rules(): void
    {
        [$p, $f, $t] = $this->world();
        [$user] = $this->tenantWithRental($p, $f, $t, '101', '6281300000101', 'Alma');

        $this->actingAs($user)->get('/penghuni/peraturan-kos')->assertOk()->assertSee('fotokopi KTP');
    }
}
