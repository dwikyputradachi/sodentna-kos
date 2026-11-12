<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Invitations;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ActivationController extends Controller
{
    public function show(string $token)
    {
        $inv = Invitations::find($token);

        return view('site.activate', ['valid' => (bool) $inv, 'name' => $inv ? User::find($inv->user_id)?->name : null, 'token' => $token]);
    }

    public function store(Request $r, string $token)
    {
        $inv = Invitations::find($token);
        abort_unless($inv, 404);

        $d = $r->validate(['password' => ['required', 'string', 'min:8', 'confirmed']]);

        $user = User::findOrFail($inv->user_id);
        abort_unless($user->role === User::TENANT && $user->is_active, 403);

        DB::transaction(function () use ($user, $inv, $d) {
            $user->forceFill(['password' => $d['password']])->save();
            $inv->update(['used_at' => now()]);
        });

        Auth::login($user);
        $r->session()->regenerate();

        return redirect()->route('tenant.home');
    }
}
