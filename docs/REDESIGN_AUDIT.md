# Chapung Art Redesign Audit

Phase: PHASE 1 - Audit Frontend dan Baseline Aman  
Date: 2026-07-12  
Scope: Audit frontend publik tanpa perubahan desain, business logic, database, Filament, dependency, atau `.env`.

## 1. Current Technology Stack

- Backend: Laravel `^13.8`, PHP `^8.3`.
- Admin: Filament `^5.6` menurut `composer.json`. Catatan: beberapa dokumen lama masih menyebut Filament 4, sehingga perlu diselaraskan pada dokumentasi berikutnya tanpa mengubah package.
- Frontend rendering: Blade server-side templates.
- Styling: Tailwind CSS `^3.1.0`, `@tailwindcss/forms`, Vite `^8.0.0`.
- JavaScript: `resources/js/app.js` dengan Alpine.js, global search debounce, dan favorite sync.
- Asset pipeline: `vite.config.js` memakai input `resources/css/app.css` dan `resources/js/app.js`.
- Public asset baseline: `public/images/logo.svg`, `public/images/og-image.jpg`, `public/images/og.jpg`, `public/images/artwork-placeholder.svg`, legalitas images, `public/robots.txt`, `public/build/manifest.json`.
- Authorization and roles: Spatie Laravel Permission `^8.3`, model `User` mengimplementasikan `FilamentUser`.
- Testing: Pest `^4.7`, PHPUnit config memakai SQLite in-memory untuk testing.
- Marketplace payment model: transfer/manual confirmation and manual shipping estimates from config, no paid gateway dependency.

## 2. Public Routes

Primary public routes in `routes/web.php`:

| URI | Name | Controller | View / Output |
| --- | --- | --- | --- |
| `/` | `home` | `HomeController` | `welcome.blade.php` |
| `/artworks` | `artworks.index` | `ArtworkController@index` | `gallery.blade.php` |
| `/gallery` | `gallery` | `ArtworkController@gallery` | `gallery.blade.php` |
| `/artwork/{slug}` | `artwork.show` | `ArtworkController@show` | `artwork-detail.blade.php` |
| `/photography` | `photography.index` | `PhotographyController@index` | `photography.blade.php` |
| `/photography/{slug}` | `photography.show` | `PhotographyController@show` | `photography-detail.blade.php` |
| `/artists` | `artists.index` | `ArtistController@index` | `artists/index.blade.php` |
| `/artists/{slug}` | `artists.show` | `ArtistController@show` | `artists/show.blade.php` |
| `/collections/{slug}` | `collections.show` | `CollectionController@show` | `collections/show.blade.php` |
| `/news` | `news.index` | `PostController@index` | `news/index.blade.php` |
| `/news/{slug}` | `news.show` | `PostController@show` | `news/show.blade.php` |
| `/media` | `media.index` | `PostController@index` | currently renders `news.index` |
| `/media/{slug}` | `media.show` | `PostController@show` | currently renders `news.show` |
| `/about` | `about` | `PublicPageController@about` | `pages/about.blade.php` |
| `/contact` | `contact` | `PublicPageController@contact` | `pages/contact.blade.php` |
| `/contact` POST | `contact.send` | `PublicPageController@sendContact` | mail and redirect |
| `/search` | `search.index` | `SearchController@index` | `search/index.blade.php` |
| `/search/live` | `search.live` | `SearchController@live` | JSON |
| `/language/{locale}` | `language.switch` | `LocaleController` | redirect back |
| `/lang/{locale}` | `lang.switch` | `LocaleController` | redirect back |
| `/sitemap.xml` | `sitemap` | `SitemapController` | XML response |
| `/certificates/verify/{certificateNumber}` | `certificates.verify` | `CertificateVerificationController` | `certificates/verify.blade.php` |

Marketplace and customer routes:

- Cart: `/cart`, `/cart/items`, `/cart/coupon`, `/cart/shipping-estimate`.
- Checkout: `/checkout`, `/checkout/success/{orderNumber}`.
- Auth-only customer routes: `/dashboard`, `/favorites`, `/orders`, `/orders/{order}`, `/orders/{order}/invoice`, `/orders/{order}/invoice.pdf`, `/artwork/{artwork:slug}/download`, review submission.
- Auth routes from `routes/auth.php`: login, password reset, email verification, logout, optional registration outside production or with `ENABLE_REGISTRATION=true`.

## 3. Public Blade Structure

- Main public shell: `resources/views/layouts/public.blade.php`.
- Auth/customer shell: `resources/views/layouts/app.blade.php`, `resources/views/layouts/guest.blade.php`, `resources/views/layouts/navigation.blade.php`.
- Homepage: `resources/views/welcome.blade.php`.
- Marketplace catalog: `resources/views/gallery.blade.php` used by `/artworks` and `/gallery`.
- Product detail: `resources/views/artwork-detail.blade.php`.
- Photography listing/detail: `resources/views/photography.blade.php`, `resources/views/photography-detail.blade.php`.
- Artist listing/profile/storefront: `resources/views/artists/index.blade.php`, `resources/views/artists/show.blade.php`.
- Collection page: `resources/views/collections/show.blade.php`.
- News listing/detail: `resources/views/news/index.blade.php`, `resources/views/news/show.blade.php`.
- Legacy or currently unused media-specific views: `resources/views/media.blade.php`, `resources/views/media-detail.blade.php`.
- Cart and checkout: `resources/views/cart/index.blade.php`, `resources/views/checkout/create.blade.php`, `resources/views/checkout/success.blade.php`.
- Customer order and invoice: `resources/views/orders/index.blade.php`, `resources/views/orders/show.blade.php`, `resources/views/invoice/show.blade.php`.
- Static pages: `resources/views/pages/about.blade.php`, `resources/views/pages/contact.blade.php`.
- Error pages: `resources/views/errors/*.blade.php` with shared error layout.
- Mail views: `resources/views/mail/*.blade.php`.

## 4. Existing Reusable Components

Public partials:

- `partials.public.image`: centralized image rendering with fallback, alt generation, width/height, lazy loading, decoding, and fetch priority options.
- `partials.public.artwork-card`: marketplace artwork card with image, badge, favorite, rating, price, detail CTA, and add-to-cart form.
- `partials.public.photography-card`: photography card pattern.
- `partials.public.post-card`: news/card pattern.
- `partials.public.empty-state`: reusable public empty state.
- `partials.public.global-search`: reusable global search form and live result panel.
- `partials.public.favorite-button`: auth-aware favorite button and AJAX data attributes.
- `partials.public.payment-information`: bank account and address information block.
- `partials.seo-meta`: shared SEO meta renderer.
- `partials.language-switcher`: shared language switcher for frontend and Filament topbar.

Blade components from Breeze remain available for auth/profile UI:

- `components.application-logo`, `dropdown`, `modal`, `nav-link`, `responsive-nav-link`, form buttons/inputs/errors.

## 5. Artwork Data Flow

Source of truth:

- Model: `App\Models\Artwork`.
- Relationships: `artist`, `category`, `collection`, `tags`, `mediaItems`, `certificates`, `reviews`, `approvedReviews`, `favorites`, `favoritedByUsers`, `pageViews`.
- Public images: `thumbnail`, `og_image`, related `MediaItem` records via public storage.
- Private digital file: `digital_file_path` on local/private disk. It is not rendered as a public URL.

Homepage flow:

- `HomeController` tracks page view and returns `PerformanceCache::homepagePayload()`.
- `PerformanceCache` supplies active homepage sections, featured artworks, featured photography, featured artists, featured collections, and latest posts.
- `welcome.blade.php` renders hero, featured artwork cards, featured artists, collections, photography, news, CTA.

Catalog flow:

- `ArtworkController@index` validates query params, builds `filteredQuery()`, eager loads artist/category/collection, review counts/averages, and favorite existence for logged-in users.
- Filter data uses `PerformanceCache::activeCategories('artwork')`, `PerformanceCache::activeCollections()`, `PerformanceCache::activeTags('artwork')`, active artists query, and distinct artwork locations.
- `gallery.blade.php` renders marketplace header, secondary nav, filters, skeleton state, grid, pagination, and empty state.

Detail flow:

- `ArtworkController@show` loads artwork with artist, category, collection, tags, media, approved reviews, review counts/averages, and favorite state.
- `ArtworkReviewService` determines review eligibility.
- `DigitalDownloadService` determines secure download state.
- `artwork-detail.blade.php` renders gallery, price, variants/specifications, artist profile, reviews, secure download CTA, and related artworks.

Security and safety:

- HTML description is sanitized in model mutators via `HtmlSanitizer`.
- Digital master path is stored private and only served through `DigitalDownloadController` after authorization.
- Image rendering goes through `ImageUploadService::normalizePath()` and fallback image helper.

## 6. Cart and Checkout Data Flow

Cart flow:

- `CartController` delegates all cart state to `CartService`.
- `CartService` stores guest cart in session keys `cart.items` and `cart.meta`.
- Logged-in cart uses session keys scoped by user id, and guest cart merges after login via `LoginEvent` listener in `AppServiceProvider`.
- Cart line data is hydrated from current `Artwork` records to avoid stale price, status, stock, and thumbnail.
- Stock and purchasable status are validated on add/update.
- Shipping estimates and coupons come from `config/chapung.php`.
- `cart/index.blade.php` renders items, update quantity, remove, coupon, shipping estimate, subtotal, estimated total, and empty state.

Checkout flow:

- `CheckoutController@create` redirects empty carts back to cart, creates session checkout token, and renders checkout form.
- `CheckoutRequest` validates customer, address, shipping, payment, and token fields.
- `CheckoutService` verifies checkout token to prevent double submit, sets shipping estimate, starts DB transaction, locks artwork rows with `lockForUpdate()`, validates current stock/status, creates or updates customer, creates order and items, decreases stock, clears cart, and records completed token.
- `checkout/create.blade.php` renders customer details, shipping address, shipping method, manual payment method, payment info, notes, and order summary.
- `checkout/success.blade.php` renders post-order summary and payment information.
- Invoice display/download uses `InvoiceController`, `InvoiceService`, and `OrderAccessService` authorization.

## 7. Existing UI Features

- Dark premium visual baseline on public layout: black/zinc background, yellow/gold accents, uppercase editorial typography, sticky navigation.
- Mobile-first responsive grids across homepage, catalog, detail, artist storefront, photography, news, cart, checkout, orders, and invoice.
- Marketplace catalog header on `/artworks` with logo, category button, large search, language switcher, login/dashboard icon, favorite icon, cart icon.
- Secondary marketplace nav: Lukisan, Fotografi, Karya Digital, Kerajinan, Koleksi Papua, Seniman, Berita.
- Global search in public navbar with realtime debounce and `/search/live` JSON endpoint.
- Catalog search, filter chips, category, artist, collection, tag, price, location, rating, stock, limited, downloadable, customizable, featured, and sort controls.
- Pagination on catalog, artists, photography, news, collection, artist storefront lists, favorites, orders.
- Loading skeleton exists on marketplace catalog through `data-catalog-skeleton` and submit/change handlers.
- Empty state partial is reused on homepage, catalog, artwork reviews, artists, collections, photography, news, search, favorites, orders, and customer dashboard.
- Lazy-loaded images with explicit dimensions are centralized in `partials.public.image` and used on most cards.
- WebP is accepted and validated by upload services/config, but rendering currently outputs regular `<img>` rather than responsive `<picture>` variants.
- Toast and validation feedback exist in `layouts.public.blade.php` for `session('toast')` and `$errors`.
- Error pages exist for 403, 404, 419, 429, 500, and 503.
- Bilingual locale switcher exists on frontend and admin topbar.
- Contact page includes WhatsApp, email, address, contact form, and Google Maps embed/link.
- Manual payment information includes bank accounts and domicile address.
- Security headers, CSRF, throttling, private digital download, invoice authorization, and noindex invoice page are present.

## 8. Missing UI Features

These are audit findings only. They are not implemented in this phase.

- `resources/views/media.blade.php` and `resources/views/media-detail.blade.php` exist but are not used by `PostController`; `/media` currently renders news views.
- Public event/exhibition route is not present, although `Exhibition` models/resources exist.
- Loading skeleton is concentrated on the marketplace catalog; most other list pages rely on normal page loads without skeleton states.
- Global search JavaScript status strings are hardcoded in English in `resources/js/app.js` instead of translation files.
- Image service accepts WebP, but frontend does not yet generate responsive image sizes or `<picture>` sources.
- Artist storefront has a visible follow button that appears static and is not wired to a backend follow feature.
- Checkout has token-based double-submit protection, but the submit button does not visually enter a loading/disabled state on submit.
- Media/news taxonomy and labels need clarification before redesign because `/media` and `/news` share the same controller flow.
- Five internal manager access is gated through `ADMIN_EMAILS`, but existing role names still include Curator, Artist, Photographer, and Journalist. This is broader than the product rule language and should be clarified before future admin UX copy changes.

## 9. Five Internal Managers Access Overview

Current implementation:

- Admin panel lives at `/admin` through `AdminPanelProvider` and Filament auth middleware.
- `User::canAccessPanel()` requires the user to be a configured admin email through `config('chapung.admin_emails')`.
- If permission tables exist, `Customer` role is blocked from Filament.
- Allowed admin role names are currently `Super Admin`, `Administrator`, `Curator`, `Artist`, `Photographer`, and `Journalist`, but they still require configured admin email access.
- `RolePermissionSeeder` creates roles and granular permissions, and assigns configured admin emails without roles to `Super Admin`.
- `Gate::before()` grants all permissions to `Super Admin` and legacy configured admins without roles.
- Public registration is disabled in production unless `ENABLE_REGISTRATION=true`.

Operational interpretation for the redesign:

- The five internal managers should be represented as configured admin users, preferably through `ADMIN_EMAILS` in production and appropriate roles.
- No public or community-facing self-upload workflow should be introduced.
- Phase 2 should avoid adding curator/approval copy to public UI because current product rule says all work entered by managers is official and ready to publish.

## 10. Files Safe to Modify

Safe for frontend redesign phases, with normal test/build validation:

- `resources/views/layouts/public.blade.php`
- `resources/views/welcome.blade.php`
- `resources/views/gallery.blade.php`
- `resources/views/artwork-detail.blade.php`
- `resources/views/photography.blade.php`
- `resources/views/photography-detail.blade.php`
- `resources/views/artists/index.blade.php`
- `resources/views/artists/show.blade.php`
- `resources/views/collections/show.blade.php`
- `resources/views/news/index.blade.php`
- `resources/views/news/show.blade.php`
- `resources/views/pages/about.blade.php`
- `resources/views/pages/contact.blade.php`
- `resources/views/search/index.blade.php`
- `resources/views/partials/public/*.blade.php`
- `resources/views/partials/seo-meta.blade.php`
- `resources/views/partials/language-switcher.blade.php`
- `resources/css/app.css`
- `resources/js/app.js`
- `tailwind.config.js`
- `vite.config.js` only if asset input or build behavior must change.
- Public static images under `public/images` when replacing or adding free-owned assets.

Safe with extra caution because they affect marketplace behavior:

- `app/Http/Controllers/ArtworkController.php`
- `app/Http/Controllers/ArtistController.php`
- `app/Http/Controllers/CollectionController.php`
- `app/Http/Controllers/PhotographyController.php`
- `app/Http/Controllers/PostController.php`
- `app/Http/Controllers/SearchController.php`
- `app/Services/SearchService.php`
- `app/Support/PerformanceCache.php`

## 11. Files That Must Be Protected

Avoid modifying in redesign phases unless a later phase explicitly requires it:

- `.env` and any local credential file.
- `database/migrations/*` and production schema files.
- `app/Filament/*` resources/pages/widgets unless an admin phase explicitly allows it.
- Order, payment, shipment, invoice, certificate, and digital download services/controllers unless the phase targets those workflows.
- `app/Services/CheckoutService.php`, `app/Services/CartService.php`, `app/Services/InvoiceService.php`, `app/Services/DigitalDownloadService.php`, `app/Services/OrderAccessService.php`.
- Models involved in stock/order/payment integrity unless changing behavior is part of a dedicated phase.
- `routes/web.php` route names used by existing tests and public links unless the phase explicitly calls for route changes.
- `public/build/*` should be generated by `npm run build`, not manually edited.
- `public/storage`, `storage/app/private`, backups, and logs.

## 12. Technical Risks

- Documentation mismatch: AGENTS header references Filament 4, while `composer.json` currently requires Filament `^5.6`.
- `/media` route names exist, but controller returns news views. This may confuse SEO, nav copy, and redesign ownership.
- Existing roles include curator/artist/photographer/journalist admin roles, while product rule says there is no curator workflow and only five internal managers use the dashboard.
- Some frontend strings remain hardcoded in Blade or JS, especially search status text and some English labels in checkout success/invoice/news.
- `gallery.blade.php` includes inline JavaScript for catalog skeleton behavior; if future redesign expands interactivity, moving this into `resources/js/app.js` would reduce duplication.
- Public media legacy views use standalone HTML shells and external `placehold.co` fallback. They are currently unused, but should not be revived without replacing external fallback behavior.
- Some pages use raw `{!! $model->description/content !!}`. Model mutators sanitize main content, but future fields should continue using centralized sanitization.
- Build output in `public/build` is generated and gitignored; manual edits to build assets would be lost.
- Mobile nav is an overflow-x horizontal nav, not a collapsible menu. This is stable but may become cramped as nav items grow.
- WebP upload support exists, but no automated conversion/responsive source set is exposed in the frontend markup.
- Visual QA still needs browser verification because automated tests do not inspect all responsive states.

## 13. Recommended Phase 2 Scope

Recommended next phase should stay frontend-only and avoid business logic changes:

- Define a public design baseline for the redesign using existing `layouts.public` and public partials.
- Normalize public navigation terminology around gallery, marketplace, artists, photography, news/media, cart, and contact.
- Decide whether `/media` should share news UI intentionally or get its own public view in a later dedicated phase.
- Extract catalog inline JS into `resources/js/app.js` only if Phase 2 requires broader interaction reuse.
- Keep all marketplace actions wired to existing routes: cart, checkout, favorite, search, language, contact, artist profile, and artwork detail.
- Preserve manual transfer payment and manual shipping estimates.
- Improve frontend localization coverage for static strings only, without translating database content.
- Keep dashboard/admin access model unchanged unless a future admin security phase explicitly changes roles or `ADMIN_EMAILS` policy.
- Do not add paid services, premium templates, paid fonts, paid icons, paid shipping APIs, or payment gateways.
