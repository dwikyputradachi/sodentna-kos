<?php

namespace App\Actions;

use App\Models\Rental;
use App\Models\Room;
use App\Models\RoomStatusLog;
use App\Models\Tenant;
use App\Models\User;
use App\Support\Invitations;
use App\Support\Owner\Billing;
use DomainException;
use Illuminate\Support\Facades\DB;

/** Record a tenant who is already living in a room (e.g. moved over from the Excel sheet). Monthly rental, active immediately. */
class CreateTenantManually
{
    /** @return array{0: Tenant, 1: Rental, 2: ?string} [tenant, rental, activation url or null] */
    public function handle(array $d, User $by): array
    {
        return DB::transaction(function () use ($d, $by) {
            $room = Room::lockForUpdate()->with('type')->findOrFail($d['room_id']);
            if ($room->status !== 'available') throw new DomainException('Kamar ' . $room->code . ' tidak tersedia.');
            if ((int) $d['occupants'] > $room->type->capacity) throw new DomainException('Kamar ' . $room->code . ' hanya untuk ' . $room->type->capacity . ' orang.');

            $phone = Billing::phone($d['phone']);
            $tenant = Tenant::where('property_id', $room->property_id)->where('phone', $phone)->first();
            if ($tenant && Rental::where('tenant_id', $tenant->id)->whereIn('status', ['reserved', 'active', 'pending_checkout'])->exists()) {
                throw new DomainException('Penghuni ini masih memiliki sewa berjalan.');
            }

            $user = null;
            if (! empty($d['create_account'])) {
                $user = User::where('phone', $phone)->first();
                if ($user && $user->role !== User::TENANT) throw new DomainException('Nomor WhatsApp ini terdaftar sebagai akun pengelola.');
                $user ??= User::create(['name' => $d['name'], 'phone' => $phone, 'role' => User::TENANT]);
            }

            $data = [
                'name' => $d['name'], 'birth_place' => $d['birth_place'] ?? null, 'birth_date' => $d['birth_date'] ?? null, 'address' => $d['address'] ?? null,
                'id_number' => $d['id_number'] ?? null, 'job' => $d['job'] ?? null, 'institution' => $d['institution'] ?? null,
                'emergency_name' => $d['emergency_name'] ?? null, 'emergency_relation' => $d['emergency_relation'] ?? null,
                'emergency_phone' => ! empty($d['emergency_phone']) ? Billing::phone($d['emergency_phone']) : null, 'emergency_address' => $d['emergency_address'] ?? null,
                'user_id' => $user?->id, 'status' => 'active',
            ];

            if ($tenant) { // returning (former) tenant: keep existing details, only fill in what was provided
                $tenant->update(array_filter($data, fn ($v) => $v !== null && $v !== ''));
            } else {
                $tenant = Tenant::create($data + ['property_id' => $room->property_id, 'phone' => $phone]);
            }

            $start = \Illuminate\Support\Carbon::parse($d['start_date'], 'Asia/Jakarta');
            $nextDue = ! empty($d['next_due_date'])
                ? \Illuminate\Support\Carbon::parse($d['next_due_date'], 'Asia/Jakarta')
                : \App\Support\BillingSchedule::upcoming(\Illuminate\Support\Carbon::today('Asia/Jakarta'), $start->day);
            $rental = Rental::create([
                'property_id' => $room->property_id, 'tenant_id' => $tenant->id, 'room_id' => $room->id, 'mode' => 'monthly',
                'occupants' => (int) $d['occupants'], 'start_date' => $start, 'rate' => (int) $d['rate'], 'deposit_amount' => (int) $d['deposit_amount'],
                'billing_anchor_day' => $start->day, 'next_due_date' => $nextDue->toDateString(), 'co_occupant_name' => $d['name_2'] ?? null, 'status' => 'active',
            ]);

            $from = $room->status;
            $room->update(['status' => 'occupied']);
            RoomStatusLog::create(['property_id' => $room->property_id, 'room_id' => $room->id, 'from_status' => $from, 'to_status' => 'occupied',
                'note' => 'Penghuni dicatat manual: ' . $tenant->name, 'user_id' => $by->id]);

            $account = $tenant->user_id ? User::find($tenant->user_id) : null;
            $url = $account && ! $account->password ? Invitations::issue($account) : null;

            return [$tenant, $rental, $url];
        });
    }
}
