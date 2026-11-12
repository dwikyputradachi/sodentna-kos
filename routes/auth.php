<?php

use App\Http\Controllers\Auth\LoginController as L;
use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function () {
    Route::get('masuk', [L::class, 'show'])->middleware('guest')->name('login');
    Route::post('masuk', [L::class, 'store'])->middleware('guest');
    Route::post('keluar', [L::class, 'destroy'])->middleware('auth')->name('logout');
});
