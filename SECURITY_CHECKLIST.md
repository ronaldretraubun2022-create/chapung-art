# SECURITY CHECKLIST - CHAPUNG ART

## Security yang sudah diterapkan

- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL=https://chapungart.com`
- `SESSION_SECURE_COOKIE=true`
- `SESSION_HTTP_ONLY=true`
- `SESSION_SAME_SITE=lax`
- `LOG_LEVEL=warning`
- Security headers aktif pada web middleware:
  - `X-Frame-Options: SAMEORIGIN`
  - `X-Content-Type-Options: nosniff`
  - `Referrer-Policy: strict-origin-when-cross-origin`
  - `Permissions-Policy: camera=(), microphone=(), geolocation=()`
  - `Strict-Transport-Security: max-age=31536000; includeSubDomains; preload`
- `/admin` dilindungi auth middleware Filament.
- `/admin/login` memakai throttling custom.
- `/register` tidak aktif di production kecuali `ENABLE_REGISTRATION=true`.
- Password user memakai hash Laravel standar lewat cast `password => hashed`.
- Upload gambar Filament dibatasi ke `jpg/jpeg/png/webp`, maksimal 4MB, dan nama file UUID.
- Apache `public/.htaccess` memblokir dotfiles, `.git`, SQLite, logs, composer/package/vite config, dan directory listing.
- Database SQLite berada di `database/database.sqlite`, bukan di `public/`.
- Private local storage route dimatikan lewat `config/filesystems.php` (`local.serve=false`).

## Command cache production

```powershell
& 'C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe' artisan optimize:clear
& 'C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe' artisan config:cache
& 'C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe' artisan route:cache
& 'C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe' artisan view:cache
```

Jika PHP sudah ada di PATH:

```bash
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Checklist testing

- Buka `/` dan pastikan homepage normal.
- Buka `/gallery`, `/photography`, `/media`, `/sitemap.xml`, dan `/robots.txt`.
- Buka `/register` dan pastikan 404.
- Buka `/admin` dan pastikan redirect ke `/admin/login`.
- Login admin dan pastikan dashboard Filament tetap normal.
- Upload gambar `.jpg`, `.png`, `.webp` ukuran valid.
- Tolak upload `.svg`, `.php`, `.html`, `.js`, dan file lebih dari 4MB.
- Cek response header production memuat security headers.
- Coba akses `/.env`, `/.git/`, `/database/database.sqlite`, `/storage/logs/laravel.log`, `/composer.json`, `/composer.lock`, `/package.json`, dan `/vite.config.js`; semua harus gagal/404/403.
- Pastikan `php artisan route:list --path=storage` tidak menampilkan route private `storage/{path}` bawaan Laravel.

## Catatan server dan Cloudflare

- Document root wajib mengarah ke folder `public/`.
- HTTPS wajib aktif sebelum HSTS preload dipakai.
- Di Cloudflare aktifkan:
  - Always Use HTTPS
  - Automatic HTTPS Rewrites
  - WAF managed rules
  - rate limit untuk `/admin/*`
  - bot protection sesuai kebutuhan
- Jika memakai Nginx, tambahkan deny block untuk dotfiles, SQLite, logs, dan file config project.

## Backup SQLite dan storage

Jalankan dari root project:

```powershell
$stamp = Get-Date -Format "yyyyMMdd-HHmmss"
New-Item -ItemType Directory -Force "backups\$stamp"
Copy-Item "database\database.sqlite" "backups\$stamp\database.sqlite"
Copy-Item "storage\app\public" "backups\$stamp\storage-public" -Recurse
Copy-Item ".env" "backups\$stamp\.env.private"
```

Simpan backup di lokasi privat/offsite. Jangan letakkan folder backup di `public/`, dan jangan commit `.env.private`.
