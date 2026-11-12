<?php

use App\Http\Controllers\Owner\ExtraController as X;
use App\Http\Controllers\Owner\PageController as P;
use App\Http\Controllers\Owner\ApplicationController as A;
use App\Http\Controllers\Owner\RoomController as R;
use App\Http\Controllers\Owner\WaitlistController as W;
use App\Http\Middleware\EnsureRole;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', EnsureRole::class . ':owner,admin'])->prefix('owner')->name('owner.')->group(function () {
    Route::get('/', [P::class, 'dashboard'])->name('dashboard');
    Route::get('kamar-lantai', [R::class, 'index'])->name('rooms');
    Route::post('kamar-lantai/lantai', [R::class, 'storeFloor'])->name('floors.store');
    Route::post('kamar-lantai/tipe', [R::class, 'storeType'])->name('types.store');
    Route::put('kamar-lantai/tipe/{type}', [R::class, 'updateType'])->name('types.update');
    Route::post('kamar-lantai/kamar', [R::class, 'storeRoom'])->name('rooms.store');
    Route::get('kamar-lantai/kamar/{room}', [R::class, 'show'])->name('rooms.show');
    Route::put('kamar-lantai/kamar/{room}', [R::class, 'updateRoom'])->name('rooms.update');
    Route::post('kamar-lantai/kamar/{room}/status', [R::class, 'status'])->name('rooms.status');
    Route::get('penghuni', [P::class, 'tenants'])->name('tenants');
    Route::get('tagihan-pembayaran', [P::class, 'billing'])->name('billing');
    Route::get('keuangan', [P::class, 'finance'])->name('finance');
    Route::get('deposit', [P::class, 'deposits'])->name('deposits');
    Route::get('laporan', [P::class, 'reports'])->name('reports');
    Route::get('pemeliharaan', [P::class, 'maintenance'])->name('maintenance');
    Route::get('dokumen-kuitansi', [P::class, 'documents'])->name('documents');
    Route::get('pengajuan-sewa', [A::class, 'index'])->name('applications');
    Route::get('pengajuan-sewa/{application}', [A::class, 'show'])->name('applications.show');
    Route::post('pengajuan-sewa/{application}/setujui', [A::class, 'approve'])->name('applications.approve');
    Route::post('pengajuan-sewa/{application}/tolak', [A::class, 'reject'])->name('applications.reject');
    Route::post('pengajuan-sewa/{application}/tautan-aktivasi', [A::class, 'invitation'])->name('applications.invitation');
    Route::post('pengajuan-sewa/{application}/mulai-sewa', [A::class, 'checkIn'])->name('applications.checkin');
    Route::get('pengajuan-sewa/{application}/identitas', [A::class, 'idDocument'])->name('applications.id');
    Route::get('daftar-tunggu', [W::class, 'index'])->name('waitlist');
    Route::post('daftar-tunggu/{entry}/status', [W::class, 'status'])->name('waitlist.status');
    Route::get('persediaan-barang', [X::class, 'inventory'])->name('inventory');
    Route::get('pengaturan', [X::class, 'settings'])->name('settings');
});
