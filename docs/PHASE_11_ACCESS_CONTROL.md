# PHASE 11 - Access Control

## Current Access Model

- Admin dashboard uses Filament at `/admin` with `FilamentUser::canAccessPanel()` on `App\Models\User`.
- Admin access requires the user email to exist in `config('chapung.admin_emails')`, sourced from `ADMIN_EMAILS`.
- `config('chapung.admin_emails')` now normalizes, deduplicates, and caps configured internal admin emails to five addresses.
- Role and permission storage uses Spatie Permission tables and the web guard.
- Public registration is environment gated and creates customer accounts only. It does not create artist/perupa or dashboard manager accounts.
- Frontend artwork, photography, post, and checkout flows do not provide public self-submission routes for perupa.

## Target Access Matrix

| Role | Primary responsibility | Key permissions |
| --- | --- | --- |
| Super Admin | System owner | All permissions |
| Pengelola Karya | Artwork, artist, photography, collections, categories, certificates, exhibitions | CRUD for art/catalog resources |
| Pengelola Transaksi | Orders, payments, shipments, customers, certificates, sales reports | CRUD for transaction resources |
| Pengelola Konten | News, media, homepage, SEO, categories, tags, exhibitions | CRUD for editorial/content resources |
| Operator Viewer | Internal monitoring | View-only permissions across operational resources |
| Customer | Public marketplace buyer | No admin permissions |

Legacy roles remain available for backward compatibility:

- `Administrator`
- `Curator`
- `Artist`
- `Photographer`
- `Journalist`

These roles are mapped to equivalent or narrower permission sets so existing data does not break while the five-manager access model is adopted.

## Files Changed

- `app/Http/Controllers/Auth/RegisteredUserController.php`
- `app/Models/User.php`
- `config/chapung.php`
- `database/seeders/RolePermissionSeeder.php`
- `tests/Feature/InternalAccessControlTest.php`

## Security Risks

- Production must keep `ADMIN_EMAILS` limited to the real five internal accounts. Extra values are ignored after the first five unique valid emails.
- Public registration remains available outside production for testing. In production it remains disabled unless explicitly enabled with environment configuration.
- Existing legacy roles should be reviewed in production data and migrated operationally to the five target roles over time.
- Filament resource authorization depends on seeded Spatie permissions. Run the role permission seeder during deployment.

## Manual Verification Steps

1. Confirm `.env` production `ADMIN_EMAILS` contains only the five internal manager emails.
2. Run `php artisan db:seed --class=RolePermissionSeeder` after deployment or during controlled maintenance.
3. Log in as each internal manager and verify only expected menu groups/actions appear.
4. Log in as a customer account and confirm `/admin` returns forbidden.
5. Confirm `/register` is not available in production unless customer registration is intentionally enabled.
6. Confirm there is no frontend route or form for public artist/perupa artwork submission.

## Recommended Phase 12 Scope

Continue with responsive, dark mode, and animation polish across the public marketplace without changing access control logic.
