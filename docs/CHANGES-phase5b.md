# Fase 5b — Tagihan, Pembayaran, Kuitansi, Pengingat (tombol WhatsApp)

## Pasang
1. Extract ke root proyek (Replace: routes/owner.php, routes/public.php, lang, ApproveApplication, CreateTenantManually, TenantController, PageController tenant, view owner/tenant/site, Rental.php, STATE.md).
2. php artisan route:clear && php artisan view:clear
3. php artisan migrate        (tabel invoices, payments, receipts, message_logs + kolom rentals.next_due_date)
4. php artisan test
(Opsional, nanti untuk produksi/bot: jadwal harian `php artisan billing:generate`.)

## Uji manual
1. Penghuni lama (Tambah penghuni): kosongkan "Jatuh tempo berikutnya" -> otomatis bulan depan. Isi tanggal lampau untuk penghuni yang menunggak.
2. Tagihan & Pembayaran: tab "Perlu perhatian" (H-2/Jatuh tempo/Terlambat), "Belum jatuh tempo", "Lunas", "Semua". Cari nama/kamar/nomor tagihan.
3. Tombol WhatsApp (sesuai tahap): tab baru wa.me terbuka dengan pesan siap kirim; di bawah tombol muncul "Dibuka <tanggal jam>".
4. Catat pembayaran (tanggal, tunai/transfer) -> status Lunas, muncul "Kirim kuitansi via WhatsApp" dan "Lihat kuitansi".
5. Buka tautan kuitansi di jendela penyamaran: tanpa login, tanpa nomor HP/KTP; tombol Cetak/Simpan PDF.
6. Tagihan salah -> "Batalkan tagihan" (ada alasan, riwayat tetap).
7. Setujui pengajuan baru -> tagihan pertama otomatis (sewa + deposit).
8. Daftar Penghuni (tab Aktif): kolom Tagihan dengan status + tombol WhatsApp di samping nama. Detail penghuni: riwayat tagihan & pembayaran.
9. Portal penghuni: Tagihan, Riwayat Pembayaran (+kuitansi), kartu pembayaran berikutnya di Beranda.
Catatan: bot otomatis & unggah bukti bayar = Fase 5c. Buku kas/laporan/dashboard nyata = Fase 6.
