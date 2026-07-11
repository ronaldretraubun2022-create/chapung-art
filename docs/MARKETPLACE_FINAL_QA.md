# PHASE 10 - Marketplace Final QA

Dokumen ini merangkum QA akhir marketplace Chapung Art untuk fase marketplace katalog, detail produk, cart, checkout, review, favorit, digital download, dan order management.

## Scope

- UI marketplace publik.
- Responsive desktop, tablet, dan mobile.
- Performa query, pagination, cache, dan asset loading.
- Security route customer, admin, invoice, upload, dan private download.
- Automated testing dan production build.
- Manual verification sebelum deploy.

## UI

- Homepage, katalog, detail artwork, cart, checkout, dashboard customer, order history, invoice, dan contact memakai layout dark premium Chapung Art.
- Marketplace header, secondary navigation, language switcher, cart, favorite, dan search tetap tersedia di frontend.
- Empty state tersedia untuk katalog, favorites, cart, reviews, orders, dan data marketplace kosong.
- Loading skeleton katalog tetap tersedia melalui `data-catalog-skeleton`.
- File private digital tidak ditampilkan sebagai URL publik di HTML.

## Responsive

- Grid katalog memakai 2 kolom mobile, 3 kolom tablet, dan 4 kolom desktop.
- Detail product, cart, checkout, dashboard customer, dan order detail memakai breakpoint `sm`, `md`, dan `lg`.
- CTA utama tetap berupa tombol nyata dan tetap usable pada viewport kecil.
- Manual verification tetap wajib dilakukan di browser dengan ukuran mobile, tablet, dan desktop.

## Performance

- Katalog memakai pagination.
- Query katalog/detail memakai eager loading untuk artist, category, collection, tags, media, favorites, dan reviews sesuai halaman.
- Homepage dan taxonomy cache memiliki invalidation pada model terkait dari fase sebelumnya.
- Asset production dibuat melalui Vite dan manifest berada di `public/build/manifest.json` setelah `npm run build`.
- Gambar publik memakai partial image/fallback dan lazy loading pada kartu/listing.

## Security

- Admin panel terlindungi auth Filament dan role/admin email.
- Customer dashboard, order history, invoice, favorites, review submission, dan digital download membutuhkan login sesuai route masing-masing.
- Invoice dan order detail memakai authorization berbasis customer ownership atau admin permission.
- Digital master file disimpan di `storage/app/private` melalui disk `local`; tidak dibuat symlink public.
- `.env`, `public/build`, dan `public/storage` tetap diabaikan oleh Git.
- `.env.example` tidak boleh berisi password SMTP atau secret nyata.

## Testing

Automated coverage marketplace meliputi:

- Security audit dan security headers.
- Error pages dan logging.
- Performance cache invalidation.
- Image upload validation.
- Shopping cart dan checkout.
- Invoice PDF.
- Artist public profile.
- Homepage premium.
- Global search.
- Gallery filter.
- Mail configuration.
- Localization.
- Product detail marketplace.
- Review/rating.
- Favorites.
- Digital download.
- Order management.
- Final marketplace QA route, UI, security, and documentation guard.

## Manual Verification

Jalankan sebelum deploy atau demo final:

1. Buka `/`, `/artworks`, `/gallery`, `/photography`, `/artists`, `/news`, `/about`, `/contact`.
2. Cek katalog pada mobile, tablet, dan desktop.
3. Coba search realtime, filter, sort, dan pagination katalog.
4. Buka detail artwork, cek gallery, price, review, favorite, cart CTA, digital download state.
5. Tambah item ke cart, update quantity, hapus item, kupon, dan estimasi ongkir.
6. Lakukan checkout, pastikan order sukses, invoice, dan payment info tampil.
7. Login customer, buka `/dashboard`, `/orders`, dan detail order.
8. Login admin, buka `/admin/orders`, cek status action, invoice action, payment, shipment, dan status history.
9. Cek file private digital dan proof payment tidak bisa diakses lewat URL public.
10. Jalankan `php artisan optimize:clear`, `php artisan migrate`, `php artisan test`, dan `npm run build`.

## Production Notes

- Jangan commit `.env`, credential SMTP, backup, atau private storage.
- Jalankan `php artisan migrate --force` di production setelah deploy source terbaru.
- Jalankan `php artisan storage:link` hanya untuk public storage.
- Jangan membuat symlink dari `storage/app/private` ke public.
- Pastikan `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://chapungart.com`, dan HTTPS aktif.

## Known Residual Risk

- Visual responsive QA tetap membutuhkan pemeriksaan manual di browser karena automated test hanya memverifikasi guard dan markup utama.
- Payment gateway otomatis belum menjadi scope fase ini; pembayaran masih manual/konfirmasi admin sesuai fase marketplace saat ini.
- File build di `public/build` dihasilkan lokal oleh Vite dan tidak dilacak Git karena diabaikan `.gitignore`.
