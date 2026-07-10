# MariaDB Production Audit

Phase: 27.2 — MariaDB Production  
Target: cPanel/Rumahweb, PHP 8.3, MariaDB, Laravel 13.

## Summary

Audit migration production berfokus pada kompatibilitas MariaDB untuk:

- migration order
- foreign key
- index
- enum
- json
- unique constraint
- cascade/null delete behavior

Hasil audit: schema siap untuk MariaDB production dengan satu compatibility migration tambahan untuk memastikan enum `posts.status` mendukung status `review` pada deployment fresh maupun upgrade.

## Migration Order

Migration dasar tersedia sebelum relasi turunan:

- `users`, `cache`, `jobs`, `sessions`
- `artists`, `artworks`, `photographies`, `categories`, `posts`
- permission tables
- category/artist/collection/tag/media relations
- marketplace tables: customers, orders, payments, shipments, certificates
- SEO, analytics, activity log, performance indexes, invoice fields

Urutan ini aman untuk MariaDB karena tabel referensi dibuat sebelum foreign key yang mengarah kepadanya.

## Foreign Key

Foreign key production memakai Laravel schema builder, bukan raw SQL. Relasi penting:

- `artists.user_id -> users.id` memakai `nullOnDelete`.
- `artworks.category_id -> categories.id` memakai `nullOnDelete`.
- `artworks.artist_id -> artists.id` memakai `nullOnDelete`.
- `artworks.collection_id -> collections.id` memakai `nullOnDelete`.
- `photographies.category_id`, `artist_id`, `collection_id` memakai `nullOnDelete`.
- `posts.author_id -> users.id` memakai `nullOnDelete`.
- `orders.customer_id -> customers.id` memakai `nullOnDelete`.
- `payments.order_id`, `shipments.order_id`, `order_items.order_id` memakai `cascadeOnDelete`.
- `certificates.artwork_id` memakai `cascadeOnDelete`; `certificates.artist_id` memakai `nullOnDelete`.
- Pivot `artwork_tag`, `photography_tag`, `post_tag` memakai `cascadeOnDelete`.

Catatan: `sessions.user_id` sengaja hanya index, bukan foreign key. Ini mengikuti pola Laravel agar session tidak gagal ketika user terhapus.

## Index

Index utama tersedia untuk query production:

- session lookup: `sessions.user_id`, `sessions.last_activity`
- cache expiration: `cache.expiration`, `cache_locks.expiration`
- content filters: category, artist, collection, status, featured, price
- report/analytics: `page_views.viewable_type/viewed_at`, `page_views.device/viewed_at`
- posts published query: `posts.status/published_at`, `posts.category_id/status/published_at`
- polymorphic relations: media, SEO, activity log, page views

Index custom diberi nama eksplisit agar aman pada MariaDB dan rollback tidak bergantung pada nama auto-generated.

## Enum

Enum awal:

- `artworks.status`: `available`, `sold`, `reserved`
- `photographies.status`: `available`, `sold`, `reserved`
- `categories.type`: `artwork`, `photography`, `post`, `general`
- `posts.status`: `draft`, `published`, `archived`

News CMS membutuhkan `posts.status=review`. Migration baru ditambahkan:

```text
database/migrations/2026_07_11_370000_ensure_posts_status_review_enum_for_mariadb.php
```

Migration ini hanya berjalan untuk driver `mysql` atau `mariadb`, sehingga aman untuk SQLite development/testing dan memastikan MariaDB production memiliki enum final:

```sql
ENUM('draft', 'review', 'published', 'archived')
```

## JSON

Kolom JSON yang digunakan:

- `homepage_sections.payload`
- `seo_metas.schema_json`
- `activity_logs.properties`

MariaDB menyimpan JSON sebagai tipe kompatibel dengan validasi JSON pada versi yang mendukung. Aplikasi memakai cast array pada model terkait sehingga data tetap dikelola sebagai array di Laravel.

## Unique Constraint

Unique constraint penting:

- `users.email`
- slug content: artworks, photographies, categories, posts, artists, collections, exhibitions
- `tags.slug`
- `site_settings.key`
- `orders.order_number`
- `orders.invoice_number`
- `certificates.certificate_number`
- pivot uniqueness: `artwork_id/tag_id`, `photography_id/tag_id`, `post_id/tag_id`
- permission uniqueness mengikuti Spatie Permission

MariaDB mengizinkan multiple `NULL` pada unique nullable field seperti `artworks.sku` dan `orders.invoice_number`, sesuai kebutuhan aplikasi.

## Cascade and Null Delete

Cascade dipakai hanya untuk child data yang tidak bermakna tanpa parent:

- order items, payments, shipments mengikuti order.
- certificates mengikuti artwork.
- exhibition items mengikuti exhibition.
- content-tag pivot mengikuti content/tag.

`nullOnDelete` dipakai untuk relasi editorial atau customer-facing agar data historis tetap aman:

- author, artist, category, collection, customer, user.

## Production Migration Command

Jalankan di cPanel setelah `.env` MariaDB siap:

```bash
php artisan migrate --force
```

Jika migrate gagal karena driver hosting tidak mengenal `mariadb`, ubah:

```env
DB_CONNECTION=mysql
```

Lalu ulangi:

```bash
php artisan optimize:clear
php artisan migrate --force
```
