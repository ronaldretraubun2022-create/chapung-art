# PHASE 3 - Navbar, Mobile Menu, Search, dan Footer

## Objective

Membangun navigasi publik modern, mobile-first, dan fokus pada penemuan karya Chapung Art tanpa mengubah flow backend marketplace.

## Scope

- Navbar publik dengan logo, Beranda, Karya, Perupa, Kategori, Koleksi, Berita/Event bila route tersedia, search trigger, cart indicator, favorit, bahasa, login/dashboard/admin sesuai akses.
- Mobile menu berbentuk dialog kanan yang dapat dibuka dengan tombol keyboard, fokus ke link pertama, dan ditutup dengan tombol close atau Escape.
- Search modal menggunakan endpoint existing `search.live` dan halaman `search.index`.
- Search state: idle, minimum character, loading, empty, dan error.
- Footer lengkap: identitas, menu utama, kategori aktif, koleksi aktif, kontak existing, sosial media dari site settings, dan copyright dinamis.

## Files Created

- `resources/views/partials/public/navigation.blade.php`
- `resources/views/partials/public/footer.blade.php`
- `docs/PHASE_03_NAVIGATION.md`

## Files Updated

- `resources/views/layouts/public.blade.php`
- `resources/views/partials/public/global-search.blade.php`
- `resources/views/welcome.blade.php`
- `resources/js/app.js`
- `resources/css/app.css`
- `resources/lang/id/chapung.php`
- `resources/lang/en/chapung.php`

## Backend Impact

- Tidak ada migration.
- Tidak ada route baru.
- Tidak ada controller/service/model yang diubah.
- Cart, checkout, payment, invoice, stock, order, shipping, notification, dan auth flow tidak disentuh.

## Verification Checklist

- Desktop navbar menampilkan menu utama, search trigger, cart indicator, favorit, bahasa, dan login/dashboard/admin sesuai akses.
- Mobile menu dapat dibuka via tombol dan ditutup via tombol close atau Escape.
- Search memakai endpoint existing dan menampilkan loading, empty, error, serta submit ke halaman search.
- Footer link menu utama, kategori, koleksi, kontak, dan sosial media memakai data existing.
- Tidak ada menu pendaftaran perupa mandiri.
- Tidak ada label kurator baru pada navigation/footer.

## Commands

```powershell
php artisan test
npm run build
```

## Validation Result

- `php artisan test`: PASS, 173 tests / 820 assertions.
- `npm run build`: BLOCKED by local Windows sandbox/Vite `spawn EPERM` while loading `vite.config.js`, before asset compilation.

## Notes

- Link `Koleksi` diarahkan ke section homepage `#collections` karena belum ada route index koleksi publik.
- Link `Kategori` diarahkan ke katalog karya dengan anchor filter kategori existing.
- Footer kategori dan koleksi mengambil data aktif dari `PerformanceCache`.
- Build failure tidak berasal dari perubahan Blade/CSS/JS Phase 3; error terjadi pada child process Vite/Rolldown saat resolving config.
