<?php

namespace App\Models;

use App\Models\Concerns\BelongsToProperty;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Floor extends Model
{
    use BelongsToProperty;

    protected $fillable = ['property_id', 'name', 'sort_order'];

    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class)->orderBy('code');
    }
}
