<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Support\Owner\Billing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function show()
    {
        return view('auth.login');
    }

    public function store(Request $r)
    {
        $r->validate(
            ['login' => ['required', 'string'], 'password' => ['required', 'string']],
            ['login.required' => 'Nomor WhatsApp atau email wajib diisi.', 'password.required' => 'Kata sandi wajib diisi.']
        );

        $login = trim($r->input('login'));
        $key = Str::lower($login) . '|' . $r->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages(['login' => 'Terlalu banyak percobaan. Coba lagi dalam ' . RateLimiter::availableIn($key) . ' detik.']);
        }

        $field = str_contains($login, '@') ? 'email' : 'phone';
        $value = $field === 'phone' ? Billing::phone($login) : Str::lower($login);

        if (! Auth::attempt([$field => $value, 'password' => $r->input('password'), 'is_active' => true], $r->boolean('remember'))) {
            RateLimiter::hit($key, 60);
            throw ValidationException::withMessages(['login' => 'Nomor WhatsApp/email atau kata sandi salah.']);
        }

        RateLimiter::clear($key);
        $r->session()->regenerate();

        return redirect()->intended(Auth::user()->isStaff() ? route('owner.dashboard') : route('tenant.home'));
    }

    public function destroy(Request $r)
    {
        Auth::logout();
        $r->session()->invalidate();
        $r->session()->regenerateToken();

        return redirect()->route('login');
    }
}
