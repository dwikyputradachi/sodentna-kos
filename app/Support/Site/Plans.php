<?php

namespace App\Support\Site;

use App\Models\Property;
use App\Models\RoomType;

/**
 * The three rental plans shown as cards on the application form:
 * 1 person (monthly), 2 persons (monthly), daily (1 person).
 * Prices come from room types; the cheapest eligible type is the reference.
 */
class Plans
{
    public static function for(?Property $p): array
    {
        $types = $p ? RoomType::withoutGlobalScopes()->where('property_id', $p->id)->get() : collect();

        $one = $types->sortBy('rate_one')->first();
        $two = $types->filter(fn ($t) => $t->capacity >= 2 && $t->rate_two)->sortBy('rate_two')->first();
        $day = $types->filter(fn ($t) => $t->allows_daily && $t->daily_rate)->sortBy('daily_rate')->first();

        $plan = fn (string $key, string $label, string $mode, int $occ, ?RoomType $t, ?int $price) => [
            'key' => $key, 'label' => $label, 'mode' => $mode, 'occupants' => $occ, 'available' => (bool) $t, 'type_id' => $t?->id,
            'price' => $price, 'deposit' => $mode === 'monthly' ? $t?->deposit_amount : null,
            'total' => $mode === 'monthly' && $t ? $price + $t->deposit_amount : null,
        ];

        return [
            'one' => $plan('one', '1 Orang', 'monthly', 1, $one, $one?->rate_one),
            'two' => $plan('two', '2 Orang', 'monthly', 2, $two, $two?->rate_two),
            'daily' => $plan('daily', 'Harian', 'daily', 1, $day, $day?->daily_rate),
        ];
    }
}
