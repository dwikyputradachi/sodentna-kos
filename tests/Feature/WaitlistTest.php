<?php

namespace Tests\Feature;

use App\Models\Property;
use App\Models\RoomType;
use App\Models\User;
use App\Models\WaitingListEntry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class WaitlistTest extends TestCase
{
    use RefreshDatabase;

    private function setupProperty(int $capacity = 2): array
    {
        $p = Property::create(['name' => 'Kos A', 'slug' => 'a']);
        $t = RoomType::create(['property_id' => $p->id, 'name' => 'Standar', 'capacity' => $capacity, 'rate_one' => 1500000, 'rate_two' => $capacity === 2 ? 2000000 : null]);

        return [$p, $t];
    }

    private function payload(array $o = []): array
    {
        return $o + ['name' => 'Budi', 'phone' => '0812-3456-7890', 'occupants' => 1, 'desired_start' => Carbon::today('Asia/Jakarta')->addDays(5)->toDateString()];
    }

    public function test_visitor_can_join_waiting_list(): void
    {
        [$p, $t] = $this->setupProperty();
        $this->post(route('site.waitlist.store'), $this->payload(['room_type_id' => $t->id]))->assertSessionHasNoErrors()->assertSessionHas('done');
        $this->assertDatabaseHas('waiting_list_entries', ['property_id' => $p->id, 'phone' => '6281234567890', 'status' => 'waiting']);
    }

    public function test_same_person_is_not_queued_twice(): void
    {
        [, $t] = $this->setupProperty();
        $this->post(route('site.waitlist.store'), $this->payload(['room_type_id' => $t->id]));
        $this->post(route('site.waitlist.store'), $this->payload(['room_type_id' => $t->id]));
        $this->assertDatabaseCount('waiting_list_entries', 1);
    }

    public function test_two_occupants_need_acknowledgement_and_respect_capacity(): void
    {
        [, $t] = $this->setupProperty(2);
        $this->post(route('site.waitlist.store'), $this->payload(['occupants' => 2, 'room_type_id' => $t->id]))->assertSessionHasErrors('couple_ack');

        [, $single] = [null, RoomType::create(['property_id' => Property::first()->id, 'name' => 'Single', 'capacity' => 1, 'rate_one' => 1500000])];
        $this->post(route('site.waitlist.store'), $this->payload(['occupants' => 2, 'couple_ack' => 1, 'room_type_id' => $single->id]))->assertSessionHasErrors('occupants');
    }

    public function test_past_start_date_is_rejected(): void
    {
        $this->setupProperty();
        $this->post(route('site.waitlist.store'), $this->payload(['desired_start' => '2020-01-01']))->assertSessionHasErrors('desired_start');
    }

    public function test_owner_manages_only_own_property_entries(): void
    {
        [$p, $t] = $this->setupProperty();
        $owner = User::create(['name' => 'Pemilik', 'phone' => '6281200000010', 'role' => 'owner', 'password' => 'rahasia123']);
        $owner->properties()->attach($p->id);

        $mine = WaitingListEntry::create(['property_id' => $p->id, 'code' => 'DT-10001', 'name' => 'A', 'phone' => '6281', 'room_type_id' => $t->id, 'occupants' => 1, 'desired_start' => '2030-01-01']);
        $other = Property::create(['name' => 'Kos B', 'slug' => 'b']);
        $theirs = WaitingListEntry::create(['property_id' => $other->id, 'code' => 'DT-10002', 'name' => 'B', 'phone' => '6282', 'occupants' => 1, 'desired_start' => '2030-01-01']);

        $this->actingAs($owner);
        $this->get(route('owner.waitlist'))->assertOk()->assertSee('DT-10001')->assertDontSee('DT-10002');
        $this->post(route('owner.waitlist.status', $mine), ['status' => 'offered'])->assertSessionHasNoErrors();
        $this->assertSame('offered', $mine->fresh()->status);
        $this->post(route('owner.waitlist.status', $theirs), ['status' => 'offered'])->assertNotFound();
    }
}
