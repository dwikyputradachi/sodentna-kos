# Sodentna — Website Publik (UI, data demo)

Prasyarat: ZIP owner + portal penghuni sudah terpasang.

## Pasang
1. Extract ZIP ke root proyek.
2. Di `routes/web.php`: HAPUS rute bawaan `Route::get('/', function () { return view('welcome'); });`
   lalu tambahkan di akhir: `require __DIR__.'/public.php';`
3. `php artisan route:clear && php artisan view:clear`
4. Buka `http://sodentna.test/`

## Uji manual
- Beranda, Tentang Kos, Ketersediaan (denah hanya status, tanpa nama penghuni), Detail Kamar (Standar & Double).
- Pengajuan: pilih Double -> muncul peringatan pasutri + centang wajib; pilih Harian -> jumlah penghuni maks. 1;
  Double tidak bisa harian. Isi semua field -> kode pengajuan muncul.
- Status Pengajuan: coba SDN-1001 / 1002 / 1003 / kode lain.
- Cek tampilan HP (F12 -> ikon perangkat).

## Catatan
- Harga harian (Rp120.000) dan data kamar hanyalah contoh dev; nilai asli dikonfigurasi owner (Fase 2).
- Kolom agama sengaja tidak ada sampai owner mengonfirmasi perlu.
