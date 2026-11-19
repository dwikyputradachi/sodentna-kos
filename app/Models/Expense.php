<?php

namespace App\Models;

use App\Models\Concerns\BelongsToProperty;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    use BelongsToProperty;

    public const CATEGORIES = ['Listrik & air', 'Kebersihan', 'Perbaikan & pemeliharaan', 'Perlengkapan', 'Internet', 'Upah / gaji', 'Pajak & iuran', 'Lainnya'];

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['spent_on' => 'date', 'voided_at' => 'datetime'];
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }
}
