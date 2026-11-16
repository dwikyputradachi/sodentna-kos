# Fase 4 — Portal penghuni dari database

## Pasang
1. Extract ke root proyek (Replace: CurrentProperty.php, Rental.php, routes/tenant.php, lang, PageController tenant, view tenant, STATE.md).
2. php artisan route:clear && php artisan view:clear      (tidak ada migrasi baru)
3. php artisan test

## Uji manual
1. Setujui satu pengajuan di owner, aktifkan akun lewat tautan, lalu masuk sebagai penghuni (jendela penyamaran / profil Chrome lain).
2. /penghuni: kamar, tipe, lantai, tarif, tanggal mulai, jatuh tempo tiap tanggal N, status "Dipesan".
3. Kartu "Pembayaran awal": sewa bulan pertama + deposit = total (mis. Rp2.000.000 untuk 1 orang).
4. /penghuni/profil: nama/identitas/WA terkunci; ubah kontak darurat + email, simpan. Ganti kata sandi (wajib isi sandi lama).
5. Setelah owner menekan "Mulai sewa", status di Beranda berubah menjadi "Aktif".
6. Akun uji lama tanpa data sewa (mis. akun dev) menampilkan "Data penghuni belum tersedia" — normal.
Catatan: Tagihan dan Riwayat Pembayaran baru terisi setelah Fase 5 (penagihan).
