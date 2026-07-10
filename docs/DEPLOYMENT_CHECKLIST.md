# Chapung Art Production Deployment Checklist

Target hosting: Rumahweb Paket M/Medium cPanel  
Domain: `chapungart.com`  
Goal: siapkan project untuk upload/deploy, bukan deploy langsung dari local.

## 1. Pre-Upload Checklist

- Pastikan `.env` local tidak ikut diupload atau commit.
- Upload source project ke folder non-public, contoh:

```text
/home/CPANEL_USER/chapung-art
```

- Arahkan document root domain/subdomain ke:

```text
/home/CPANEL_USER/chapung-art/public
```

- Jika cPanel tidak mengizinkan document root diubah, gunakan struktur aman: file Laravel tetap di luar `public_html`, dan hanya isi folder `public/` yang diarahkan ke `public_html` dengan path `index.php` disesuaikan.
- Jangan simpan backup, `.env`, `storage/app/private`, `vendor`, atau file Laravel root di folder publik.

## 2. Production `.env`

Copy template:

```bash
cp .env.example .env
```

Wajib isi ulang di server:

```env
APP_NAME="Chapung Art"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://chapungart.com
ADMIN_EMAILS=admin@chapungart.com

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=CPANEL_USER_chapungart
DB_USERNAME=CPANEL_USER_chapungart
DB_PASSWORD=STRONG_DATABASE_PASSWORD

SESSION_SECURE_COOKIE=true
SESSION_DOMAIN=.chapungart.com

MAIL_HOST=mail.chapungart.com
MAIL_USERNAME=noreply@chapungart.com
MAIL_PASSWORD=STRONG_EMAIL_PASSWORD

BACKUP_ARCHIVE_PASSWORD=LONG_RANDOM_BACKUP_PASSWORD
```

Generate app key di server:

```bash
php artisan key:generate --force
```

## 3. Composer Install

Jalankan di root project server:

```bash
composer install --no-dev --optimize-autoloader
```

Jika cPanel memakai path Composer/PHP khusus, gunakan path dari Terminal cPanel atau MultiPHP Manager.

## 4. Frontend Build

Jalankan:

```bash
npm install
npm run build
```

Pastikan folder/file berikut ada setelah build:

```text
public/build
public/build/manifest.json
```

Jika build dilakukan di local, upload juga folder `public/build` ke server.

## 5. Database Migration

Buat database dan user MySQL dari cPanel, lalu jalankan:

```bash
php artisan migrate --force
```

Opsional untuk data awal jika database kosong:

```bash
php artisan db:seed --force
```

## 6. Storage Link & Upload Path

Jalankan:

```bash
php artisan storage:link
```

Upload publik harus tetap melalui disk `public`:

```text
storage/app/public
public/storage -> storage/app/public
```

Backup dan file private harus tetap di:

```text
storage/app/private
```

Jangan membuat symlink dari `storage/app/private` ke public.

## 7. Cache & Optimization Commands

Jalankan setelah `.env`, dependency, migration, dan build selesai:

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

Jika mengubah `.env` setelah cache aktif:

```bash
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

## 8. File Permission Aman

Rekomendasi permission cPanel:

```bash
find . -type f -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;
chmod -R 775 storage bootstrap/cache
chmod 600 .env
```

Jika server membutuhkan group write untuk PHP handler tertentu, gunakan `775` hanya untuk:

```text
storage
bootstrap/cache
```

Jangan gunakan `777` kecuali diminta eksplisit oleh support hosting dan hanya sementara untuk debugging.

## 9. Admin Login Security

- Pastikan `ADMIN_EMAILS` hanya berisi email admin yang boleh akses Filament.
- Pastikan user Super Admin sudah dibuat dan memakai password kuat.
- Gunakan HTTPS penuh.
- Jangan aktifkan `APP_DEBUG=true` di production.
- Setelah deploy, cek:

```text
https://chapungart.com/admin
```

## 10. SSL & Domain

Di Rumahweb cPanel:

- Arahkan DNS `chapungart.com` dan `www.chapungart.com` ke hosting.
- Aktifkan AutoSSL/SSL dari menu SSL/TLS Status.
- Pastikan `APP_URL=https://chapungart.com`.
- Paksa HTTPS dari cPanel atau `.htaccess` jika dibutuhkan.
- Cek halaman publik:

```text
https://chapungart.com
https://www.chapungart.com
```

## 11. Backup & Maintenance

Backup manual:

```bash
php artisan backup:run --disable-notifications
```

Cleanup backup:

```bash
php artisan backup:clean --disable-notifications
```

Backup harus tersimpan di private storage:

```text
storage/app/private/{BACKUP_NAME}
```

Lihat detail di:

```text
docs/BACKUP.md
```

## 12. Final Production Commands

Command produksi sesuai urutan:

```bash
composer install --no-dev --optimize-autoloader
npm install
npm run build
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

## 13. Post-Deploy Verification

Cek halaman publik:

```text
/
/gallery
/photography
/artists
/news
/about
/contact
```

Cek admin:

```text
/admin
```

Cek upload:

- Upload image dari Filament.
- Pastikan image tampil di frontend via `/storage/...`.
- Pastikan file private tidak bisa diakses publik.

Cek SEO:

- Source HTML memiliki `<title>`.
- Source HTML memiliki `<meta name="description">`.
- Canonical URL memakai `https://chapungart.com`.

## 14. Rollback Basic

Simpan backup sebelum migration besar:

```bash
php artisan backup:run --disable-notifications
```

Jika deploy gagal:

- Restore source dari arsip sebelumnya.
- Restore database dari backup terakhir.
- Jalankan ulang cache:

```bash
php artisan optimize:clear
php artisan optimize
```
