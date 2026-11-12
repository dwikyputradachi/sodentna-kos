<?php

namespace App\Models;

use App\Models\Concerns\BelongsToProperty;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Tenant extends Model
{
    use BelongsToProperty;

    protected $fillable = ['property_id', 'user_id', 'name', 'phone', 'birth_place', 'birth_date', 'address', 'id_number', 'job', 'institution',
        'emergency_name', 'emergency_relation', 'emergency_phone', 'emergency_address', 'id_doc_path', 'status'];

    protected $hidden = ['id_number'];

    protected function casts(): array
    {
        return ['birth_date' => 'date', 'id_number' => 'encrypted'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
