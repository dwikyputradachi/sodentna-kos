<?php

namespace App\Models;

use App\Models\Concerns\BelongsToProperty;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model
{
    use BelongsToProperty;

    protected $fillable = ['property_id', 'user_id', 'name', 'phone', 'birth_place', 'birth_date', 'address', 'id_number', 'job', 'institution',
        'emergency_name', 'emergency_relation', 'emergency_phone', 'emergency_address', 'id_doc_path', 'status', 'religion'];

    protected $hidden = ['id_number'];

    protected function casts(): array
    {
        return ['birth_date' => 'date', 'id_number' => 'encrypted'];
    }

    public function rentals(): HasMany
    {
        return $this->hasMany(Rental::class)->orderByDesc('start_date')->orderByDesc('id');
    }

    public function maskedId(): string
    {
        $v = (string) $this->id_number;
        if ($v === '') return '—';

        return strlen($v) > 6 ? substr($v, 0, 4) . str_repeat('•', strlen($v) - 8) . substr($v, -4) : str_repeat('•', strlen($v));
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
