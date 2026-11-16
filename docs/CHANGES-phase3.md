# Fase 3 — Website publik dari DB, pengajuan sewa, review owner, akun otomatis

## Pasang
1. Extract ke root proyek (Replace: routes/owner.php, routes/public.php, lang/id/validation.php, 4 view situs publik, applications owner, STATE.md).
2. php artisan route:clear && php artisan view:clear
3. php artisan migrate
4. php artisan test   (target semua lulus; 9 tes baru ApplicationFlowTest)

## Uji manual
1. Tanpa login: / , /ketersediaan-kamar (status dari DB, tanpa nama penghuni), /kamar/{id}.
2. /pengajuan-sewa: isi form + unggah KTP (jpg/png/pdf < 5 MB) -> muncul kode SDN-xxxxx.
3. /status-pengajuan: kode + 4 digit terakhir WA -> "Menunggu ditinjau". Digit salah -> "tidak cocok".
4. Login pemilik -> Pengajuan Sewa -> Tinjau: NIK tersamar, "Buka berkas" menampilkan KTP (hanya pemilik login).
5. Setujui + pilih kamar -> muncul tautan aktivasi + tombol WhatsApp (nomor Anda bila diubah). Kamar menjadi Dipesan.
6. Buka tautan di jendela penyamaran -> buat kata sandi -> masuk portal penghuni. Tautan kedua kali -> tidak berlaku.
7. Mulai sewa (check-in) -> kamar Terisi.
Catatan: KTP disimpan di storage/app/private/identitas (tidak bisa diakses lewat URL publik).
Catatan: portal penghuni, Penghuni owner, dan Dashboard masih data demo (Fase 4-5).
