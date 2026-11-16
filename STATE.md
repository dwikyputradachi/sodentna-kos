# STATE — Sodentna (tempel file ini di awal chat baru)

Stack: Laravel 13, PHP 8.5, MySQL 8.4.3 (Laragon). Tailwind + Alpine via CDN (dev). Livewire belum dipakai.
Konvensi: UI Bahasa Indonesia; kode/komentar Inggris; format Rp; Asia/Jakarta; tabel operasional punya property_id (trait BelongsToProperty).
Login: nomor WhatsApp (dinormalisasi 62...) atau email + kata sandi. Peran: owner, admin, tenant.
Cara kerja: Claude.ai -> ZIP per sub-langkah -> extract di Laragon -> uji -> kirim hasil. Hanya kirim file yang berubah.

## Selesai
- Frontend demo: owner (12 halaman), portal penghuni, website publik (data demo di app/Support/*/Demo*.php).
- Fase 1 (LULUS): auth, peran, properties, property_users, PropertyScope, DevSeeder.
- Fase 2 (LULUS): floors, room_types (tarif 1 orang/2 orang, deposit, harian), rooms, room_status_logs; halaman Kamar & Lantai + detail kamar sudah dari DB. Dashboard, website publik, portal masih data demo.
- Fase 3 (LULUS; ExampleTest diperbaiki di Fase 3b): website publik dari DB; pengajuan sewa tersimpan (KTP privat, NIK terenkripsi); status via kode+4 digit; review owner; setujui => tenants+users(tanpa sandi)+rentals(reserved)+kamar Dipesan; tautan aktivasi sekali pakai 3 hari (token di-hash); check-in => Terisi.
- Fase 3c (menunggu uji): PENYEWA memilih kamar sendiri (grid kamar tersedia/tidak di form; dari halaman Ketersediaan ketuk kamar -> form terisi). Owner hanya menyetujui kamar pilihan; bila kamar sudah diambil orang lain owner memilih pengganti. Pemberitahuan: tombol WA untuk akun baru (tautan aktivasi) maupun akun lama (alamat login); halaman Status Pengajuan menampilkan Kamar & status akun (menunggu diaktifkan/sudah aktif). /masuk untuk yang sudah login diarahkan ke dashboard-nya; header situs publik tampil 'Dashboard'.
- Satu browser = satu sesi login (cookie dibagi antar tab). Profil Chrome/jendela samaran berbeda = sesi terpisah.
- Fase 5c (menunggu uji): BOT WA OTOMATIS. config/whatsapp.php (WA_BOT_URL, WA_BOT_KEY hanya di .env), BotSender (POST {pesan,nomor}, header x-api-key; 2xx = terkirim kecuali JSON success=false/status error), AutoMessenger (default MATI; per properti; satu pesan per tahap per tagihan; klik manual dihitung sudah ditangani; maks 3 percobaan, jeda 60 menit; jam 08-20 WIB; keterlambatan hanya 3 hari pertama; kuitansi hanya setelah bot dinyalakan), kuitansi dikirim langsung setelah 'Catat pembayaran' (gagal tidak membatalkan pembayaran), perintah `billing:remind` (dijadwalkan tiap 10 menit di routes/console.php; uji: `php artisan schedule:work`), halaman /owner/pengiriman-whatsapp (saklar, pilih tahap, pesan uji, riwayat), catatan 'Terkirim/Gagal kirim otomatis' di Tagihan, tabel property_settings. Format respons bot belum diketahui pasti -> cek & sesuaikan BotSender.
- Fase 5b: LULUS.
- Fase 5b (lama): TAGIHAN & PEMBAYARAN. invoices/payments/receipts/message_logs + rentals.next_due_date. Tagihan bulanan otomatis 7 hari sebelum jatuh tempo (GenerateInvoices, idempotent; dipanggil saat halaman Tagihan/portal dibuka + perintah `php artisan billing:generate`); tagihan pertama (sewa+deposit) dibuat saat pengajuan disetujui; jatuh tempo mengikuti tanggal masuk (tgl 29-31 -> hari terakhir bulan pendek). Status H-2/Jatuh Tempo/Terlambat/Lunas/Dibatalkan; tombol WA per tahap (POST -> catat message_logs 'opened' -> redirect wa.me; tahap harus sesuai). 'Catat pembayaran' (tunai/transfer) -> payment + kuitansi (nomor NNN/KSA/MM/YYYY, tautan rahasia /kuitansi/{token}, tanpa HP/KTP). Pembayaran pisah sewa vs deposit. Batalkan tagihan (bukan hapus). Portal penghuni: Tagihan, Riwayat+kuitansi dari DB. Persetujuan WA ada di form pengajuan.
- Fase 5a: LULUS (perbaikan placeholder).
- Fase 5a (lama): Penghuni dari DB — tab Aktif/Dipesan/Alumni, cari nama/kamar/WA/nama ke-2; detail penghuni (data, riwayat sewa per kamar, akun portal); 'Tambah penghuni' manual untuk penghuni lama (tanggal masuk boleh lampau, tarif bisa diubah/grandfathered, langsung Aktif + kamar Terisi); 'Akhiri sewa' -> Alumni, kamar Tersedia/Perbaikan, riwayat tetap; detail kamar menampilkan riwayat penghuni. Riwayat pembayaran menyusul di 5b.
- Fase 4: LULUS.
- Fase 4 (lama): portal penghuni dari DB (Beranda: kamar/tarif/tanggal/status sewa; Pembayaran awal = sewa+deposit; Profil: kontak darurat+email+ganti kata sandi, nama/identitas/WA hanya pemilik; Peraturan + tanggal persetujuan). CurrentProperty untuk role tenant diambil dari record tenant-nya. Tagihan & Riwayat masih kosong sampai Fase 5.
- Fase 3c: LULUS.
- Fase 3b: form pengajuan 3 kartu horizontal (1 Orang / 2 Orang / Harian) sesuai formulir cetak; 2 orang = Nama 1 & 2 (data lain bersama, foto identitas ke-2 opsional); Harian = form singkat (maks 1 orang, tgl masuk-keluar, total = tarif x malam); riwayat kode di perangkat (localStorage) + tombol Salin + owner bisa kirim ulang kode via WA.
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
- Akun: BUKAN password default; sistem membuat tautan aktivasi sekali pakai, pemilik tinggal tekan tombol WhatsApp. Username = nomor WA.
- Peraturan resmi = formulir cetak (18 poin, di DemoTenant::rules, nanti dipindah ke Pengaturan).
- Tombol Kirim WhatsApp + status H-2/jatuh tempo/terlambat harus tampil di daftar Penghuni aktif; ada tab Mantan Penghuni.
- Keuangan: istilah sederhana (Uang masuk / Uang keluar), input sekali, posting otomatis, koreksi bukan hapus.
- Fitur baru: Daftar Tunggu per tipe kamar (tawaran via WhatsApp manual).

## BELUM FINAL (jangan dibangun sebelum konfirmasi Bu Hesty)
- KEBIJAKAN DEPOSIT: formulir cetak (poin 13) menyatakan deposit DIKEMBALIKAN UTUH bila tidak ada kerusakan, dipotong sesuai biaya perbaikan bila ada. Ini bertentangan dengan kabar 'tidak dikembalikan' -> default refundable, minta konfirmasi Bu Hesty. Belum ada aturan bila kerusakan > deposit.
- PASUTRI: Pemilik bilang tidak boleh, tetapi formulir 2 orang poin 2 berbunyi 'pasangan suami istri wajib menunjukkan surat nikah asli' (artinya boleh). Konfirmasi.
  Dampak akuntansi: dikembalikan = titipan (bukan pendapatan); tidak dikembalikan = pendapatan. Rencana: sakelar `deposit_refundable` di Pengaturan.
- Pembayaran sewa harian & tarif harian; min/maks sewa harian; jatuh tempo tgl 29-31; biaya tamu menginap; kolom agama;
  masa berlaku tawaran daftar tunggu (belum ada pembatas otomatis); contoh Excel keuangan Bu Hesty (untuk kategori & format ekspor).

## Berikutnya
Fase 5d: unggah bukti bayar penghuni & verifikasi (setujui/tolak). [catatan lama 5c: bot WhatsApp otomatis (driver terpisah dari tombol: H-2, jatuh tempo, kuitansi saat status jadi lunas; jam kirim 08-20, jeda antar pesan, fallback tombol bila gagal, log status sent/failed; nomor KHUSUS bot; API key hanya di .env) + unggah bukti bayar penghuni & verifikasi. Fase 6 keuangan (buku kas dari payments, pengeluaran, laporan, dashboard nyata). [catatan lama 5b: tabel invoice+payment, tagihan bulanan otomatis mengikuti tanggal masuk (tgl 29-31 -> hari terakhir bulan pendek, bisa diatur), 'Catat pembayaran' manual (tunai/transfer), status H-2/jatuh tempo/terlambat + tombol WA di daftar penghuni, unggah bukti & verifikasi, kuitansi, riwayat bayar di detail penghuni & kamar. Catatan keuangan Bu Hesty: rapi, riwayat per kamar/nama, alumni tetap tercatat, mudah dikelola dari HP. (invoice, H-2, verifikasi, pembayaran awal = sewa+deposit). Belum ada: form 'Tambah penghuni' manual & impor dari Excel; halaman Penghuni owner masih demo.
