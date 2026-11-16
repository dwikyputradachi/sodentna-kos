# Sodentna — Owner: Pengajuan Sewa, Persediaan, Pengaturan (UI, data demo)

Prasyarat: ZIP owner sudah terpasang. ZIP ini MENIMPA `routes/owner.php` dan `components/owner/badge.blade.php`.

## Pasang
1. Extract ke root proyek, pilih Replace saat diminta.
2. `php artisan route:clear && php artisan view:clear`
3. Buka /owner/pengajuan-sewa, /owner/persediaan-barang, /owner/pengaturan

## Uji manual
- Pengajuan: klik baris -> panel detail. SDN-1007 (Lina & Rudi, 2 orang di kamar standar) harus menampilkan peringatan dan tombol Setujui nonaktif.
  Setujui/Tolak memunculkan konfirmasi lalu status dan hitungan berubah.
- Persediaan: Keran air = Menipis, Cat tembok = Habis.
- Pengaturan: 5 tab; tab Keputusan owner memuat 6 poin terbuka.
