# Sodentna — Owner Pages (UI, data demo)

## Pasang
1. Extract ZIP ke root proyek Laravel (timpa jika diminta).
2. Tambahkan SATU baris di akhir `routes/web.php`:
   `require __DIR__.'/owner.php';`
3. Pastikan `.env`: `APP_TIMEZONE=Asia/Jakarta`, `APP_LOCALE=id`.
4. `php artisan route:clear && php artisan view:clear`
5. Buka `http://sodentna.test/owner` (sesuaikan host Laragon).

## Catatan
- Butuh internet (Tailwind CDN, Alpine CDN, Google Fonts). Ganti ke Vite sebelum produksi.
- Data = `app/Support/Owner/DemoData.php` (khusus dev). Belum ada database/auth.
- Logika nyata: status H-2/Jatuh Tempo/Terlambat/Menunggu Verifikasi dan link WhatsApp
  (`app/Support/Owner/Billing.php`).

## Uji manual
- Tagihan: baris H-2, Jatuh Tempo, Terlambat punya tombol WA berbeda; Menunggu Verifikasi tanpa tombol.
- Klik tombol WA: terbuka wa.me/62... dengan pesan terisi.
- Keuangan/Laporan: deposit tidak masuk pendapatan sewa.
- Penghuni: kolom pencarian memfilter baris. Mobile: tombol Menu membuka sidebar.
