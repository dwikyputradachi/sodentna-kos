<?php

namespace App\Support;

use App\Models\AccountInvitation;
use App\Models\User;
use Illuminate\Support\Str;

/** One-time account activation links. Only the sha256 hash of the token is stored. */
class Invitations
{
    public const DAYS = 3; // TODO: make owner-configurable (Settings)

    public static function issue(User $user): string
    {
        AccountInvitation::where('user_id', $user->id)->whereNull('used_at')->delete();
        $token = Str::random(48);
        AccountInvitation::create(['user_id' => $user->id, 'token_hash' => hash('sha256', $token), 'expires_at' => now()->addDays(self::DAYS)]);

        return route('activation', $token);
    }

    public static function find(string $token): ?AccountInvitation
    {
        return AccountInvitation::where('token_hash', hash('sha256', $token))->whereNull('used_at')->where('expires_at', '>', now())->first();
    }
}
