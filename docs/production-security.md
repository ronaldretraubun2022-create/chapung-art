# Chapung Art Production Security

## Backup ringan

Jalankan dari root project Laravel:

```powershell
$stamp = Get-Date -Format "yyyyMMdd-HHmmss"
New-Item -ItemType Directory -Force "backups\$stamp"
Copy-Item "database\database.sqlite" "backups\$stamp\database.sqlite"
Copy-Item "storage\app\public" "backups\$stamp\storage-public" -Recurse
Copy-Item ".env" "backups\$stamp\.env.private"
```

Simpan folder `backups` di lokasi privat, jangan expose ke `public/`, dan jangan commit `.env.private`.

## Nginx deny block

Tambahkan di server block production jika memakai Nginx:

```nginx
location ~ /\.(env|git) {
    deny all;
}

location ~* /(database/.*\.sqlite|storage/logs/|composer\.(json|lock)|package\.json|vite\.config\.js)$ {
    deny all;
}
```

Pastikan document root selalu mengarah ke folder `public`.

## Permission production

- `storage/` dan `bootstrap/cache/` harus writable oleh user web server.
- `.env` dan `database/database.sqlite` tidak boleh berada di `public/`.
- Di VPS Linux gunakan permission ketat: file `640`, folder `750`, lalu ownership ke user deploy dan group web server.
- Di Windows/Laragon, pastikan hanya akun Windows/server yang menjalankan Apache/PHP yang punya write access ke `.env` dan database SQLite.
- Isi `ADMIN_EMAILS=email-admin@domain.com` di `.env` untuk membatasi panel Filament hanya ke email admin.
