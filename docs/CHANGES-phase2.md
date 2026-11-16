# Fase 2 — Lantai, Tipe Kamar, Kamar (data dari database)

## Pasang
1. Extract ZIP ke root proyek (Replace: routes/owner.php, DevSeeder, settings & rooms view, STATE.md).
2. php artisan route:clear && php artisan view:clear
3. php artisan migrate
4. php artisan db:seed --class=DevSeeder   (membuat 2 lantai, 12 kamar, tipe "Standar": 1 orang Rp1,5 jt / 2 orang Rp2 jt)
5. php artisan test

## Uji manual (login 081300000001 / sodentna-dev)
- /owner/kamar-lantai: 12 kamar dari database. Coba tambah lantai, tipe (kapasitas 2 wajib tarif 2 orang), dan kamar.
- Nomor kamar kembar -> pesan "Nomor kamar sudah digunakan."
- Klik kamar -> ubah status (ada konfirmasi) -> muncul di "Riwayat status". Klik dua kali tidak menggandakan riwayat.
- Ubah tarif tipe lewat tombol "Ubah" -> label tarif berubah.
- Pengaturan > Aturan sewa kini mengarah ke Kamar & Lantai.
Catatan: Dashboard, website publik, dan portal penghuni masih memakai data demo sampai Fase 3-4.
