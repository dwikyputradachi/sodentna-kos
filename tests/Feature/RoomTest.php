<?php

namespace Tests\Feature;

use App\Models\Floor;
use App\Models\Property;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoomTest extends TestCase
{
    use RefreshDatabase;

    private function owner(string $slug = 'a', string $phone = '6281211111111'): array
    {
        $p = Property::create(['name' => strtoupper($slug), 'slug' => $slug]);
        $u = User::create(['name' => 'Pemilik', 'phone' => $phone, 'role' => 'owner', 'password' => 'rahasia123']);
        $u->properties()->attach($p->id);

        return [$u, $p];
    }

    private function seedRoom(Property $p, string $code = '101'): Room
    {
        $f = Floor::create(['property_id' => $p->id, 'name' => 'Lantai ' . $code]);
        $t = RoomType::create(['property_id' => $p->id, 'name' => 'T' . $code, 'capacity' => 1, 'rate_one' => 1500000]);

        return Room::create(['property_id' => $p->id, 'floor_id' => $f->id, 'room_type_id' => $t->id, 'code' => $code, 'status' => 'available']);
    }

    public function test_owner_can_create_floor_type_and_room(): void
    {
        [$u, $p] = $this->owner();
        $this->actingAs($u);

        $this->post(route('owner.floors.store'), ['name' => 'Lantai 1'])->assertSessionHasNoErrors();
        $this->post(route('owner.types.store'), ['name' => 'Standar', 'capacity' => 2, 'rate_one' => 1500000, 'rate_two' => 2000000, 'deposit_amount' => 500000])->assertSessionHasNoErrors();

        $this->post(route('owner.rooms.store'), ['code' => '101', 'floor_id' => Floor::first()->id, 'room_type_id' => RoomType::first()->id])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('rooms', ['code' => '101', 'property_id' => $p->id, 'status' => 'available']);
        $this->get(route('owner.rooms'))->assertOk()->assertSee('101');
    }

    public function test_two_person_type_requires_second_rate(): void
    {
        [$u] = $this->owner();
        $this->actingAs($u)->post(route('owner.types.store'), ['name' => 'Double', 'capacity' => 2, 'rate_one' => 1500000, 'deposit_amount' => 500000])
            ->assertSessionHasErrors('rate_two');
    }

    public function test_duplicate_room_code_is_rejected_within_a_property(): void
    {
        [$u, $p] = $this->owner();
        $room = $this->seedRoom($p, '101');
        $this->actingAs($u)->post(route('owner.rooms.store'), ['code' => '101', 'floor_id' => $room->floor_id, 'room_type_id' => $room->room_type_id])
            ->assertSessionHasErrors('code');
    }

    public function test_status_change_is_logged_once(): void
    {
        [$u, $p] = $this->owner();
        $room = $this->seedRoom($p);
        $this->actingAs($u);

        $this->post(route('owner.rooms.status', $room), ['status' => 'maintenance', 'note' => 'AC rusak']);
        $this->post(route('owner.rooms.status', $room), ['status' => 'maintenance']); // double click

        $this->assertSame('maintenance', $room->fresh()->status);
        $this->assertDatabaseCount('room_status_logs', 1);
    }

    public function test_room_of_another_property_is_not_accessible(): void
    {
        [$u] = $this->owner('a', '6281211111111');
        [, $b] = $this->owner('b', '6281222222222');
        $other = $this->seedRoom($b, '901');

        $this->actingAs($u)->get(route('owner.rooms.show', $other))->assertNotFound();
        $this->post(route('owner.rooms.status', $other), ['status' => 'maintenance'])->assertNotFound();
    }

    public function test_tenant_cannot_open_rooms_page(): void
    {
        $t = User::create(['name' => 'Penghuni', 'phone' => '6281233333333', 'role' => 'tenant', 'password' => 'rahasia123']);
        $this->actingAs($t)->get(route('owner.rooms'))->assertForbidden();
    }
}
