# Production Environment

Target production Chapung Art:

- Hosting: cPanel/Rumahweb Paket M atau setara.
- PHP: 8.3.x.
- Database: MariaDB via cPanel MySQL Databases.
- Domain: `chapungart.com` dan `www.chapungart.com`.
- SSL: HTTPS penuh dengan AutoSSL/SSL cPanel.
- Public document root: folder Laravel `public`.

## 1. cPanel Setup

Upload source Laravel ke folder non-public, contoh:

```text
/home/CPANEL_USER/chapung-art
```

Arahkan document root domain ke:

```text
/home/CPANEL_USER/chapung-art/public
```

Jika hosting tidak mengizinkan document root ke folder tersebut, gunakan struktur aman: file Laravel tetap di luar `public_html`, lalu hanya isi folder `public/` yang diletakkan pada document root dan path `index.php` disesuaikan.

Jangan simpan file berikut di folder publik:

- `.env`
- `vendor/`
- `storage/app/private/`
- `storage/logs/`
- `database/*.sqlite`
- backup database atau arsip backup

## 2. PHP 8.3

Di cPanel, pilih PHP 8.3 dari MultiPHP Manager atau Select PHP Version. Extension yang perlu aktif:

- `bcmath`
- `ctype`
- `curl`
- `dom`
- `fileinfo`
- `filter`
- `gd`
- `intl`
- `json`
- `mbstring`
- `openssl`
- `pdo`
- `pdo_mysql`
- `tokenizer`
- `xml`
- `zip`

Pastikan Composer dijalankan memakai PHP 8.3 yang sama.

## 3. MariaDB

Buat database dan user dari cPanel MySQL Databases. Gunakan format nama cPanel, misalnya:

```env
DB_CONNECTION=mariadb
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=cpaneluser_chapungart
DB_USERNAME=cpaneluser_chapungart
DB_PASSWORD=CHANGE_ME_STRONG_DATABASE_PASSWORD
DB_CHARSET=utf8mb4
DB_COLLATION=utf8mb4_unicode_ci
```

Jika driver `mariadb` tidak tersedia pada hosting tertentu, gunakan fallback Laravel compatible:

```env
DB_CONNECTION=mysql
```

## 4. SSL and HTTPS

Aktifkan AutoSSL/SSL untuk `chapungart.com` dan `www.chapungart.com`, lalu set:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://chapungart.com
TRUSTED_PROXIES=*
SESSION_SECURE_COOKIE=true
SECURITY_HSTS_ENABLED=true
SECURITY_HSTS_MAX_AGE=31536000
```

Aplikasi akan memaksa URL scheme HTTPS saat `APP_ENV=production` dan `APP_URL` memakai `https://`.

## 5. Filesystem

Gunakan disk private sebagai default dan disk public hanya untuk upload publik:

```env
FILESYSTEM_DISK=local
IMAGE_UPLOAD_DISK=public
IMAGE_UPLOAD_FALLBACK_PUBLIC_PATH=images/og-image.jpg
```

Jalankan storage link setelah deployment:

```bash
php artisan storage:link
```

Struktur yang diharapkan:

```text
storage/app/private  -> private, tidak public
storage/app/public   -> upload publik
public/storage       -> symlink ke storage/app/public
```

## 6. Session

Production memakai database session agar stabil pada cPanel shared hosting:

```env
SESSION_DRIVER=database
SESSION_CONNECTION=mariadb
SESSION_TABLE=sessions
SESSION_ENCRYPT=true
SESSION_COOKIE=chapung_art_session
SESSION_DOMAIN=.chapungart.com
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax
```

Migration `sessions` sudah tersedia di project dan dibuat saat `php artisan migrate --force`.

## 7. Cache and Queue

Gunakan database cache dan queue untuk kompatibilitas cPanel:

```env
CACHE_STORE=database
CACHE_PREFIX=chapungart_cache
DB_CACHE_CONNECTION=mariadb
DB_CACHE_TABLE=cache
DB_CACHE_LOCK_CONNECTION=mariadb
DB_CACHE_LOCK_TABLE=cache_locks
QUEUE_CONNECTION=database
QUEUE_FAILED_DRIVER=database-uuids
```

Migration `cache`, `cache_locks`, `jobs`, dan `failed_jobs` sudah tersedia.

Untuk queue worker, tambahkan cron atau cPanel process sesuai dukungan hosting. Jika queue worker tidak tersedia, gunakan command berkala:

```bash
php artisan queue:work --stop-when-empty --tries=3 --timeout=60
```

## 8. Production Commands

## 8. Mail Configuration

Buat email account di cPanel untuk mailbox operasional berikut:

```env
ADMIN_EMAILS=admin@chapungart.com
INFO_EMAIL=info@chapungart.com
GALLERY_EMAIL=gallery@chapungart.com
NEWS_EMAIL=news@chapungart.com
MEDIA_EMAIL=media@chapungart.com
SUPPORT_EMAIL=support@chapungart.com
FINANCE_EMAIL=finance@chapungart.com
CONTACT_EMAIL=contact@chapungart.com
```

Gunakan SMTP cPanel untuk pengiriman aplikasi:

```env
MAIL_MAILER=smtp
MAIL_HOST=mail.chapungart.com
MAIL_PORT=465
MAIL_USERNAME=admin@chapungart.com
MAIL_PASSWORD=
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS=admin@chapungart.com
MAIL_FROM_NAME="${APP_NAME}"
```

Form contact publik akan mengirim ke mailbox sesuai pilihan user. Jika mailbox departemen tidak valid, aplikasi fallback ke `contact`, lalu `info`, lalu `MAIL_FROM_ADDRESS`.
Password SMTP hanya boleh diisi pada file `.env` production di cPanel dan tidak boleh di-commit.

## 9. Production Commands

Jalankan dari root project di server:

```bash
composer install --no-dev --optimize-autoloader
npm install
npm run build
php artisan migrate --force
php artisan storage:link
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

## 10. Permission

Rekomendasi permission cPanel:

```bash
find . -type f -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;
chmod -R 775 storage bootstrap/cache
chmod 600 .env
```

Gunakan `777` hanya sementara jika diminta support hosting dan kembalikan segera.

## 11. Verification

Cek setelah deployment:

- `https://chapungart.com`
- `https://chapungart.com/gallery`
- `https://chapungart.com/photography`
- `https://chapungart.com/artists`
- `https://chapungart.com/news`
- `https://chapungart.com/sitemap.xml`
- `https://chapungart.com/robots.txt`
- `https://chapungart.com/admin`

Pastikan:

- Admin redirect ke login jika guest.
- Upload image tampil via `/storage/...`.
- `.env` tidak bisa diakses publik.
- `APP_DEBUG=false`.
- Header security dan HTTPS aktif.
- Cache config/route/view berhasil dibuat.
