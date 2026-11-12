<?php

namespace App\Models;

use App\Models\Concerns\BelongsToProperty;
use App\Support\Owner\Fmt;
use Illuminate\Database\Eloquent\Model;

class RoomType extends Model
{
    use BelongsToProperty;

    protected $fillable = ['property_id', 'name', 'size', 'capacity', 'rate_one', 'rate_two', 'deposit_amount', 'allows_daily', 'daily_rate'];

    protected function casts(): array
    {
        return ['allows_daily' => 'boolean'];
    }

    /** Monthly rate for the given number of occupants (never above capacity). */
    public function monthlyRate(int $occupants): int
    {
        return $occupants >= 2 && $this->capacity >= 2 && $this->rate_two !== null ? (int) $this->rate_two : (int) $this->rate_one;
    }

    public function priceLabel(): string
    {
        $s = '1 orang ' . Fmt::rp($this->rate_one);
        return $this->capacity >= 2 && $this->rate_two !== null ? $s . ' · 2 orang ' . Fmt::rp($this->rate_two) : $s;
    }
}
