# PHASE 08 - Fotografi dan Produk Digital

## Objective

Memastikan fotografi seni dan karya digital diperlakukan sebagai produk resmi marketplace tanpa membuat sistem download baru, tanpa membuka private storage, dan tanpa mengubah payment flow.

## Scope Implemented

- Kartu fotografi menampilkan badge produk digital atau cetak fisik berdasarkan sinyal data existing seperti lisensi, kategori, dan judul.
- Kartu fotografi memakai label localization untuk lokasi, lisensi, dan harga by request.
- Detail fotografi menampilkan atribut produk hanya dari data yang tersedia: tipe produk, tipe pengiriman, lokasi, kamera, lensa, tanggal pengambilan, lisensi, dan stok.
- Gambar utama detail fotografi memakai `loading="eager"` dan `fetchpriority="high"`; thumbnail tambahan tetap lazy melalui partial image existing.
- Produk fotografi digital diberi catatan keamanan bahwa file dirilis melalui konfirmasi Chapung Art dan tidak ada path privat yang tampil publik.
- Karya digital artwork tetap memakai `DigitalDownloadService`, private disk `local`, route `artwork.download`, dan authorization existing.
- Tidak ada migration, dependency, payment flow, atau download flow baru.

## Files Created

- `docs/PHASE_08_DIGITAL_PHOTOGRAPHY.md`
- `tests/Feature/PhotographyDigitalProductTest.php`

## Files Updated

- `resources/views/partials/public/photography-card.blade.php`
- `resources/views/photography-detail.blade.php`
- `resources/lang/id/chapung.php`
- `resources/lang/en/chapung.php`

## Validation Coverage

- Fotografi digital tampil sebagai produk digital di listing dan detail.
- Fotografi fisik tanpa atribut tambahan tetap aman dengan fallback by request dan empty description.
- Detail fotografi tidak menampilkan `digital_file_path` atau `storage/app/private`.
- Gambar utama detail fotografi memakai eager/high priority.

## Security Notes

- Private digital master hanya tersedia lewat `DigitalDownloadController` untuk artwork yang berhak.
- UI fotografi tidak membuat URL download baru dan tidak menampilkan private storage path.
- Upload digital tetap divalidasi oleh `DigitalDownloadService` dan Filament resource existing.

## Known Gap

- Checkout existing masih berbasis alamat dan konfirmasi manual untuk seluruh pesanan. Jika produk digital murni perlu melewati alamat pengiriman, itu membutuhkan phase commerce/backend terpisah agar tidak mengubah flow transaksi stabil di Phase 8.

## Commands

```powershell
php artisan optimize:clear
php artisan test
npm run build
git diff --check
php artisan route:list
php artisan migrate:status
```
