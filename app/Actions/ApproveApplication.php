<?php

namespace App\Actions;

use App\Models\Rental;
use App\Models\RentalApplication;
use App\Models\Room;
use App\Models\RoomStatusLog;
use App\Models\Tenant;
use App\Models\User;
use App\Support\Invitations;
use DomainException;
use Illuminate\Support\Facades\DB;

/** Approve = create/update tenant + login account + reserved rental, and mark the room as reserved. One transaction. */
class ApproveApplication
{
    /** @return array{0: Rental, 1: ?string, 2: User} [rental, activation url (null if account already active), user] */
    public function handle(RentalApplication $application, int $roomId, User $by): array
    {
        return DB::transaction(function () use ($application, $roomId, $by) {
            $app = RentalApplication::lockForUpdate()->findOrFail($application->id);
            if ($app->status !== 'pending') throw new DomainException('Pengajuan ini sudah diproses.');

            $room = Room::lockForUpdate()->with('type')->findOrFail($roomId);
            if ($room->status !== 'available') throw new DomainException('Kamar tidak lagi tersedia.');
            if ($app->occupants > $room->type->capacity) throw new DomainException('Jumlah penghuni melebihi kapasitas kamar.');
            if ($app->mode === 'daily' && (! $room->type->allows_daily || $app->occupants > 1)) {
                throw new DomainException('Sewa harian hanya untuk 1 orang dan pada kamar yang mengizinkan sewa harian.');
            }

            $user = User::where('phone', $app->phone)->first();
            if ($user && $user->role !== User::TENANT) throw new DomainException('Nomor WhatsApp ini terdaftar sebagai akun pengelola.');
            $user ??= User::create(['name' => $app->name, 'phone' => $app->phone, 'role' => User::TENANT]);

            $tenant = Tenant::updateOrCreate(['property_id' => $app->property_id, 'phone' => $app->phone], [
                'user_id' => $user->id, 'name' => $app->name, 'birth_place' => $app->birth_place, 'birth_date' => $app->birth_date,
                'address' => $app->address, 'id_number' => $app->id_number, 'job' => $app->job, 'institution' => $app->institution,
                'emergency_name' => $app->emergency_name, 'emergency_relation' => $app->emergency_relation,
                'emergency_phone' => $app->emergency_phone, 'emergency_address' => $app->emergency_address,
                'id_doc_path' => $app->id_doc_path, 'status' => 'applicant',
            ]);

            $monthly = $app->mode === 'monthly';
            $rental = Rental::create([
                'property_id' => $app->property_id, 'tenant_id' => $tenant->id, 'room_id' => $room->id, 'application_id' => $app->id,
                'mode' => $app->mode, 'occupants' => $app->occupants, 'start_date' => $app->start_date, 'end_date' => $app->end_date,
                'rate' => $monthly ? $room->type->monthlyRate($app->occupants) : (int) $room->type->daily_rate,
                'deposit_amount' => $room->type->deposit_amount, 'billing_anchor_day' => $monthly ? $app->start_date->day : null,
                'status' => 'reserved',
            ]);

            $from = $room->status;
            $room->update(['status' => 'reserved']);
            RoomStatusLog::create(['property_id' => $room->property_id, 'room_id' => $room->id, 'from_status' => $from, 'to_status' => 'reserved',
                'note' => 'Pengajuan ' . $app->code . ' disetujui', 'user_id' => $by->id]);

            $app->update(['status' => 'approved', 'room_id' => $room->id, 'tenant_id' => $tenant->id, 'reviewed_by' => $by->id, 'reviewed_at' => now()]);

            return [$rental, $user->password ? null : Invitations::issue($user), $user];
        });
    }
}
