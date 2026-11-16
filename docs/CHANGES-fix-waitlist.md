# Perbaikan tes Fase 2 + Daftar Tunggu

## Pasang
1. Extract ke root proyek (Replace: User.php, badge, layout situs, rooms publik, applications owner, routes/owner.php, routes/public.php, lang/id/validation.php, STATE.md).
2. php artisan route:clear && php artisan view:clear
3. php artisan migrate
4. php artisan test   (target: semua lulus, termasuk 5 tes baru WaitlistTest)

## Uji manual
- /daftar-tunggu (tanpa login): isi form -> muncul kode DT-xxxxx dan "Antrean ke-1". Kirim lagi dengan nomor sama -> "sudah ada di daftar tunggu".
- Pilih 2 penghuni -> muncul centang pasutri (wajib).
- Login pemilik -> Pengajuan Sewa -> "Daftar Tunggu" -> tombol "Tawarkan via WhatsApp", "Tandai ditawari", "Selesai", "Batalkan".

## Catatan nomor HP
Kolom users.phone harus berformat 62xxxxxxxxxx (mis. 6282285455631). Jika diisi 0822..., login gagal.
