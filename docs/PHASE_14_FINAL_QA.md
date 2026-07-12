# PHASE 14 - Final QA

## Executive Summary

Chapung Art marketplace redesign phases 6-14 have been reviewed through automated tests, route audit, production build, and Git hygiene checks. No new feature was added in this phase. The application is ready for staging or controlled production deployment after manual browser verification on the target hosting environment.

## Test Environment

- Branch: `feat/art-marketplace-redesign`
- Stack: Laravel 13, PHP 8.3, Filament, Blade, Tailwind CSS, Vite
- Local PHP binary: `C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe`
- Environment file: `.env` is ignored by Git and is not tracked
- Public route count: 140 routes from `php artisan route:list`

## Automated Test Results

- `php artisan optimize:clear`: passed
- `php artisan route:list`: passed, 140 routes
- `php artisan test`: passed, 196 tests, 984 assertions
- `npm run build`: passed
- `git diff --check`: pending final doc commit check in final gate

## Production Build Result

Vite production build completed successfully:

- `public/build/manifest.json` - 0.33 kB, gzip 0.16 kB
- `public/build/assets/app-D2Pi92IT.css` - 73.51 kB, gzip 12.59 kB
- `public/build/assets/app-Bne8ZG1s.js` - 51.55 kB, gzip 18.18 kB

Build artifacts remain ignored according to `.gitignore` and are not staged as source changes.

## Manual QA Checklist

- [ ] Homepage `/` renders hero, artwork sections, artists, collections, photography, news, CTA, SEO, and lazy images.
- [ ] Artwork catalog `/artworks` supports search, category/artist/collection/tag filters, sort, pagination, skeleton state, empty state, and responsive grid.
- [ ] Legacy gallery `/gallery` remains available and does not break navigation.
- [ ] Artwork detail `/artwork/{slug}` renders gallery, Product/VisualArtwork schema, price, variants, specs, artist profile, reviews, related products, and purchase CTAs.
- [ ] Sold artwork detail hides active purchase flow and shows archive/sold state.
- [ ] Artists `/artists` and artist profile `/artists/{slug}` render cover/photo, bio, statistics, artworks, collections, reviews, and pagination.
- [ ] Collections `/collections/{slug}` render artwork and photography lists with pagination.
- [ ] Photography `/photography` and `/photography/{slug}` render physical/digital product signals and safe media previews.
- [ ] News/media `/news`, `/media`, `/news/{slug}`, and `/media/{slug}` render editorial cards, category/date/author, Article schema, and lazy YouTube no-cookie embeds when present.
- [ ] Cart `/cart` supports add/update/remove, coupon, shipping estimate, subtotal, total, and empty state.
- [ ] Checkout `/checkout` validates customer/contact/address/shipping/payment fields and shows loading state on submit.
- [ ] Checkout success and invoice routes show payment instructions, bank accounts, contact numbers, and authorization boundaries.
- [ ] Customer dashboard `/dashboard`, `/orders`, and `/orders/{order}` protect customer data and do not expose other customers' orders.
- [ ] Admin `/admin` only allows configured internal emails with internal roles.
- [ ] Light/dark theme toggle works on desktop and mobile and persists locally.
- [ ] 404, 403, 419, 429, 500, and 503 error pages render without stack traces.

## Accessibility Check

- Focus ring is defined globally with `:focus-visible`.
- Navbar, mobile drawer, search modal, cart/checkout buttons, and theme toggle expose accessible labels or ARIA state.
- `prefers-reduced-motion` disables heavy animation and transition duration.
- Image partial generates alt text through `ImageUploadService::altText()` and includes width/height/sizes where applicable.
- Manual keyboard testing is still required in the browser for modal focus loops and long checkout forms.

## Security Check

- `.env` is ignored and not tracked.
- No SMTP password or production secret is stored in `.env.example` or source changes from these phases.
- Admin panel requires Filament auth plus configured internal admin email and role boundary.
- Customer order, invoice, favorites, review submission, and digital downloads require authentication and authorization.
- Digital master files remain in private storage and are not exposed in public HTML, schema, or image paths.
- CSP allows Laravel/Vite/Livewire/Filament, Google Maps, and YouTube no-cookie embeds required by existing public UI.
- Public registration in production remains disabled unless explicitly enabled for customer accounts.

## Performance Check

- Public listing pages use pagination.
- Homepage/taxonomy caches retain model-driven invalidation.
- Above-the-fold hero/detail images use eager loading and high fetch priority.
- Secondary/card/supporting images use lazy loading.
- Public image partial includes responsive `sizes` hints and safe fallback handling.
- No paid CDN, image service, font, template, or plugin was added.

## Known Issues

- Lighthouse was not run in this environment, so no score is reported.
- Visual QA across real mobile/tablet/desktop browsers still needs to be completed manually.
- Payment remains manual transfer/admin confirmation by design; no automatic payment gateway is included.
- Public event/exhibition route is not available yet; exhibition data exists in admin and can be scoped for a future phase.

## Deployment Checklist

- [ ] Confirm production `.env` uses `APP_ENV=production`, `APP_DEBUG=false`, and `APP_URL=https://chapungart.com`.
- [ ] Fill SMTP password only in production `.env` on cPanel; never commit it.
- [ ] Confirm `ADMIN_EMAILS` contains only the five internal manager emails.
- [ ] Run `composer install --no-dev --optimize-autoloader` on hosting if dependencies are not already installed.
- [ ] Run `php artisan migrate --force`.
- [ ] Run `php artisan db:seed --class=RolePermissionSeeder` if roles/permissions need refreshing.
- [ ] Run `php artisan storage:link` only for public storage.
- [ ] Do not symlink `storage/app/private`.
- [ ] Run `php artisan optimize:clear` then production cache commands as appropriate.
- [ ] Upload or build Vite assets so `public/build/manifest.json` exists on hosting.
- [ ] Verify SSL, trusted proxy settings, security headers, and HTTPS redirects on cPanel.

## Rollback Checklist

- [ ] Keep the previous deployed source archive or Git commit available.
- [ ] Back up production database and uploaded files before migration.
- [ ] If deployment fails before migration, restore previous source and clear caches.
- [ ] If migration has run, restore database backup before reverting code when schema compatibility requires it.
- [ ] Re-run `php artisan optimize:clear` after rollback.

## Final Recommendation

Status: ready for staging or controlled production deployment after manual browser QA on the target environment.

Do not deploy automatically from local QA. Push the feature branch, review the PR/diff, run the deployment checklist, then deploy through the existing cPanel workflow.
