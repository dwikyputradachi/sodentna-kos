<?php

namespace App\Support;

use App\Models\Property;

/** v1 serves one property on the public site. Multi-property: resolve by domain/slug here. */
class SiteProperty
{
    public static function find(): ?Property
    {
        return Property::where('is_active', true)->orderBy('id')->first();
    }

    public static function get(): Property
    {
        return Property::where('is_active', true)->orderBy('id')->firstOrFail();
    }
}
