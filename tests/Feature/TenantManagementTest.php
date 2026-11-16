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
use Tests\TestCase;

class TenantManagementTest extends TestCase
{
    use RefreshDatabase;

    private function world(): array
    {
        $p = Property::create(['name' => 'Kos A', 'slug' => 'a']);
        $owner = User::create(['name' => 'Pemilik', 'phone' => '6281200000040', 'role' => 'owner', 'password' => 'rahasia123']);
        $owner->properties()->attach($p->id);
        $f = Floor::create(['property_id' => $p->id, 'name' => 'Lantai 1']);
        $t = RoomType::create(['property_id' => $p->id, 'name' => 'Standar', 'capacity' => 2, 'rate_one' => 1500000, 'rate_two' => 2000000, 'deposit_amount' => 500000]);
        $r101 = Room::create(['property_id' => $p->id, 'floor_id' => $f->id, 'room_type_id' => $t->id, 'code' => '101', 'status' => 'available']);
        $r102 = Room::create(['property_id' => $p->id, 'floor_id' => $f->id, 'room_type_id' => $t->id, 'code' => '102', 'status' => 'available']);

        return [$p, $owner, $r101, $r102];
    }

    private function payload(Room $room, array $o = []): array
    {
        return $o + ['name' => 'Ahmad', 'phone' => '0812-1111-2222', 'room_id' => $room->id, 'occupants' => 1, 'start_date' => '2026-08-01', 'rate' => 1500000, 'deposit_amount' => 500000, 'create_account' => 1];
    }

    public function test_owner_records_an_existing_tenant(): void
    {
        [, $owner, $r101] = $this->world();
        $this->actingAs($owner);

        $this->post(route('owner.tenants.store'), $this->payload($r101, ['rate' => 1300000]))->assertSessionHasNoErrors()->assertSessionHas('activation');

        $this->assertSame('occupied', $r101->fresh()->status);
        $this->assertDatabaseHas('tenants', ['phone' => '6281211112222', 'status' => 'active']);
        $this->assertDatabaseHas('rentals', ['rate' => 1300000, 'status' => 'active', 'billing_anchor_day' => 1]); // grandfathered rate + past start date
        $this->assertDatabaseCount('account_invitations', 1);
    }

    public function test_cannot_add_into_an_occupied_room_or_beyond_capacity(): void
    {
        [, $owner, $r101] = $this->world();
        $this->actingAs($owner);

        $this->post(route('owner.tenants.store'), $this->payload($r101))->assertSessionHasNoErrors();
        $this->post(route('owner.tenants.store'), $this->payload($r101, ['name' => 'Lain', 'phone' => '081299998888']))->assertSessionHasErrors('room_id');
        $this->assertDatabaseCount('tenants', 1);

        $single = RoomType::create(['property_id' => $r101->property_id, 'name' => 'Single', 'capacity' => 1, 'rate_one' => 1500000]);
        $r103 = Room::create(['property_id' => $r101->property_id, 'floor_id' => $r101->floor_id, 'room_type_id' => $single->id, 'code' => '103', 'status' => 'available']);
        $this->post(route('owner.tenants.store'), $this->payload($r103, ['name' => 'Dua', 'phone' => '081277776666', 'occupants' => 2]))->assertSessionHasErrors('room_id');
    }

    public function test_search_by_name_room_and_co_occupant_across_tabs(): void
    {
        [, $owner, $r101, $r102] = $this->world();
        $this->actingAs($owner);
        $this->post(route('owner.tenants.store'), $this->payload($r101, ['name' => 'Ahmad']));
        $this->post(route('owner.tenants.store'), $this->payload($r102, ['name' => 'Bayu', 'phone' => '081233334444', 'occupants' => 2, 'name_2' => 'Sinta', 'rate' => 2000000]));

        $this->get(route('owner.tenants'))->assertSee('Ahmad')->assertSee('Bayu');
        $this->get(route('owner.tenants', ['q' => 'bayu']))->assertSee('Bayu')->assertDontSee('Ahmad');
        $this->get(route('owner.tenants', ['q' => '101']))->assertSee('Ahmad')->assertDontSee('Bayu');
        $this->get(route('owner.tenants', ['q' => 'Sinta']))->assertSee('Bayu');
        $this->get(route('owner.tenants', ['q' => '0812-3333']))->assertDontSee('Ahmad'); // phone search ignores formatting only for digits
    }

    public function test_ending_a_rental_moves_the_tenant_to_alumni_and_frees_the_room(): void
    {
        [, $owner, $r101] = $this->world();
        $this->actingAs($owner);
        $this->post(route('owner.tenants.store'), $this->payload($r101));
        $tenant = Tenant::firstOrFail();

        $this->post(route('owner.tenants.end', $tenant), ['end_date' => '2026-11-10', 'room_status' => 'available', 'note' => 'pindah'])->assertSessionHasNoErrors();
        $this->post(route('owner.tenants.end', $tenant), ['end_date' => '2026-11-10', 'room_status' => 'available'])->assertNotFound(); // nothing left to end

        $this->assertSame('former', $tenant->fresh()->status);
        $this->assertSame('available', $r101->fresh()->status);
        $this->assertSame('ended', Rental::firstOrFail()->status);
        $this->assertSame('2026-11-10', Rental::firstOrFail()->end_date->toDateString());

        $this->get(route('owner.tenants'))->assertDontSee('Ahmad');
        $this->get(route('owner.tenants', ['tab' => 'alumni']))->assertSee('Ahmad');
        $this->get(route('owner.tenants', ['tab' => 'alumni', 'q' => '101']))->assertSee('Ahmad'); // still found by the room he lived in
        $this->get(route('owner.rooms.show', $r101))->assertSee('Ahmad')->assertSee('10 Nov 2026');
    }

    public function test_returning_alumnus_keeps_one_record_with_two_rentals(): void
    {
        [, $owner, $r101, $r102] = $this->world();
        $this->actingAs($owner);
        $this->post(route('owner.tenants.store'), $this->payload($r101));
        $this->post(route('owner.tenants.end', Tenant::firstOrFail()), ['end_date' => '2026-09-01', 'room_status' => 'available']);

        $this->post(route('owner.tenants.store'), $this->payload($r102, ['start_date' => '2026-10-01']))->assertSessionHasNoErrors();

        $this->assertDatabaseCount('tenants', 1);
        $this->assertSame(2, Rental::count());
        $this->get(route('owner.tenants.show', Tenant::firstOrFail()))->assertSee('101')->assertSee('102');
    }

    public function test_tenant_with_running_rental_cannot_be_added_twice(): void
    {
        [, $owner, $r101, $r102] = $this->world();
        $this->actingAs($owner);
        $this->post(route('owner.tenants.store'), $this->payload($r101));

        $this->post(route('owner.tenants.store'), $this->payload($r102))->assertSessionHasErrors('room_id');
        $this->assertSame('available', $r102->fresh()->status);
    }

    public function test_other_property_tenant_is_not_visible(): void
    {
        [, $owner] = $this->world();
        $b = Property::create(['name' => 'Kos B', 'slug' => 'b']);
        $other = Tenant::create(['property_id' => $b->id, 'name' => 'Orang Lain', 'phone' => '6281888888888', 'status' => 'active']);

        $this->actingAs($owner)->get(route('owner.tenants.show', $other))->assertNotFound();
        $this->get(route('owner.tenants', ['q' => 'Orang']))->assertDontSee('Orang Lain');
    }

    public function test_account_can_be_created_later_for_a_tenant_without_one(): void
    {
        [, $owner, $r101] = $this->world();
        $this->actingAs($owner);
        $this->post(route('owner.tenants.store'), $this->payload($r101, ['create_account' => 0]));
        $tenant = Tenant::firstOrFail();
        $this->assertNull($tenant->user_id);

        $this->post(route('owner.tenants.invitation', $tenant))->assertSessionHas('activation', fn ($a) => $a['existing'] === false);
        $this->assertNotNull($tenant->fresh()->user_id);
    }

    public function test_tenant_role_is_forbidden(): void
    {
        $this->world();
        $u = User::create(['name' => 'T', 'phone' => '6281200000050', 'role' => 'tenant', 'password' => 'rahasia123']);
        $this->actingAs($u)->get(route('owner.tenants'))->assertForbidden();
    }
}
