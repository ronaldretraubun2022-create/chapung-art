# Deployment

Target: Rumahweb Paket M/Medium cPanel.  
Domain production: `https://chapungart.com`.

Dokumen checklist lengkap ada di:

```text
docs/DEPLOYMENT_CHECKLIST.md
```

Dokumen environment production lengkap ada di:

```text
docs/PRODUCTION_ENVIRONMENT.md
```

Dokumen audit MariaDB production ada di:

```text
docs/MARIADB_PRODUCTION_AUDIT.md
```

## Production Environment Target

- cPanel/Rumahweb hosting.
- PHP 8.3.x dengan extension Laravel aktif.
- MariaDB via cPanel MySQL Databases.
- HTTPS penuh untuk `chapungart.com` dan `www.chapungart.com`.
- Document root wajib mengarah ke folder `public`.
- Session, cache, dan queue memakai database agar kompatibel dengan shared hosting.

## Production Command Order

Jalankan dari root project di server:

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

## Production `.env` Baseline

Gunakan `.env.example` sebagai baseline production, lalu isi secret di server:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://chapungart.com
DB_CONNECTION=mariadb
SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
FILESYSTEM_DISK=local
IMAGE_UPLOAD_DISK=public
SESSION_SECURE_COOKIE=true
SECURITY_HSTS_ENABLED=true
MAIL_MAILER=smtp
MAIL_HOST=mail.chapungart.com
MAIL_FROM_ADDRESS=noreply@chapungart.com
```

Mailbox production yang harus dibuat di cPanel: `admin`, `info`, `gallery`, `news`, `media`, `support`, `finance`, dan `contact` pada domain `chapungart.com`.

## Important Rules

- Jangan upload `.env` local.
- Jangan set `APP_DEBUG=true` di production.
- Document root harus mengarah ke folder `public`.
- Upload publik hanya melalui `storage/app/public` dan symlink `public/storage`.
- Backup/private files tetap di `storage/app/private` dan tidak boleh public.
- Gunakan HTTPS penuh untuk `chapungart.com` dan `www.chapungart.com`.
- Batasi admin login dengan `ADMIN_EMAILS` dan role `Super Admin`.
