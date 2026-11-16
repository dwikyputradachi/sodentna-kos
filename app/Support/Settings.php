<?php

namespace App\Support;

use App\Models\PropertySetting;

/** Small per-property key/value store (owner-changeable switches). */
class Settings
{
    public static function get(int $propertyId, string $key, mixed $default = null): mixed
    {
        $row = PropertySetting::where('property_id', $propertyId)->where('key', $key)->first();

        return $row ? $row->value : $default;
    }

    public static function set(int $propertyId, string $key, mixed $value): void
    {
        PropertySetting::updateOrCreate(['property_id' => $propertyId, 'key' => $key], ['value' => is_bool($value) ? ($value ? '1' : '0') : (is_array($value) ? json_encode($value) : $value)]);
    }

    public static function bool(int $propertyId, string $key, bool $default = false): bool
    {
        $v = self::get($propertyId, $key);

        return $v === null ? $default : $v === '1';
    }

    public static function json(int $propertyId, string $key, array $default = []): array
    {
        $v = self::get($propertyId, $key);
        $d = $v ? json_decode($v, true) : null;

        return is_array($d) ? $d : $default;
    }
}
