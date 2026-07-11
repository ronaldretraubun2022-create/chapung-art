# PHASE 05 - Kartu Karya dan Galeri Masonry

## Objective

Membuat tampilan galeri karya yang kuat secara visual, mobile-first, tetap ringan, dan tetap mendukung alur penjualan existing.

## Scope

- Kartu karya reusable untuk daftar galeri, homepage, koleksi, favorit, profil perupa, dan related artwork.
- Masonry gallery memakai CSS native tanpa dependency baru.
- Integrasi filter, sort, dan search server-side existing.
- Karya terjual tetap tampil sebagai arsip publik, tetapi tombol beli tidak aktif.
- Tidak ada perubahan business rule stok, cart, checkout, order, payment, invoice, shipping, notifikasi, autentikasi, atau `.env`.

## Implementation Summary

- `partials.public.artwork-card` diperkuat untuk menampilkan gambar, judul, perupa, kategori, medium, tahun, harga, status, tombol detail, dan tombol tambah keranjang.
- Badge sold dibuat eksplisit dan memprioritaskan status `sold` dibanding badge lain.
- Tombol cart hanya aktif jika `status = available` dan `stock > 0`; status sold/reserved/stok kosong tampil sebagai tombol disabled.
- Aspect ratio gambar stabil dengan fallback rasio berdasarkan dimensi karya bila tersedia.
- Lazy loading dan fallback image tetap memakai `partials.public.image` dan `ImageUploadService::fallbackUrl()`.
- Galeri hasil memakai `.ca-masonry` berbasis CSS columns, tanpa library baru.
- Query galeri memuat data tambahan yang dibutuhkan kartu: `year`, `width`, `height`, `stock`, dan `medium`.

## Filter, Sort, and Search

- Filter existing tetap aktif: kategori, perupa, koleksi, tag, harga, lokasi, rating, stok, limited, downloadable, customizable, featured, dan type chip.
- Sort existing tetap aktif: newest, oldest, price low, price high, popular, rating, dan legacy featured.
- Search katalog tetap memakai request `q` existing dengan debounce form submit aman; tidak menambah endpoint realtime baru.

## Files Created

- `docs/PHASE_05_GALLERY.md`

## Files Updated

- `app/Http/Controllers/ArtworkController.php`
- `resources/css/app.css`
- `resources/views/gallery.blade.php`
- `resources/views/partials/public/artwork-card.blade.php`
- `tests/Feature/GalleryFilterTest.php`

## Validation Checklist

- Gallery desktop: masonry CSS tersedia melalui `data-gallery-masonry` dan `.ca-masonry`.
- Gallery mobile: masonry turun ke 1 column.
- Filter: tetap memakai form `data-marketplace-filter` existing.
- Sort: tetap memakai parameter `sort` existing.
- Search: tetap memakai parameter `q` dengan debounce submit existing.
- Sold state: karya `sold` tetap tampil, badge sold tampil, tombol cart disabled.
- Empty state: tetap memakai `partials.public.empty-state`.
- Add to cart: tetap memakai route `cart.store` dan business rule `CartService` existing.
- `php artisan migrate`: passed, nothing to migrate.
- `php artisan optimize`: passed.
- `php artisan test tests\Feature\GalleryFilterTest.php`: passed, 7 tests / 55 assertions.
- `php artisan test`: failed outside Phase 5 scope; existing homepage/localization assertions expect old copy (`CHAPUNG ART`, `Featured Artwork`, `Explore Artwork`) while current homepage renders artwork-led marketplace copy.
- `npm run build`: failed before compiling assets with Vite `spawn EPERM` while loading `vite.config.js` on Windows environment.

## Known Issues Outside Scope

- Copy lama seperti `curated`/`kurasi` masih ada di sebagian key marketplace existing. Phase 5 hanya menyesuaikan kartu dan galeri, bukan audit copy lintas modul.
- Test homepage/localization existing belum selaras dengan homepage marketplace saat ini.
- Vite build terblokir oleh izin subprocess environment (`spawn EPERM`), bukan oleh syntax CSS/Blade Phase 5.
