<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Tagihan bulanan dibuat tiap malam; pengingat/kuitansi otomatis dicek tiap 10 menit (hanya jalan bila diaktifkan di Pengaturan).
Schedule::command('billing:generate')->dailyAt('00:30')->timezone('Asia/Jakarta');
Schedule::command('billing:remind')->everyTenMinutes()->timezone('Asia/Jakarta')->withoutOverlapping();
