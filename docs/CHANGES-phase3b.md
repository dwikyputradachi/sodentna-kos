# Fase 3b — Form pengajuan 3 kartu + perbaikan ExampleTest

## Pasang
1. Extract ke root proyek (Replace: ExampleTest, ApplicationFlowTest, controller & view pengajuan/status, view owner, routes/owner.php, lang, Tenant.php, ApproveApplication.php, DemoTenant.php, STATE.md).
2. php artisan route:clear && php artisan view:clear
3. php artisan migrate        (menambah kolom name_2, id_doc_2_path, religion, co_occupant_name)
4. php artisan test           (target: semua lulus)

## Uji manual (coba di HP / mode perangkat F12)
- /pengajuan-sewa: kartu 1 Orang / 2 Orang / Harian bisa digeser; kartu terpilih berwarna teal; total bawah ikut berubah.
- 2 Orang: muncul Nama lengkap 1 & 2, foto identitas ke-2 (opsional), centang "bukan pasangan suami istri".
- Harian: form singkat (tanpa tempat/tgl lahir, pekerjaan, instansi); isi tanggal masuk & keluar -> muncul "N malam x tarif = total".
  Kartu Harian nonaktif bila belum ada tipe kamar dengan sewa harian + tarif harian (atur di Kamar & Lantai).
- Setelah kirim: kode besar + tombol Salin + "Simpan ke WhatsApp saya". Buka /status-pengajuan: ada daftar "Pengajuan di perangkat ini".
- Owner: kode ada tombol Salin di daftar & detail; tombol "Kirim kode ke pemohon via WhatsApp".
