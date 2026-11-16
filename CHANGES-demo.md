# Data demo untuk pengujian (hanya lokal)

## Isi ulang database dengan data demo
cd C:\laragon\www\sodentna
Expand-Archive -Path "$HOME\Downloads\sodentna-demo-data.zip" -DestinationPath . -Force
composer dump-autoload
php artisan migrate:fresh --seed --seeder=DemoSeeder

(migrate:fresh MENGHAPUS semua tabel. Aman untuk demo; jangan dipakai jika sudah ada data asli.)

## Login
Pemilik : 081300000001 / sodentna-dev
Penghuni: 081234567001 / sodentna-dev  (Alma Rikhafinata, kamar 101)

## Isi data (tanggal relatif terhadap HARI INI)
Aktif : Alma (H-2) | Rizki (jatuh tempo hari ini) | Rina (terlambat 3 hari) | Dewi & Rani (lunas) | Fajar (belum ada tagihan) | Putri (belum jatuh tempo) | Bayu & Dimas
Dipesan: Sari Ramadhani, kamar 106 (tagihan pertama sewa+deposit)
Alumni : Ahmad Fauzi, kamar 103 (riwayat pembayaran 6 bulan + kuitansi)
Pengajuan menunggu: Budi Santoso (202), Tono Wijaya (205, harian) | Daftar tunggu: 2 orang | Kamar 104: dalam perbaikan

## Uji bot dengan nomor sendiri
Tambahkan di .env:  DEMO_PHONE=0812xxxxxxxx   lalu jalankan ulang seeder.
Penghuni "Rizki Ramadhan" (jatuh tempo hari ini) akan memakai nomor itu. Tagihan > "Kirim Tagihan" atau `php artisan billing:remind --ignore-window`.

Catatan: berkas KTP demo tidak ada (tombol "Buka berkas" kosong); nomor HP penghuni lain palsu.
