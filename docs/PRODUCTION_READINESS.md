# Production Readiness - Chapung Art

## Scope

Audit ini untuk branch `feat/production-readiness` dan target deployment shared hosting/cPanel Rumahweb dengan PHP 8.3, MariaDB, HTTPS penuh, Laravel 13, Filament 5, Blade, dan Tailwind CSS.

## Environment

- `.env.example` adalah baseline production dan tidak boleh berisi secret nyata.
- `APP_ENV=production`, `APP_DEBUG=false`, dan `APP_URL=https://chapungart.com` wajib dipakai di server.
- `APP_KEY` wajib dibuat di server dengan `php artisan key:generate --force`.
- `DB_PASSWORD`, `MAIL_PASSWORD`, dan `BACKUP_ARCHIVE_PASSWORD` wajib diisi hanya di `.env` server.
- Session, cache, dan queue memakai database agar cocok untuk shared hosting.
- `LOG_CHANNEL=production` memakai daily log dengan sanitizer context.

## Security

- `SecurityHeaders` aktif pada web middleware.
- HSTS hanya dikirim untuk HTTPS production.
- CSP production tidak mengizinkan `localhost:5173` atau `127.0.0.1:5173`.
- Source Vite dev server `http://localhost:5173`, `http://127.0.0.1:5173`, `ws://localhost:5173`, dan `ws://127.0.0.1:5173` hanya ditambahkan saat environment `local`.
- CSRF tetap memakai middleware Laravel default.
- Session cookie production wajib `secure`, `http_only`, `same_site=lax`, dan terenkripsi.
- Trusted proxy memakai `TRUSTED_PROXIES` untuk kompatibilitas SSL/proxy cPanel/Cloudflare.
- Rate limit aktif untuk login, public form, search, dan certificate verification.
- Admin panel dibatasi oleh `canAccessPanel`, role, permission, dan `ADMIN_EMAILS`.
- Log sanitizer menyaring password, token, API key, secret, cookie, authorization, dan field pembayaran sensitif.

## Upload And Storage

- Public upload hanya melalui disk `public` ke `storage/app/public` dan symlink `public/storage`.
- Private file digital memakai disk `local` di `storage/app/private`.
- Image upload hanya menerima JPEG, PNG, dan WebP sesuai MIME, extension, ukuran, dan signature file.
- Digital download hanya menerima PDF, JPEG, PNG, dan WebP sesuai MIME, extension, ukuran, dan signature file.
- Nama file upload memakai UUID, bukan nama asli user.
- Executable signature seperti `MZ`, `<?php`, `<script`, dan shebang script ditolak.
- Kegagalan storage dicatat aman dan dikembalikan sebagai validation error.

## Database

- Jalankan hanya migration non-destruktif dengan `php artisan migrate --force`.
- Jangan menjalankan `migrate:fresh`, `db:wipe`, `schema:dump --prune`, atau command destruktif di production.
- Migration mencakup foreign key, cascade/null-on-delete yang eksplisit, unique constraint untuk slug/order/invoice/certificate, dan index performa untuk marketplace/search/report.
- Jalankan `php artisan migrate:status` sebelum dan sesudah deploy untuk verifikasi.

## Email

- SMTP memakai mailbox domain `chapungart.com` dari cPanel.
- Password SMTP hanya di `.env` server.
- Contact form menangani kegagalan SMTP dengan logging aman dan tidak menampilkan exception ke pengguna.
- Internal notification mail untuk admin/payment sudah memakai safe mail wrapper.

## Error Handling

- `APP_DEBUG=false` wajib di production.
- Error page 403, 404, 419, 429, 500, dan 503 tersedia dengan desain Chapung Art dan localization ID/EN.
- Error page tidak menampilkan stack trace, path vendor, token, atau detail exception.

## Performance

- Public listing memakai pagination dan eager loading pada controller/query marketplace.
- Asset production dibangun dengan `npm run build`.
- Cache production memakai `config:cache`, `route:cache`, `view:cache`, dan `optimize`.
- Hindari dependency berat baru kecuali ada kebutuhan teknis jelas.

## Deployment Commands

Jalankan dari root project di cPanel SSH:

```bash
composer install --no-dev --optimize-autoloader
npm install
npm run build
php artisan optimize:clear
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
php artisan queue:restart
```

## Folder Permissions

- `storage/` harus writable oleh user hosting.
- `bootstrap/cache/` harus writable oleh user hosting.
- `public/storage` harus berupa symlink ke `storage/app/public`.
- Jangan membuat `storage/app/private` atau `database/` menjadi public web root.

## Backup And Rollback

- Backup database via cPanel Backup/MySQL atau `mysqldump` sebelum deploy.
- Backup `storage/app/public`, `storage/app/private`, dan `.env` server ke lokasi privat.
- Simpan artifact build lama atau release folder lama jika memakai deploy berbasis release.
- Jika deploy gagal sebelum migration: rollback code ke release sebelumnya dan jalankan cache clear.
- Jika deploy gagal setelah migration: jangan hapus data; restore database dari backup hanya jika ada instruksi manual dan downtime disetujui.

## Manual Checks

- Homepage, gallery, artwork detail, artist, collection, cart, checkout, invoice, contact, login, admin dashboard.
- Upload media valid dan invalid.
- Bilingual ID/EN pada public dan admin.
- Role dan permission admin.
- Security headers pada HTTPS production.
- `/storage` hanya mengekspos file public upload.
