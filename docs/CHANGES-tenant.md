# Sodentna — Portal Penghuni (UI, data demo)

Prasyarat: ZIP owner sudah terpasang (portal memakai komponen `x-owner.badge/card` dan class `App\Support\Owner\*`).

## Pasang
1. Extract ZIP ke root proyek.
2. Tambahkan di akhir `routes/web.php`:
   `require __DIR__.'/tenant.php';`
3. `php artisan route:clear && php artisan view:clear`
4. Buka `http://sodentna.test/penghuni`

## Uji manual (buka di mode HP: F12 → ikon perangkat)
- Beranda: kartu kamar, pembayaran berikutnya (status H-2), 3 pembayaran terakhir.
- Tagihan: tombol "Kirim bukti" aktif hanya setelah file dipilih; file >5 MB menampilkan pesan; setelah kirim status menjadi Menunggu Verifikasi.
- Riwayat, Profil (nama & identitas terkunci), Peraturan Kos.
- Tombol "Hubungi pengelola" membuka wa.me.
