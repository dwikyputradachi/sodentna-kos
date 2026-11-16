<?php

use App\Http\Controllers\Tenant\PageController as P;
use App\Http\Middleware\EnsureRole;
use Illuminate\Support\Facades\Route;

// Phase 4: every query must be scoped to the authenticated tenant.
Route::middleware(['web', 'auth', EnsureRole::class . ':tenant'])->prefix('penghuni')->name('tenant.')->group(function () {
    Route::get('/', [P::class, 'home'])->name('home');
    Route::get('tagihan', [P::class, 'bills'])->name('bills');
    Route::get('riwayat-pembayaran', [P::class, 'history'])->name('history');
    Route::get('profil', [P::class, 'profile'])->name('profile');
    Route::put('profil', [P::class, 'updateProfile'])->name('profile.update');
    Route::put('profil/kata-sandi', [P::class, 'updatePassword'])->name('profile.password');
    Route::get('peraturan-kos', [P::class, 'rules'])->name('rules');
});
