<?php

namespace App\Models;

use App\Models\Concerns\BelongsToProperty;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RentalApplication extends Model
{
    use BelongsToProperty;

    public const STATUSES = ['pending' => 'Menunggu', 'approved' => 'Disetujui', 'rejected' => 'Ditolak'];

    protected $guarded = ['id'];

    protected $hidden = ['id_number'];

    protected function casts(): array
    {
        return [
            'start_date' => 'date', 'end_date' => 'date', 'birth_date' => 'date', 'consented_at' => 'datetime',
            'reviewed_at' => 'datetime', 'id_number' => 'encrypted',
        ];
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(RoomType::class, 'room_type_id');
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function maskedId(): string
    {
        $v = (string) $this->id_number;

        return strlen($v) > 6 ? substr($v, 0, 4) . str_repeat('•', strlen($v) - 8) . substr($v, -4) : str_repeat('•', strlen($v));
    }
}
