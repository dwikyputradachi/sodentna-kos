<?php

namespace App\Models;

use App\Models\Concerns\BelongsToProperty;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WaitingListEntry extends Model
{
    use BelongsToProperty;

    public const STATUSES = ['waiting' => 'Menunggu', 'offered' => 'Ditawari', 'converted' => 'Selesai', 'cancelled' => 'Dibatalkan', 'expired' => 'Kedaluwarsa'];

    protected $fillable = ['property_id', 'code', 'name', 'phone', 'room_type_id', 'occupants', 'desired_start', 'notes', 'status', 'offered_at'];

    protected function casts(): array
    {
        return ['desired_start' => 'date', 'offered_at' => 'datetime'];
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(RoomType::class, 'room_type_id');
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    /** First come, first served, per room type (null = any type). Public-safe: returns a number only. */
    public static function positionOf(self $e): int
    {
        return static::withoutGlobalScopes()->where('property_id', $e->property_id)->where('status', 'waiting')
            ->where('room_type_id', $e->room_type_id)->where('id', '<=', $e->id)->count();
    }
}
