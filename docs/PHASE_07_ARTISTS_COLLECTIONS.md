# PHASE 07 - Profil Perupa dan Koleksi Karya

## Objective

Menempatkan perupa sebagai identitas utama di balik setiap karya tanpa membuat dashboard perupa, registrasi mandiri, atau alur input karya publik.

## Scope Implemented

- Daftar perupa memakai kartu profil reusable `partials.public.artist-card`.
- Kartu perupa menampilkan foto/fallback inisial, lokasi aman, spesialisasi, jumlah karya, jumlah fotografi, dan link profil.
- Detail perupa mempertahankan hero cover, foto profil, bio, info perjalanan berkarya, kontak Chapung Art, koleksi, fotografi, review signal, dan SEO existing.
- Detail perupa sekarang menampilkan preview eksplisit untuk karya tersedia dan karya terjual.
- Halaman koleksi memakai fallback copy translation, bukan teks hardcoded.
- Data kosong tetap memakai empty state existing.
- Tidak ada perubahan migration, dashboard, checkout, cart, payment, invoice, order, atau auth flow.

## Files Created

- `docs/PHASE_07_ARTISTS_COLLECTIONS.md`
- `resources/views/partials/public/artist-card.blade.php`

## Files Updated

- `app/Http/Controllers/ArtistController.php`
- `resources/views/artists/index.blade.php`
- `resources/views/artists/show.blade.php`
- `resources/views/collections/show.blade.php`
- `tests/Feature/ArtistPublicProfileTest.php`

## Validation Coverage

- Daftar perupa render dengan kartu marketplace dan fallback foto.
- Detail perupa render cover, foto, bio, SEO, karya, koleksi, review, CTA kontak, karya tersedia, dan karya terjual.
- Koleksi tetap menampilkan judul, deskripsi/fallback, cover, daftar karya, fotografi, pagination, dan empty state.
- Inactive artist tetap tidak public.

## Security Notes

- Tidak ada input karya mandiri di frontend.
- Tidak ada statistik sensitif atau credential yang ditampilkan.
- Link sosial tetap dinormalisasi ke URL HTTP/HTTPS dan memakai `rel="nofollow noopener"`.

## Commands

```powershell
php artisan optimize:clear
php artisan test
npm run build
git diff --check
php artisan route:list
php artisan migrate:status
```

## Known Issues

- Manual browser QA responsive tetap diperlukan untuk memastikan proporsi kartu perupa di viewport kecil.
