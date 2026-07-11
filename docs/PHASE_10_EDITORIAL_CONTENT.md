# PHASE 10 - Editorial Content

## Objective

Improve supporting editorial content for Chapung Art without shifting the public product focus away from artworks, artists, collections, cart, checkout, payment, and orders.

## Scope Completed

- Audited public news and media routes powered by `PostController`.
- Kept `/news` and `/media` on the existing route structure while making card links respect the active route namespace.
- Replaced remaining hardcoded editorial interface copy with Laravel translation keys in Indonesian and English.
- Improved the reusable public post card with category, date, reading time, author, excerpt, lazy thumbnail, and localized CTA.
- Added Article JSON-LD fallback data to public post detail pages.
- Updated post detail hero image to load eagerly with high fetch priority.
- Added a reusable supporting media partial for images, local public videos, and YouTube no-cookie embeds.
- Added a small `VideoEmbed` helper to normalize only valid YouTube IDs and URLs.
- Updated CSP `frame-src` to allow `https://www.youtube-nocookie.com` for production video embeds.
- Added automated tests for news index, media route links, article detail SEO/media rendering, and video URL normalization.

## Event and Exhibition Notes

The database and Filament admin already contain exhibition data, but there is no existing public event route in `routes/web.php`. Phase 10 therefore documents this as a future public routing task rather than adding a new event section that could affect navigation or marketplace priorities.

## Files Created

- `app/Support/VideoEmbed.php`
- `resources/views/partials/public/media-item.blade.php`
- `tests/Feature/EditorialContentTest.php`
- `tests/Unit/VideoEmbedTest.php`

## Files Updated

- `app/Http/Controllers/PostController.php`
- `config/security.php`
- `resources/lang/en/chapung.php`
- `resources/lang/id/chapung.php`
- `resources/views/news/index.blade.php`
- `resources/views/news/show.blade.php`
- `resources/views/partials/public/post-card.blade.php`
- `tests/Feature/SecurityHeadersMiddlewareTest.php`

## Protected Areas

- No migrations were added.
- No Filament resources were changed.
- No paid services, new packages, or platform dependencies were added.
- Marketplace cart, checkout, payment, invoice, stock, and order logic were not changed.

## Recommended Phase 11 Scope

Audit and document five-manager dashboard access, admin route protection, and user-facing boundaries without altering existing marketplace flows.
