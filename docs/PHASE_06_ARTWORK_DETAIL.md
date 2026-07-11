# Phase 6 - Artwork Detail

## Objective

Meningkatkan halaman detail karya agar informatif, premium, mobile-first, dan berorientasi konversi tanpa mengubah flow cart, checkout, pembayaran, stok, invoice, order, atau pengiriman.

## Scope Implemented

- Galeri gambar detail karya dengan thumbnail selector.
- Image zoom ringan berbasis Alpine/native markup, tanpa dependency baru.
- Loading skeleton dan fallback image state.
- Judul, perupa, tahun, medium, ukuran, kategori, deskripsi, harga, status/stok, lisensi, dan sertifikat hanya bila data tersedia.
- Add to cart tetap memakai route existing `cart.store`.
- Checkout tetap memakai route existing `checkout.create`.
- Sold/unavailable state menonaktifkan aksi pembelian tanpa mengubah stok.
- Share WhatsApp dan Facebook memakai URL publik halaman karya.
- Mobile sticky purchase area untuk aksi cepat.
- Karya terkait dan profil singkat perupa tetap tampil dari data existing.

## Important Notes

- Tidak ada dependency baru.
- Tidak ada migration baru.
- Tidak ada perubahan `.env`.
- Tidak ada perubahan checkout/cart service.
- Tidak menampilkan klaim sertifikat bila `certificate_number` kosong.
- Tidak mengerjakan Phase 7.

## Validation Coverage

- Artwork tersedia: halaman detail menampilkan galeri, harga, add to cart, checkout, share, zoom, sticky mobile, dan karya terkait.
- Artwork terjual: halaman detail menampilkan archive/sold state dan tidak menampilkan checkout aktif.
- Artwork tanpa gambar tambahan: fallback image dan fallback message tampil.
- Add to cart: submit dari detail memakai flow cart existing dan masuk session cart.
- Mobile sticky area: marker `data-mobile-sticky-purchase` tersedia pada detail page.

## Files Updated

- `resources/views/artwork-detail.blade.php`
- `resources/lang/id/chapung.php`
- `resources/lang/en/chapung.php`
- `tests/Feature/ProductDetailMarketplaceTest.php`

## Files Created

- `docs/PHASE_06_ARTWORK_DETAIL.md`

## Commands

```powershell
php -l app\Http\Controllers\ArtworkController.php
php -l app\Models\Artwork.php
php -l tests\Feature\ProductDetailMarketplaceTest.php
php artisan test tests\Feature\ProductDetailMarketplaceTest.php
php artisan test tests\Feature\ShoppingCartTest.php
php artisan migrate
php artisan optimize
php artisan test
npm run build
node .\node_modules\vite\bin\vite.js build
```

## Validation Result

- `php -l app\Http\Controllers\ArtworkController.php`: passed.
- `php -l app\Models\Artwork.php`: passed.
- `php -l tests\Feature\ProductDetailMarketplaceTest.php`: passed.
- `php artisan test tests\Feature\ProductDetailMarketplaceTest.php`: passed, 4 tests / 46 assertions.
- `php artisan test tests\Feature\ShoppingCartTest.php`: passed, 9 tests / 51 assertions.
- `php artisan migrate`: passed, nothing to migrate.
- `php artisan optimize`: passed.
- `php artisan test`: failed outside Phase 6 scope, 174 passed / 2 failed. Isolated failure confirmed in `tests/Feature/HomepagePremiumTest.php` where homepage fixture output does not contain expected `CHAPUNG ART` uppercase hero text.
- `npm run build`: blocked by environment, Vite failed while spawning Windows `net use` with `spawn EPERM` before compiling project assets. Direct `node .\node_modules\vite\bin\vite.js build` produced the same error.

## Known Issues

- Existing homepage test expectation mismatch in `tests/Feature/HomepagePremiumTest.php`; not changed in Phase 6.
- Vite build is blocked in this environment by Windows spawn permission (`spawn EPERM`) during Vite config loading; not caused by Phase 6 code.
