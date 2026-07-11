# Phase 02 Design System and Public Layout

## Objective

Membangun fondasi visual Chapung Art sebagai premium art marketplace tanpa mengubah route, data binding, checkout flow, cart flow, payment flow, order flow, authentication, atau business logic.

## Palette

Token utama berada di `tailwind.config.js` dan `resources/css/app.css`.

| Token | Hex | Usage |
| --- | --- | --- |
| `chapung.black` | `#050505` | Body background, public shell |
| `chapung.ink` | `#0B0B0D` | Footer, secondary section surface |
| `chapung.charcoal` | `#111113` | Card/surface utama |
| `chapung.graphite` | `#1C1C20` | Elevated dark surface |
| `chapung.line` | `#2A2A30` | Border |
| `chapung.muted` | `#A1A1AA` | Secondary text |
| `chapung.paper` | `#F7F3EA` | Light surface reserve |
| `chapung.gold` | `#C89B3C` | Primary CTA, accent, active nav |
| `chapung.gold-soft` | `#E3C16F` | CTA hover |
| `chapung.maroon` | `#7A1F2B` | Papua Selatan accent reserve |

## Typography

- Primary font: `Inter`, loaded from Google Fonts free CDN in `resources/views/layouts/public.blade.php`.
- Fallback: default system sans stack from Tailwind.
- Display utility: `font-display`.
- Headline style: uppercase, heavy weight, tight tracking only where already used for marketplace editorial identity.
- Body style: `ca-copy`, readable line-height, neutral zinc text.

## Spacing, Radius, Shadow

- Container: `ca-container` / `<x-public.container>` uses `max-w-7xl`, `px-4 sm:px-6 lg:px-8`.
- Section spacing: `ca-section` and `ca-section-soft` use mobile-first vertical rhythm.
- Radius: `rounded-chapung` (`0.625rem`) and `rounded-chapung-lg` (`0.875rem`).
- Shadow: `shadow-chapung-soft` and `shadow-chapung-gold` for subtle premium depth.

## Breakpoints

Mengikuti default Tailwind breakpoints:

| Breakpoint | Width | Usage |
| --- | ---: | --- |
| `sm` | `640px` | Mobile landscape and compact grid changes |
| `md` | `768px` | Gallery/detail grid transitions |
| `lg` | `1024px` | Desktop nav, sticky marketplace/detail panels |
| `xl` | `1280px` | Catalog density |
| `2xl` | `1536px` | Reserved |

## Reusable Components

Komponen dasar dibuat di `resources/views/components/public`:

- `<x-public.container>`: responsive page container.
- `<x-public.section-heading>`: eyebrow, title, description.
- `<x-public.button>`: primary, secondary, ghost button/link.
- `<x-public.badge>`: gold, muted, danger, success badge.
- `<x-public.input>`: input, select, textarea base style.
- `<x-public.card-surface>`: reusable dark surface/card.
- `<x-public.empty-state>`: standardized empty state.
- `<x-public.loading-skeleton>`: catalog loading placeholder.
- `<x-public.alert>`: toast/error/status alert.

CSS utility layer dibuat di `resources/css/app.css`:

- `ca-container`
- `ca-section`
- `ca-section-soft`
- `ca-eyebrow`
- `ca-heading-xl`
- `ca-heading-lg`
- `ca-copy`
- `ca-surface`
- `ca-surface-muted`
- `ca-button`
- `ca-button-primary`
- `ca-button-secondary`
- `ca-button-ghost`
- `ca-badge`
- `ca-badge-gold`
- `ca-badge-muted`
- `ca-field`
- `ca-skeleton`
- `ca-alert`

## Public Layout Changes

- Public layout memakai token baru untuk body, nav, footer, active state, CTA, toast, dan error alert.
- Global search memakai `ca-field`, token border/surface, dan reusable button.
- Artwork card memakai `ca-surface`, token gold, badge component, dan CTA token.
- Empty state partial diarahkan ke reusable component.
- Gallery loading placeholder memakai `<x-public.loading-skeleton>`.

## Files Changed

Created:

- `docs/PHASE_02_DESIGN_SYSTEM.md`
- `resources/views/components/public/alert.blade.php`
- `resources/views/components/public/badge.blade.php`
- `resources/views/components/public/button.blade.php`
- `resources/views/components/public/card-surface.blade.php`
- `resources/views/components/public/container.blade.php`
- `resources/views/components/public/empty-state.blade.php`
- `resources/views/components/public/input.blade.php`
- `resources/views/components/public/loading-skeleton.blade.php`
- `resources/views/components/public/section-heading.blade.php`

Updated:

- `resources/css/app.css`
- `resources/views/gallery.blade.php`
- `resources/views/layouts/public.blade.php`
- `resources/views/partials/public/artwork-card.blade.php`
- `resources/views/partials/public/empty-state.blade.php`
- `resources/views/partials/public/global-search.blade.php`
- `tailwind.config.js`

## Compatibility Notes

- No new npm/composer dependency added.
- No `.env` change.
- No route change.
- No database change.
- No business logic change.
- Google Fonts Inter is free; system font fallback tetap tersedia jika CDN gagal.
- Tailwind opacity modifier inside `@apply` was avoided for custom colors to stay compatible with current Tailwind version.
- Existing pages keep current data binding and form actions.

## Validation

- `php artisan view:cache`: passed.
- `php artisan test --stop-on-failure`: passed, 173 tests, 820 assertions.
- `npx tailwindcss -i ./resources/css/app.css -o ./storage/framework/testing/phase-02-app.css --minify`: passed.
- `npm run build`: blocked by environment-level Vite/Rolldown `spawn EPERM` while loading `vite.config.js`, before app asset compilation.

## Visual Check Scope

Code-level visual review performed for:

- Homepage public layout shell.
- Gallery header, catalog card, empty state, loading skeleton.
- Artwork detail inherited public shell and artwork card related section.
- Cart inherited public shell and empty state styling.
- Checkout inherited public shell and alert/input token compatibility.

Browser screenshot/manual viewport verification still needs to be run in an environment where Vite build/dev server can spawn child processes normally.
