<?php

namespace App\Models;

use App\Models\Concerns\BelongsToProperty;
use App\Support\Owner\Fmt;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Invoice extends Model
{
    use BelongsToProperty;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['period_start' => 'date', 'period_end' => 'date', 'due_date' => 'date', 'paid_at' => 'datetime'];
    }

    public function rental(): BelongsTo
    {
        return $this->belongsTo(Rental::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class)->whereNull('voided_at');
    }

    public function periodLabel(): string
    {
        return match ($this->kind) {
            'first' => 'Pembayaran awal',
            'daily' => Fmt::date($this->period_start) . ' – ' . Fmt::date($this->period_end),
            default => Fmt::date($this->period_start, 'F Y'),
        };
    }
}
