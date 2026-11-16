# Fase 5a — Penghuni & Riwayat (Aktif / Dipesan / Alumni, tambah manual, akhiri sewa)

## Pasang
1. Extract ke root proyek (Replace: Tenant.php, routes/owner.php, lang, RoomController, room-show, STATE.md).
2. php artisan route:clear && php artisan view:clear     (tidak ada migrasi baru)
3. php artisan test

## Uji manual (login pemilik)
1. Penghuni > Tambah penghuni: pilih kamar (tarif & deposit terisi otomatis), tanggal masuk boleh lampau, ubah tarif bila penghuni lama beda. Simpan -> kamar Terisi, tombol WA tautan aktivasi muncul.
2. Penghuni: tab Aktif / Dipesan / Alumni. Cari "Bayu", "101", nomor WA, atau nama teman sekamar.
3. Klik nama: data, riwayat sewa per kamar, status akun, tombol Chat WhatsApp.
4. Akhiri sewa (konfirmasi) -> pindah ke Alumni; masih ketemu lewat pencarian nama atau nomor kamar.
5. Kamar & Lantai > klik kamar: "Riwayat penghuni kamar" menampilkan semua yang pernah tinggal + periode.
6. Tambahkan lagi alumni yang sama (nomor WA sama) ke kamar lain -> tetap 1 data penghuni dengan 2 riwayat sewa.
Catatan: pembayaran belum tercatat; kolom Riwayat pembayaran terisi setelah Fase 5b. Penyelesaian deposit saat keluar ada di fitur deposit (nanti).
