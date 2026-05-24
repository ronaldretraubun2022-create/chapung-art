# Production Checklist - Chapung Art

Dokumen ini mencatat status final production project Laravel Chapung Art setelah audit ringan dan hardening akhir.

## Status Project

| Area | Status | Keterangan |
| --- | --- | --- |
| Aplikasi publik | Selesai | Halaman utama dan halaman frontend berjalan normal. |
| Admin CMS | Aman | `/admin` terlindungi login dan redirect ke `/admin/login`. |
| Storage public | Aktif | Upload gambar tampil dari `/storage`. |
| Sitemap | Aktif | `/sitemap.xml` dapat diakses. |
| Robots | Aktif | `/robots.txt` dapat diakses. |
| Cache production | Aktif | Optimize/cache production sudah berjalan. |
| Public registration | Tertutup | `/register` mengembalikan 404 pada production. |
| Project stability | Stabil | Tidak ada issue blocking pada URL utama. |

## Catatan Storage

Pesan berikut saat menjalankan command:

```bash
php artisan storage:link
```

dengan output:

```text
The [public/storage] link already exists.
```

adalah kondisi normal. Artinya symbolic link storage sudah aktif dan tidak perlu dibuat ulang.

## Catatan Log

File:

```text
storage/logs/laravel.log
```

masih berisi error lama dari masa development Filament. Log tersebut tidak berada di folder `public`, sehingga tidak terbuka ke publik selama document root hosting diarahkan ke folder `public`.

## Hasil Testing URL

| URL | Status | Hasil |
| --- | ---: | --- |
| `/` | 200 | Beranda normal |
| `/gallery` | 200 | Gallery normal |
| `/photography` | 200 | Photography normal |
| `/media` | 200 | Media normal |
| `/sitemap.xml` | 200 | Sitemap aktif |
| `/robots.txt` | 200 | Robots aktif |
| `/storage/posts/01KSBH77Y01NJSKAHGQNDNENCJ.jpeg` | 200 | Gambar storage tampil |
| `/admin` | 302 | Redirect ke `/admin/login` |
| `/register` | 404 | Registration publik tertutup |

## Command Production

Command audit dan cache production yang sudah dijalankan:

```bash
php artisan route:list --except-vendor
php artisan storage:link
php artisan config:clear
php artisan cache:clear
php artisan optimize
```

## Status Akhir

Project Chapung Art sudah layak online dengan kondisi:

- Project stabil.
- Route publik normal.
- Admin panel terlindungi login.
- Upload gambar dari storage tampil.
- Sitemap dan robots aktif.
- Cache production aktif.
- Route register publik tertutup pada production.
- Tidak ada file sensitif yang terbuka dari folder `public`.

## Catatan Maintenance

- Pastikan document root hosting selalu diarahkan ke folder `public`.
- Jangan membuka `APP_DEBUG=true` di production.
- Backup rutin database SQLite dan folder `storage/app/public`.
- Backup folder `public/images` jika ada aset legalitas atau OpenGraph baru.
- Jika upload gambar tidak tampil, cek ulang `php artisan storage:link` dan permission folder `storage`.
- Setelah update route, config, atau view, jalankan ulang `php artisan optimize`.
- Jika mengubah Blade/Tailwind/Vite asset, jalankan `npm run build`.
- Simpan file `.env` hanya di server, jangan dipublikasikan ke repository publik.
