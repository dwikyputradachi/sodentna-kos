<?php

namespace Tests\Feature;

use App\Models\AccountInvitation;
use App\Models\Floor;
use App\Models\Property;
use App\Models\RentalApplication;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\User;
use App\Support\Invitations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ApplicationFlowTest extends TestCase
{
    use RefreshDatabase;

    private function base(int $capacity = 2, bool $daily = false): array
    {
        $p = Property::create(['name' => 'Kos A', 'slug' => 'a']);
        $owner = User::create(['name' => 'Pemilik', 'phone' => '6281200000020', 'role' => 'owner', 'password' => 'rahasia123']);
        $owner->properties()->attach($p->id);
        $f = Floor::create(['property_id' => $p->id, 'name' => 'Lantai 1']);
        $t = RoomType::create(['property_id' => $p->id, 'name' => 'Standar', 'capacity' => $capacity, 'rate_one' => 1500000, 'rate_two' => $capacity === 2 ? 2000000 : null,
            'deposit_amount' => 500000, 'allows_daily' => $daily, 'daily_rate' => $daily ? 120000 : null]);
        $room = Room::create(['property_id' => $p->id, 'floor_id' => $f->id, 'room_type_id' => $t->id, 'code' => '101', 'status' => 'available']);

        return [$p, $owner, $t, $room];
    }

    private function application(Property $p, RoomType $t, ?Room $room = null, array $o = []): RentalApplication
    {
        return RentalApplication::create($o + [
            'property_id' => $p->id, 'code' => 'SDN-' . random_int(10000, 99999), 'room_type_id' => $t->id, 'room_id' => $room?->id, 'mode' => 'monthly', 'occupants' => 2,
            'start_date' => '2030-01-05', 'payment_method' => 'transfer', 'name' => 'Budi', 'phone' => '6281234567890', 'birth_place' => 'Batam',
            'birth_date' => '2000-01-01', 'address' => 'Jl. A', 'id_number' => '3171000000000001', 'job' => 'Karyawan', 'institution' => 'PT A',
            'emergency_name' => 'Ibu', 'emergency_relation' => 'Ibu', 'emergency_phone' => '6281200000099', 'emergency_address' => 'Jl. B',
            'id_doc_path' => 'identitas/x.jpg', 'consented_at' => now(), 'status' => 'pending',
        ]);
    }

    private function payload(string $plan, Room $room, array $o = []): array
    {
        $base = ['plan' => $plan, 'room_id' => $room->id, 'start_date' => '2030-01-05', 'payment_method' => 'transfer', 'name' => 'Budi', 'id_number' => '3171000000000001',
            'phone' => '0812-3456-7890', 'address' => 'Jl. A', 'emergency_name' => 'Ibu', 'emergency_relation' => 'Ibu', 'emergency_phone' => '081200000099',
            'id_doc' => UploadedFile::fake()->create('ktp.pdf', 100, 'application/pdf'), 'consent_rules' => 1, 'consent_data' => 1];
        if ($plan !== 'daily') $base += ['birth_place' => 'Batam', 'birth_date' => '2000-01-01', 'job_choice' => 'Karyawan', 'institution' => 'PT A', 'emergency_address' => 'Jl. B'];
        if ($plan === 'two') $base += ['name_2' => 'Sinta', 'couple_ack' => 1];
        if ($plan === 'daily') $base += ['end_date' => '2030-01-08'];

        return $o + $base;
    }

    public function test_public_pages_render(): void
    {
        [, , $t, $room] = $this->base();
        foreach (['/', '/ketersediaan-kamar', '/kamar/' . $t->id, '/pengajuan-sewa', '/pengajuan-sewa?paket=two&kamar=' . $room->id, '/status-pengajuan', '/daftar-tunggu'] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_availability_page_links_available_rooms_to_the_form(): void
    {
        [, , , $room] = $this->base();
        $this->get('/ketersediaan-kamar')->assertSee('kamar=' . $room->id, false);
        $room->update(['status' => 'occupied']);
        $this->get('/ketersediaan-kamar')->assertDontSee('kamar=' . $room->id, false);
    }

    public function test_applicant_picks_the_room_and_identity_is_private(): void
    {
        Storage::fake();
        [, , , $room] = $this->base();

        $this->post(route('site.apply.store'), $this->payload('one', $room))->assertSessionHasNoErrors()->assertSessionHas('done');

        $app = RentalApplication::withoutGlobalScopes()->firstOrFail();
        $this->assertSame($room->id, $app->room_id);
        $this->assertSame(1, $app->occupants);
        $this->assertSame('6281234567890', $app->phone);
        Storage::assertExists($app->id_doc_path);
        $this->assertNotSame('3171000000000001', DB::table('rental_applications')->value('id_number')); // encrypted at rest
    }

    public function test_applicant_cannot_pick_an_unavailable_room(): void
    {
        [, , , $room] = $this->base();
        $room->update(['status' => 'occupied']);

        $this->post(route('site.apply.store'), $this->payload('one', $room))->assertSessionHasErrors('room_id');
        $this->assertDatabaseCount('rental_applications', 0);
    }

    public function test_room_must_suit_the_plan(): void
    {
        [, , , $single] = $this->base(1, false);
        $this->post(route('site.apply.store'), $this->payload('two', $single))->assertSessionHasErrors('room_id');   // capacity 1
        $this->post(route('site.apply.store'), $this->payload('daily', $single))->assertSessionHasErrors('room_id'); // no daily rental
    }

    public function test_two_person_application_needs_second_name_and_acknowledgement(): void
    {
        Storage::fake();
        [, , , $room] = $this->base();

        $this->post(route('site.apply.store'), $this->payload('two', $room, ['name_2' => '']))->assertSessionHasErrors('name_2');
        $this->post(route('site.apply.store'), $this->payload('two', $room, ['couple_ack' => null]))->assertSessionHasErrors('couple_ack');
        $this->post(route('site.apply.store'), $this->payload('two', $room))->assertSessionHasNoErrors();

        $app = RentalApplication::withoutGlobalScopes()->firstOrFail();
        $this->assertSame(2, $app->occupants);
        $this->assertSame('Sinta', $app->name_2);
    }

    public function test_daily_application_uses_short_form_and_needs_end_date(): void
    {
        Storage::fake();
        [, , , $room] = $this->base(2, true);

        $this->post(route('site.apply.store'), $this->payload('daily', $room, ['end_date' => null]))->assertSessionHasErrors('end_date');
        $this->post(route('site.apply.store'), $this->payload('daily', $room))->assertSessionHasNoErrors();

        $app = RentalApplication::withoutGlobalScopes()->firstOrFail();
        $this->assertSame('daily', $app->mode);
        $this->assertNull($app->birth_date);
    }

    public function test_owner_approves_the_room_the_applicant_picked(): void
    {
        [$p, $owner, $t, $room] = $this->base();
        $app = $this->application($p, $t, $room, ['name_2' => 'Sinta']);
        $this->actingAs($owner);

        $this->post(route('owner.applications.approve', $app), [])->assertSessionHasNoErrors()->assertSessionHas('activation');
        $this->post(route('owner.applications.approve', $app), [])->assertSessionHasErrors('room_id'); // double click

        $this->assertDatabaseCount('tenants', 1);
        $this->assertDatabaseCount('rentals', 1);
        $this->assertDatabaseHas('rentals', ['rate' => 2000000, 'deposit_amount' => 500000, 'status' => 'reserved', 'billing_anchor_day' => 5, 'co_occupant_name' => 'Sinta']);
        $this->assertSame('reserved', $room->fresh()->status);
        $this->assertSame('approved', $app->fresh()->status);
        $this->assertNull(User::where('phone', '6281234567890')->first()->password);
        $this->assertDatabaseCount('account_invitations', 1);
    }

    public function test_second_applicant_for_the_same_room_cannot_also_be_approved(): void
    {
        [$p, $owner, $t, $room] = $this->base();
        $first = $this->application($p, $t, $room);
        $second = $this->application($p, $t, $room, ['phone' => '6281999999991']);
        $this->actingAs($owner);

        $this->post(route('owner.applications.approve', $first), [])->assertSessionHasNoErrors();
        $this->post(route('owner.applications.approve', $second), [])->assertSessionHasErrors('room_id');
        $this->assertSame('pending', $second->fresh()->status);
    }

    public function test_owner_can_pick_a_replacement_room_when_requested_one_is_gone(): void
    {
        [$p, $owner, $t, $room] = $this->base();
        $other = Room::create(['property_id' => $p->id, 'floor_id' => $room->floor_id, 'room_type_id' => $t->id, 'code' => '102', 'status' => 'available']);
        $app = $this->application($p, $t, $room);
        $room->update(['status' => 'occupied']);
        $this->actingAs($owner);

        $this->post(route('owner.applications.approve', $app), [])->assertSessionHasErrors('room_id');
        $this->post(route('owner.applications.approve', $app), ['room_id' => $other->id])->assertSessionHasNoErrors();
        $this->assertSame('reserved', $other->fresh()->status);
    }

    public function test_cannot_approve_into_too_small_room(): void
    {
        [$p, $owner, $t, $room] = $this->base();
        $small = RoomType::create(['property_id' => $p->id, 'name' => 'Single', 'capacity' => 1, 'rate_one' => 1500000]);
        $room->update(['room_type_id' => $small->id]);
        $app = $this->application($p, $t, $room);

        $this->actingAs($owner)->post(route('owner.applications.approve', $app), [])->assertSessionHasErrors('room_id');
        $this->assertSame('pending', $app->fresh()->status);
    }

    public function test_existing_active_account_gets_a_notification_not_an_activation_link(): void
    {
        [$p, $owner, $t, $room] = $this->base();
        User::create(['name' => 'Lama', 'phone' => '6281234567890', 'role' => 'tenant', 'password' => 'sudah-punya-123']);
        $app = $this->application($p, $t, $room);

        $this->actingAs($owner)->post(route('owner.applications.approve', $app), [])->assertSessionHas('activation', fn ($a) => $a['existing'] === true);
        $this->assertDatabaseCount('account_invitations', 0);

        $this->post(route('owner.applications.invitation', $app))->assertSessionHas('activation', fn ($a) => $a['existing'] === true);
    }

    public function test_reject_needs_reason(): void
    {
        [$p, $owner, $t] = $this->base();
        $app = $this->application($p, $t);
        $this->actingAs($owner);

        $this->post(route('owner.applications.reject', $app), [])->assertSessionHasErrors('reason');
        $this->post(route('owner.applications.reject', $app), ['reason' => 'Kamar penuh'])->assertSessionHasNoErrors();
        $this->assertSame('rejected', $app->fresh()->status);
    }

    public function test_activation_link_is_single_use_and_expires(): void
    {
        [$p, $owner, $t, $room] = $this->base();
        $app = $this->application($p, $t, $room);
        $this->actingAs($owner)->post(route('owner.applications.approve', $app), []);
        auth()->logout();

        $user = User::where('phone', '6281234567890')->first();
        $token = basename(Invitations::issue($user));

        $this->get(route('activation', $token))->assertOk()->assertSee('Aktivasi Akun');
        $this->post(route('activation.store', $token), ['password' => 'passwordku123', 'password_confirmation' => 'passwordku123'])->assertRedirect(route('tenant.home'));
        $this->assertNotNull($user->fresh()->password);
        $this->post(route('activation.store', $token), ['password' => 'lain-lagi-123', 'password_confirmation' => 'lain-lagi-123'])->assertNotFound();

        AccountInvitation::create(['user_id' => $user->id, 'token_hash' => hash('sha256', 'kedaluwarsa'), 'expires_at' => now()->subDay()]);
        $this->get(route('activation', 'kedaluwarsa'))->assertSee('Tautan Tidak Berlaku');
    }

    public function test_status_page_shows_room_and_whether_the_account_is_active(): void
    {
        [$p, $owner, $t, $room] = $this->base();
        $app = $this->application($p, $t, $room);

        $this->post(route('site.status.check'), ['code' => $app->code, 'last4' => '7890'])->assertSee('Menunggu ditinjau');
        $this->post(route('site.status.check'), ['code' => $app->code, 'last4' => '0000'])->assertSee('tidak cocok');

        $this->actingAs($owner)->post(route('owner.applications.approve', $app), []);
        auth()->logout();
        $this->post(route('site.status.check'), ['code' => $app->code, 'last4' => '7890'])->assertSee('Kamar 101')->assertSee('menunggu diaktifkan');

        User::where('phone', '6281234567890')->first()->forceFill(['password' => 'passwordku123'])->save();
        $this->post(route('site.status.check'), ['code' => $app->code, 'last4' => '7890'])->assertSee('sudah aktif');
    }

    public function test_check_in_marks_room_occupied(): void
    {
        [$p, $owner, $t, $room] = $this->base();
        $app = $this->application($p, $t, $room);
        $this->actingAs($owner);
        $this->post(route('owner.applications.approve', $app), []);

        $this->post(route('owner.applications.checkin', $app))->assertSessionHasNoErrors();
        $this->assertSame('occupied', $room->fresh()->status);
        $this->assertDatabaseHas('rentals', ['status' => 'active']);
        $this->assertDatabaseHas('tenants', ['status' => 'active']);
    }

    public function test_owner_cannot_open_other_property_application(): void
    {
        [, $owner] = $this->base();
        $b = Property::create(['name' => 'Kos B', 'slug' => 'b']);
        $tb = RoomType::create(['property_id' => $b->id, 'name' => 'B', 'capacity' => 1, 'rate_one' => 1]);
        $other = $this->application($b, $tb, null, ['phone' => '6281999999999']);

        $this->actingAs($owner)->get(route('owner.applications.show', $other))->assertNotFound();
    }
}
