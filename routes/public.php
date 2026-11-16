<?php

use App\Http\Controllers\Site\ActivationController as Act;
use App\Http\Controllers\Site\ApplicationController as A;
use App\Http\Controllers\Site\PageController as P;
use App\Http\Controllers\Site\ReceiptController as Rc;
use App\Http\Controllers\Site\WaitlistController as W;
use Illuminate\Support\Facades\Route;

Route::middleware('web')->name('site.')->group(function () {
    Route::get('/', [P::class, 'home'])->name('home');
    Route::get('tentang-kos', [P::class, 'about'])->name('about');
    Route::get('ketersediaan-kamar', [P::class, 'rooms'])->name('rooms');
    Route::get('kamar/{id}', [P::class, 'room'])->whereNumber('id')->name('room');
    Route::get('pengajuan-sewa', [A::class, 'create'])->name('apply');
    Route::post('pengajuan-sewa', [A::class, 'store'])->middleware('throttle:5,10')->name('apply.store');
    Route::get('status-pengajuan', [A::class, 'status'])->name('status');
    Route::post('status-pengajuan', [A::class, 'check'])->middleware('throttle:10,10')->name('status.check');
    Route::get('daftar-tunggu', [W::class, 'show'])->name('waitlist');
    Route::post('daftar-tunggu', [W::class, 'store'])->middleware('throttle:6,10')->name('waitlist.store');
});

Route::middleware('web')->group(function () {
    Route::get('kuitansi/{token}', [Rc::class, 'show'])->middleware('throttle:60,1')->name('receipt.show');
    Route::get('aktivasi/{token}', [Act::class, 'show'])->name('activation');
    Route::post('aktivasi/{token}', [Act::class, 'store'])->middleware('throttle:10,10')->name('activation.store');
});
