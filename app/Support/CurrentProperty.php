<?php

namespace App\Support;

/** Resolves the property the signed-in staff user is working in. Never trusts browser input. */
class CurrentProperty
{
    public static function id(): ?int
    {
        $user = auth()->user();
        if (! $user) return null;

        $ids = $user->properties()->pluck('properties.id');
        $selected = session('property_id');

        return $ids->contains($selected) ? (int) $selected : ($ids->first() ? (int) $ids->first() : null);
    }
}
