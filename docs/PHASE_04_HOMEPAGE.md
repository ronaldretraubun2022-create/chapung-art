# PHASE 04 - Homepage Marketplace Seni

## Objective

Mengubah homepage menjadi etalase utama karya para perupa Chapung Art dengan prioritas karya, profil perupa, kategori, koleksi, dan proses penjualan.

## Scope

- Hero karya unggulan berbasis data karya yang tersedia.
- Section karya terbaru, karya tersedia, kategori karya, perupa pilihan, koleksi karya, fotografi dan karya digital, karya terjual, cerita perupa, serta berita/event/video pendukung.
- Data memakai model dan relasi existing: `Artwork`, `Artist`, `Category`, `Collection`, `Photography`, dan `Post`.
- Tidak ada dependency baru.
- Tidak ada perubahan checkout, keranjang, pembayaran, invoice, order, stok, pengiriman, notifikasi, autentikasi, atau `.env`.

## Implementation Summary

- Homepage tetap memakai route existing `Route::get('/', HomeController::class)->name('home')`.
- Payload homepage diperluas melalui `App\Support\PerformanceCache::homepagePayload()` agar query tetap terpusat dan ringan.
- Kartu karya memakai partial existing `partials.public.artwork-card` supaya harga, status, tombol detail, dan add-to-cart tetap konsisten.
- Nama perupa pada kartu karya menjadi link jika relasi `artist.slug` tersedia.
- Partial gambar publik sekarang memiliki skeleton saat gambar dimuat dan fallback saat gambar rusak.
- Footer existing tetap dipakai dari `layouts.public`.

## Dynamic Data

- Hero: karya unggulan, fallback ke karya tersedia, fallback ke karya terbaru.
- Karya terbaru: data `latestArtworks`.
- Karya tersedia: data `availableArtworks` dengan `status = available` dan `stock > 0`.
- Kategori karya: data kategori aktif dengan hitungan karya tersedia.
- Perupa pilihan: data perupa aktif dan featured.
- Koleksi karya: data koleksi aktif dan featured.
- Fotografi dan karya digital: data fotografi featured dan karya digital berbasis medium/license/download flag.
- Karya terjual: data karya `status = sold` atau stok kosong.
- Cerita perupa: data perupa aktif yang memiliki bio.
- Berita pendukung: post published terbaru, dibatasi agar tidak lebih dominan daripada karya.

## Empty, Skeleton, and Fallback

- Empty state memakai `partials.public.empty-state` di setiap section yang datanya kosong.
- Skeleton memakai `x-public.loading-skeleton` pada fallback hero kosong dan `ca-skeleton` di partial gambar.
- Fallback image memakai `ImageUploadService::fallbackUrl()` untuk path kosong dan event `onerror` untuk file gambar rusak.

## Files Created

- `docs/PHASE_04_HOMEPAGE.md`

## Files Updated

- `app/Models/Category.php`
- `app/Support/PerformanceCache.php`
- `resources/views/welcome.blade.php`
- `resources/views/partials/public/artwork-card.blade.php`
- `resources/views/partials/public/image.blade.php`
- `resources/lang/id/chapung.php`
- `resources/lang/en/chapung.php`

## Validation Checklist

- Homepage desktop: pending manual browser check.
- Homepage mobile: pending manual browser check.
- Data kosong: empty state tersedia di setiap section.
- Gambar rusak: fallback image aktif melalui `onerror`.
- Link detail karya: tersedia pada hero dan kartu karya.
- Link perupa: tersedia pada hero, section perupa, cerita perupa, dan kartu karya jika relasi memuat slug.
- `php artisan test`: pending.
- `npm run build`: pending.

## Known Issues Outside Scope

- Beberapa copy lama di file bahasa dan halaman lain masih memakai istilah kurasi/curated. Homepage Phase 4 tidak memanggil copy tersebut. Perapihan lintas modul sebaiknya dikerjakan pada phase tersendiri agar tidak memperluas scope.
