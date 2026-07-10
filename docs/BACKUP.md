# Backup & Maintenance

## Storage Location

Backup Chapung Art CMS disimpan di disk Laravel `local`:

```text
storage/app/private/{BACKUP_NAME atau APP_NAME}/
```

Folder ini tidak berada di `public/` dan tidak ikut `storage:link`, sehingga file backup tidak bisa diakses langsung dari browser.

## Manual Backup

Laragon local:

```powershell
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe artisan backup:run --disable-notifications
```

cPanel hosting:

```bash
/usr/local/bin/php artisan backup:run --disable-notifications
```

Jika path PHP berbeda di hosting, gunakan path PHP yang disediakan cPanel.

## Database Only Backup

```bash
php artisan backup:run --only-db --disable-notifications
```

## Cleanup Old Backups

```bash
php artisan backup:clean --disable-notifications
```

## Suggested Cron

Jalankan sekali sehari di cPanel Cron Jobs:

```bash
cd /home/USERNAME/path-to-project && /usr/local/bin/php artisan backup:run --disable-notifications >/dev/null 2>&1
```

Tambahkan cleanup mingguan:

```bash
cd /home/USERNAME/path-to-project && /usr/local/bin/php artisan backup:clean --disable-notifications >/dev/null 2>&1
```

## Admin Page

Status backup dapat dilihat di Filament:

```text
/admin/backup-status
```

Halaman ini hanya menampilkan metadata backup dan tidak menyediakan link download untuk menjaga keamanan file.

## Notes for cPanel

- Pastikan fungsi `proc_open` dan `ZipArchive` tersedia.
- Pastikan binary `mysqldump` tersedia untuk backup database MySQL.
- Jangan memindahkan backup ke `public/` atau `storage/app/public`.
- Simpan password arsip di `.env` jika ingin enkripsi:

```env
BACKUP_ARCHIVE_PASSWORD=change-this-long-random-password
BACKUP_NAME=chapung-art
DB_DUMP_BINARY_PATH=/usr/bin
```

Untuk Laragon Windows, contoh path dump binary:

```env
DB_DUMP_BINARY_PATH=C:\laragon\bin\mysql\mysql-8.4.3-winx64\bin
DB_SQLITE_DUMP_BINARY_PATH=C:\laragon\bin\laragon\utils
```

Jika project memakai SQLite di cPanel, isi `DB_SQLITE_DUMP_BINARY_PATH` dengan folder yang berisi binary `sqlite3`.
