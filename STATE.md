# STATE — Sodentna (tempel file ini di awal chat baru)

Stack: Laravel 13, PHP 8.5, MySQL 8.4.3 (Laragon). Tailwind + Alpine via CDN (dev). Livewire belum dipakai.
Konvensi: UI Bahasa Indonesia; kode/komentar Inggris; format Rp; Asia/Jakarta; tabel operasional punya property_id (trait BelongsToProperty).
Login: nomor WhatsApp (dinormalisasi 62...) atau email + kata sandi. Peran: owner, admin, tenant.
Cara kerja: Claude.ai -> ZIP per sub-langkah -> extract di Laragon -> uji -> kirim hasil. Hanya kirim file yang berubah.

## Selesai
- Frontend demo: owner (12 halaman), portal penghuni, website publik (data demo di app/Support/*/Demo*.php).
- Fase 1 (LULUS): auth, peran, properties, property_users, PropertyScope, DevSeeder.
- Fase 2 (LULUS): floors, room_types (tarif 1 orang/2 orang, deposit, harian), rooms, room_status_logs; halaman Kamar & Lantai + detail kamar sudah dari DB. Dashboard, website publik, portal masih data demo.
- Fase 3 (menunggu uji): website publik dari DB; pengajuan sewa tersimpan (KTP privat, NIK terenkripsi); status via kode+4 digit; review owner; setujui => tenants+users(tanpa sandi)+rentals(reserved)+kamar Dipesan; tautan aktivasi sekali pakai 3 hari (token di-hash); check-in => Terisi.
- Daftar Tunggu: sisi publik (/daftar-tunggu, tersimpan di DB, antre per tipe kamar, anti-duplikat) + halaman owner (/owner/daftar-tunggu, tombol WhatsApp tawaran manual).
- Catatan: nomor telepon di DB users disimpan format 62xxxxxxxxxx (bukan 08...).

## Keputusan terkonfirmasi
- Akun penghuni dibuat OTOMATIS saat pengajuan disetujui; pemilik tidak mengetik ulang data & tidak tahu kata sandi.
  Penghuni membuat kata sandi lewat tautan aktivasi sekali pakai (dikirim pemilik via WhatsApp, manual).
- Tarif sewa bulanan ditentukan JUMLAH PENGHUNI per kontrak: 1 orang Rp1.500.000; 2 orang Rp2.000.000 (default, bisa diubah pemilik).
  Tarif disimpan sebagai snapshot di kontrak (rentals.rate) supaya pesan WhatsApp & tagihan memakai harga penghuni itu.
- Deposit Rp500.000 flat per kontrak (bukan per orang). Pembayaran pertama = sewa bulan pertama + deposit
  (Rp2.000.000 untuk 1 orang, Rp2.500.000 untuk 2 orang). Jatuh tempo bulanan mengikuti tanggal masuk.
- Pasutri tidak boleh (berlaku untuk semua sewa 2 orang; ada pernyataan di form pengajuan).
- Riwayat masalah & penggantian barang dilihat PER KAMAR dan PER PENGHUNI (maintenance_records punya room_id + rental_id).
- Tombol Kirim WhatsApp + status H-2/jatuh tempo/terlambat harus tampil di daftar Penghuni aktif; ada tab Mantan Penghuni.
- Keuangan: istilah sederhana (Uang masuk / Uang keluar), input sekali, posting otomatis, koreksi bukan hapus.
- Fitur baru: Daftar Tunggu per tipe kamar (tawaran via WhatsApp manual).

## BELUM FINAL (jangan dibangun sebelum konfirmasi Bu Hesty)
- KEBIJAKAN DEPOSIT: apakah deposit dikembalikan di akhir sewa atau tidak (hangus)? Jawaban terakhir: kemungkinan TIDAK dikembalikan, belum pasti.
  Dampak akuntansi: dikembalikan = titipan (bukan pendapatan); tidak dikembalikan = pendapatan. Rencana: sakelar `deposit_refundable` di Pengaturan.
- Pembayaran sewa harian & tarif harian; min/maks sewa harian; jatuh tempo tgl 29-31; biaya tamu menginap; kolom agama;
  masa berlaku tawaran daftar tunggu (belum ada pembatas otomatis); contoh Excel keuangan Bu Hesty (untuk kategori & format ekspor).

## Berikutnya
Fase 4: portal penghuni dari DB (profil, sewa, tagihan). Lalu Fase 5 penagihan (invoice, H-2, verifikasi, pembayaran awal = sewa+deposit). Belum ada: form 'Tambah penghuni' manual & impor dari Excel; halaman Penghuni owner masih demo.
