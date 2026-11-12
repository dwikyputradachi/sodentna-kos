<?php

namespace App\Models;

use App\Models\Concerns\BelongsToProperty;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Audit trail of room status changes. Append-only. */
class RoomStatusLog extends Model
{
    use BelongsToProperty;

    protected $fillable = ['property_id', 'room_id', 'from_status', 'to_status', 'note', 'user_id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
