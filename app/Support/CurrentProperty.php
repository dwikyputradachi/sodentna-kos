<?php

namespace App\Support;

use App\Models\Tenant;
use App\Models\User;

/** Resolves the property the signed-in staff user is working in. Never trusts browser input. */
class CurrentProperty
{
    public static function id(): ?int
    {
        $user = auth()->user();
        if (! $user) return null;

        // Tenants are not attached to properties; theirs is the property of their own tenant record.
        if ($user->role === User::TENANT) {
            $pid = Tenant::withoutGlobalScopes()->where('user_id', $user->id)->orderByDesc('id')->value('property_id');

            return $pid ? (int) $pid : null;
        }

        $ids = $user->properties()->pluck('properties.id');
        $selected = session('property_id');

        return $ids->contains($selected) ? (int) $selected : ($ids->first() ? (int) $ids->first() : null);
    }
}
