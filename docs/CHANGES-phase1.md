# Fase 1 — Fondasi (MySQL, login, peran, properti)

## 1. Database (Terminal Laragon)
mysql -u root -e "CREATE DATABASE sodentna CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

## 2. .env (ubah blok DB)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sodentna
DB_USERNAME=root
DB_PASSWORD=
APP_TIMEZONE=Asia/Jakarta
APP_LOCALE=id

## 3. Pasang
- Extract ZIP ke root proyek, pilih Replace (menimpa routes/owner.php, routes/tenant.php, 3 file layout, app/Models/User.php).
- Di routes/web.php tambahkan: require __DIR__.'/auth.php';
- php artisan route:clear && php artisan view:clear
- php artisan migrate
- php artisan db:seed --class=DevSeeder
- php artisan test

## 4. Uji manual (akun dev, hanya lokal)
- /masuk: pemilik 081300000001 / sodentna-dev -> /owner
- penghuni 081234567001 / sodentna-dev -> /penghuni
- Penghuni membuka /owner => 403. Tombol "Keluar" berfungsi. Salah sandi 5x => diblokir sementara.
Catatan: halaman masih memakai data demo; data asli mulai Fase 2.
