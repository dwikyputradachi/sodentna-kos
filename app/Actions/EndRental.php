<?php

namespace App\Actions;

use App\Models\Rental;
use App\Models\Room;
use App\Models\RoomStatusLog;
use App\Models\Tenant;
use App\Models\User;
use DomainException;
use Illuminate\Support\Facades\DB;

/**
 * End a rental: the tenant becomes an alumnus, history stays untouched, the room is released.
 * Deposit settlement is intentionally not handled here (separate deposit feature).
 */
class EndRental
{
    public function handle(Rental $rental, string $endDate, string $roomStatus, ?string $note, User $by): void
    {
        DB::transaction(function () use ($rental, $endDate, $roomStatus, $note, $by) {
            $r = Rental::lockForUpdate()->findOrFail($rental->id);
            if (! in_array($r->status, ['reserved', 'active', 'pending_checkout'], true)) throw new DomainException('Sewa ini sudah berakhir.');

            $room = Room::lockForUpdate()->findOrFail($r->room_id);
            $from = $room->status;

            $r->update(['status' => 'ended', 'end_date' => $endDate]);
            $room->update(['status' => $roomStatus]);
            RoomStatusLog::create(['property_id' => $room->property_id, 'room_id' => $room->id, 'from_status' => $from, 'to_status' => $roomStatus,
                'note' => trim('Sewa berakhir. ' . ($note ?? '')), 'user_id' => $by->id]);

            $stillRenting = Rental::where('tenant_id', $r->tenant_id)->where('id', '!=', $r->id)->whereIn('status', ['reserved', 'active', 'pending_checkout'])->exists();
            if (! $stillRenting) Tenant::whereKey($r->tenant_id)->update(['status' => 'former']);
        });
    }
}
