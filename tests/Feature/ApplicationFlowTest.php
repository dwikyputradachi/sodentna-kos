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

    private function base(int $capacity = 2): array
    {
        $p = Property::create(['name' => 'Kos A', 'slug' => 'a']);
        $owner = User::create(['name' => 'Pemilik', 'phone' => '6281200000020', 'role' => 'owner', 'password' => 'rahasia123']);
        $owner->properties()->attach($p->id);
        $f = Floor::create(['property_id' => $p->id, 'name' => 'Lantai 1']);
        $t = RoomType::create(['property_id' => $p->id, 'name' => 'Standar', 'capacity' => $capacity, 'rate_one' => 1500000, 'rate_two' => $capacity === 2 ? 2000000 : null, 'deposit_amount' => 500000]);
        $room = Room::create(['property_id' => $p->id, 'floor_id' => $f->id, 'room_type_id' => $t->id, 'code' => '101', 'status' => 'available']);

        return [$p, $owner, $t, $room];
    }

    private function application(Property $p, RoomType $t, array $o = []): RentalApplication
    {
        return RentalApplication::create($o + [
            'property_id' => $p->id, 'code' => 'SDN-' . random_int(10000, 99999), 'room_type_id' => $t->id, 'mode' => 'monthly', 'occupants' => 2,
            'start_date' => '2030-01-05', 'payment_method' => 'transfer', 'name' => 'Budi', 'phone' => '6281234567890', 'birth_place' => 'Batam',
            'birth_date' => '2000-01-01', 'address' => 'Jl. A', 'id_number' => '3171000000000001', 'job' => 'Karyawan', 'institution' => 'PT A',
            'emergency_name' => 'Ibu', 'emergency_relation' => 'Ibu', 'emergency_phone' => '6281200000099', 'emergency_address' => 'Jl. B',
            'id_doc_path' => 'identitas/x.jpg', 'consented_at' => now(), 'status' => 'pending',
        ]);
    }

    public function test_public_pages_render(): void
    {
        [, , $t] = $this->base();
        foreach (['/', '/ketersediaan-kamar', '/kamar/' . $t->id, '/pengajuan-sewa', '/status-pengajuan', '/daftar-tunggu'] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_visitor_submits_application_and_identity_is_private(): void
    {
        Storage::fake();
        [, , $t] = $this->base();

        $this->post(route('site.apply.store'), [
            'room_type_id' => $t->id, 'mode' => 'monthly', 'occupants' => 2, 'start_date' => '2030-01-05', 'payment_method' => 'transfer',
            'name' => 'Budi', 'birth_place' => 'Batam', 'birth_date' => '2000-01-01', 'id_number' => '3171000000000001', 'phone' => '0812-3456-7890',
            'job' => 'Karyawan', 'institution' => 'PT A', 'address' => 'Jl. A', 'emergency_name' => 'Ibu', 'emergency_relation' => 'Ibu',
            'emergency_phone' => '081200000099', 'emergency_address' => 'Jl. B', 'id_doc' => UploadedFile::fake()->create('ktp.pdf', 100, 'application/pdf'),
            'consent_rules' => 1, 'consent_data' => 1, 'couple_ack' => 1,
        ])->assertSessionHasNoErrors()->assertSessionHas('done');

        $app = RentalApplication::withoutGlobalScopes()->firstOrFail();
        $this->assertSame('6281234567890', $app->phone);
        Storage::assertExists($app->id_doc_path);
        $this->assertNotSame('3171000000000001', DB::table('rental_applications')->value('id_number')); // encrypted at rest
        $this->assertSame('3171000000000001', $app->id_number);
    }

    public function test_approve_creates_tenant_account_rental_once(): void
    {
        [$p, $owner, $t, $room] = $this->base();
        $app = $this->application($p, $t);
        $this->actingAs($owner);

        $this->post(route('owner.applications.approve', $app), ['room_id' => $room->id])->assertSessionHasNoErrors()->assertSessionHas('activation');
        $this->post(route('owner.applications.approve', $app), ['room_id' => $room->id])->assertSessionHasErrors('room_id'); // double click

        $this->assertDatabaseCount('tenants', 1);
        $this->assertDatabaseCount('rentals', 1);
        $this->assertDatabaseHas('rentals', ['rate' => 2000000, 'deposit_amount' => 500000, 'status' => 'reserved', 'billing_anchor_day' => 5]);
        $this->assertSame('reserved', $room->fresh()->status);
        $this->assertSame('approved', $app->fresh()->status);
        $this->assertNull(User::where('phone', '6281234567890')->first()->password);
        $this->assertDatabaseCount('account_invitations', 1);
    }

    public function test_cannot_approve_into_unavailable_or_too_small_room(): void
    {
        [$p, $owner, $t, $room] = $this->base();
        $this->actingAs($owner);

        $room->update(['status' => 'occupied']);
        $app = $this->application($p, $t);
        $this->post(route('owner.applications.approve', $app), ['room_id' => $room->id])->assertSessionHasErrors('room_id');

        $room->update(['status' => 'available']);
        $small = RoomType::create(['property_id' => $p->id, 'name' => 'Single', 'capacity' => 1, 'rate_one' => 1500000]);
        $room->update(['room_type_id' => $small->id]);
        $this->post(route('owner.applications.approve', $app), ['room_id' => $room->id])->assertSessionHasErrors('room_id');
        $this->assertSame('pending', $app->fresh()->status);
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
        $app = $this->application($p, $t);
        $this->actingAs($owner)->post(route('owner.applications.approve', $app), ['room_id' => $room->id]);
        auth()->logout();

        $user = User::where('phone', '6281234567890')->first();
        $token = basename(Invitations::issue($user));

        $this->get(route('activation', $token))->assertOk()->assertSee('Aktivasi akun');
        $this->post(route('activation.store', $token), ['password' => 'passwordku123', 'password_confirmation' => 'passwordku123'])->assertRedirect(route('tenant.home'));
        $this->assertNotNull($user->fresh()->password);
        $this->post(route('activation.store', $token), ['password' => 'lain-lagi-123', 'password_confirmation' => 'lain-lagi-123'])->assertNotFound();

        AccountInvitation::create(['user_id' => $user->id, 'token_hash' => hash('sha256', 'kedaluwarsa'), 'expires_at' => now()->subDay()]);
        $this->get(route('activation', 'kedaluwarsa'))->assertSee('Tautan tidak berlaku');
    }

    public function test_status_check_needs_code_and_last_digits(): void
    {
        [$p, , $t] = $this->base();
        $app = $this->application($p, $t);

        $this->post(route('site.status.check'), ['code' => $app->code, 'last4' => '7890'])->assertSee('Menunggu ditinjau');
        $this->post(route('site.status.check'), ['code' => $app->code, 'last4' => '0000'])->assertSee('tidak cocok');
    }

    public function test_check_in_marks_room_occupied(): void
    {
        [$p, $owner, $t, $room] = $this->base();
        $app = $this->application($p, $t);
        $this->actingAs($owner);
        $this->post(route('owner.applications.approve', $app), ['room_id' => $room->id]);

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
        $other = $this->application($b, $tb, ['phone' => '6281999999999']);

        $this->actingAs($owner)->get(route('owner.applications.show', $other))->assertNotFound();
    }
}
