<?php

namespace App\Models;

use App\Models\Concerns\BelongsToProperty;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Room extends Model
{
    use BelongsToProperty;

    public const STATUSES = ['available' => 'Tersedia', 'reserved' => 'Dipesan', 'occupied' => 'Terisi', 'maintenance' => 'Dalam Perbaikan'];

    protected $fillable = ['property_id', 'floor_id', 'room_type_id', 'code', 'status', 'notes'];

    public function floor(): BelongsTo
    {
        return $this->belongsTo(Floor::class);
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(RoomType::class, 'room_type_id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(RoomStatusLog::class)->latest('id');
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }
}
