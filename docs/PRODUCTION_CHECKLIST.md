# Production Checklist - Chapung Art

## Before Deploy

- [ ] Branch/release sudah benar dan tidak ada secret di repository.
- [ ] `.env` production dibuat manual di server dari `.env.example`.
- [ ] `APP_KEY` sudah dibuat dengan `php artisan key:generate --force`.
- [ ] `APP_ENV=production`.
- [ ] `APP_DEBUG=false`.
- [ ] `APP_URL=https://chapungart.com`.
- [ ] HTTPS/SSL aktif untuk `chapungart.com` dan `www.chapungart.com`.
- [ ] Document root hosting mengarah ke folder `public`.
- [ ] Database MariaDB production sudah dibuat di cPanel.
- [ ] Mailbox `admin`, `info`, `gallery`, `news`, `media`, `support`, `finance`, dan `contact` sudah dibuat.

## Backup

- [ ] Backup database production.
- [ ] Backup `storage/app/public`.
- [ ] Backup `storage/app/private`.
- [ ] Backup `.env` server ke lokasi privat/offsite.
- [ ] Verifikasi backup dapat diunduh dan tidak berada di `public/`.

## Server Permissions

- [ ] `storage/` writable oleh user hosting.
- [ ] `bootstrap/cache/` writable oleh user hosting.
- [ ] `storage/app/private` tidak dapat diakses publik.
- [ ] `public/storage` adalah symlink ke `storage/app/public`.

## Install And Build

```bash
composer install --no-dev --optimize-autoloader
npm install
npm run build
```

## Safe Migration

```bash
php artisan migrate:status
php artisan migrate --force
php artisan migrate:status
```

Jangan jalankan:

```bash
php artisan migrate:fresh
php artisan db:wipe
```

## Storage Link

```bash
php artisan storage:link
```

Jika symlink gagal di cPanel, buat symlink dari panel file manager/SSH:

```bash
ln -s ../storage/app/public public/storage
```

## Cache Commands

```bash
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
php artisan queue:restart
```

## Security Verification

- [ ] Response header memuat `X-Frame-Options: SAMEORIGIN`.
- [ ] Response header memuat `X-Content-Type-Options: nosniff`.
- [ ] Response header memuat `Referrer-Policy: strict-origin-when-cross-origin`.
- [ ] Response header memuat `Permissions-Policy`.
- [ ] HTTPS production memuat `Strict-Transport-Security`.
- [ ] CSP production tidak memuat `localhost:5173` atau `127.0.0.1:5173`.
- [ ] `/admin` redirect ke login untuk guest.
- [ ] `/register` tidak terbuka di production kecuali sengaja diaktifkan.
- [ ] `.env`, `.git`, `composer.json`, `package.json`, dan `storage/logs` tidak dapat diakses publik.

## Marketplace Verification

- [ ] Homepage render normal.
- [ ] Gallery filter/search render normal.
- [ ] Artwork detail render normal.
- [ ] Artist dan collection page render normal.
- [ ] Cart add/update/remove normal.
- [ ] Checkout membuat order dan tidak double-submit.
- [ ] Invoice dapat dibuka/download oleh pemilik order/admin.
- [ ] Contact form validasi department dan menangani mail failure.
- [ ] Login admin normal.
- [ ] Admin dashboard, role, permission, dan resource CRUD normal.
- [ ] Upload valid diterima dan upload executable ditolak.
- [ ] Locale ID/EN persist setelah refresh/navigasi.

## Error Pages

- [ ] 403 branded dan tanpa stack trace.
- [ ] 404 branded dan tanpa stack trace.
- [ ] 419 branded dan tanpa detail token/session.
- [ ] 429 branded dan tanpa stack trace.
- [ ] 500 branded dan tanpa stack trace.

## Rollback

- [ ] Aktifkan maintenance mode jika perlu: `php artisan down --secret="rollback-token"`.
- [ ] Kembalikan code ke release sebelumnya.
- [ ] Jalankan `composer install --no-dev --optimize-autoloader` jika dependency berubah.
- [ ] Jalankan `npm run build` jika asset berubah.
- [ ] Jalankan `php artisan optimize:clear` lalu cache ulang.
- [ ] Restore database dari backup hanya jika migration/data benar-benar perlu dikembalikan.
- [ ] Matikan maintenance mode: `php artisan up`.
