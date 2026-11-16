# Fase 3c — Penyewa memilih kamar, pemberitahuan akun, perbaikan /masuk

## Pasang
1. Extract ke root proyek (Replace semua yang diminta).
2. php artisan route:clear && php artisan view:clear     (tidak ada migrasi baru)
3. php artisan test

## Uji manual
- /ketersediaan-kamar: kamar "Tersedia" bisa diketuk -> /pengajuan-sewa terisi kamar itu.
- /pengajuan-sewa: kartu 1 Orang/2 Orang/Harian lalu "Pilih kamar". Kamar terisi/dipesan/perbaikan abu-abu; kamar yang tidak cocok paket juga nonaktif. Tombol Kirim nonaktif sebelum memilih kamar.
- Owner > Pengajuan > Tinjau: tampil "Kamar pilihan pemohon"; tombol Setujui langsung memakai kamar itu. Jika kamar sudah diambil: muncul peringatan + pilihan kamar pengganti.
- Setelah setujui: kotak WhatsApp muncul (akun baru = tautan aktivasi; akun lama = alamat login). Tombol "Kirim pemberitahuan via WhatsApp" tetap tersedia di bawah.
- /status-pengajuan (kode + 4 digit): Disetujui -> "Kamar 101 sudah dipesan", "menunggu diaktifkan" / "sudah aktif".
- Sudah login lalu buka /masuk: langsung ke dashboard, bukan kembali ke beranda. Header beranda menampilkan "Dashboard".
