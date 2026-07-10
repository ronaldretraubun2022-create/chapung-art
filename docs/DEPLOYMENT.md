# Deployment

Target: Rumahweb Paket M/Medium cPanel.  
Domain production: `https://chapungart.com`.

Dokumen checklist lengkap ada di:

```text
docs/DEPLOYMENT_CHECKLIST.md
```

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

## Important Rules

- Jangan upload `.env` local.
- Jangan set `APP_DEBUG=true` di production.
- Document root harus mengarah ke folder `public`.
- Upload publik hanya melalui `storage/app/public` dan symlink `public/storage`.
- Backup/private files tetap di `storage/app/private` dan tidak boleh public.
- Gunakan HTTPS penuh untuk `chapungart.com` dan `www.chapungart.com`.
- Batasi admin login dengan `ADMIN_EMAILS` dan role `Super Admin`.
