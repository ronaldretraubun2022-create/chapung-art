<?php

use App\Models\SeoMeta;
use App\Support\PerformanceCache;
use Illuminate\Database\Eloquent\Model;

if (! function_exists('site_setting')) {
    function site_setting(string $key, mixed $default = null): mixed
    {
        $settings = PerformanceCache::siteSettings();

        $value = $settings[$key] ?? null;

        return filled($value) ? $value : $default;
    }
}

if (! function_exists('site_bank_accounts')) {
    /**
     * @return array<int, array{bank: string, account_number: string, account_name: string}>
     */
    function site_bank_accounts(): array
    {
        $accounts = site_setting('bank_accounts');

        if (is_string($accounts)) {
            $decoded = json_decode($accounts, true);
            $accounts = is_array($decoded) ? $decoded : [];
        }

        if (! is_array($accounts) || $accounts === []) {
            $accounts = config('chapung.bank_accounts', []);
        }

        return collect($accounts)
            ->filter(fn (mixed $account): bool => is_array($account))
            ->map(fn (array $account): array => [
                'bank' => trim((string) ($account['bank'] ?? '')),
                'account_number' => preg_replace('/\D+/', '', (string) ($account['account_number'] ?? '')) ?: '',
                'account_name' => trim((string) ($account['account_name'] ?? '')),
            ])
            ->filter(fn (array $account): bool => $account['bank'] !== '' && $account['account_number'] !== '')
            ->values()
            ->all();
    }
}

if (! function_exists('site_contact_numbers')) {
    /**
     * @return array<int, array{label: string, phone: string, whatsapp: string}>
     */
    function site_contact_numbers(): array
    {
        $contacts = site_setting('contact_numbers');

        if (is_string($contacts)) {
            $decoded = json_decode($contacts, true);
            $contacts = is_array($decoded) ? $decoded : [];
        }

        if (! is_array($contacts) || $contacts === []) {
            $contacts = config('chapung.contact_numbers', []);
        }

        return collect($contacts)
            ->filter(fn (mixed $contact): bool => is_array($contact))
            ->map(fn (array $contact): array => [
                'label' => trim((string) ($contact['label'] ?? 'Admin')),
                'phone' => trim((string) ($contact['phone'] ?? '')),
                'whatsapp' => preg_replace('/\D+/', '', (string) ($contact['whatsapp'] ?? $contact['phone'] ?? '')) ?: '',
            ])
            ->filter(fn (array $contact): bool => $contact['phone'] !== '' && $contact['whatsapp'] !== '')
            ->values()
            ->all();
    }
}

if (! function_exists('seo_meta')) {
    function seo_meta(?string $routeName = null, ?Model $seoable = null, array $fallback = []): array
    {
        $routeName ??= request()->route()?->getName();

        $query = SeoMeta::query();

        $meta = null;

        if ($seoable) {
            $meta = (clone $query)
                ->where('seoable_type', $seoable::class)
                ->where('seoable_id', $seoable->getKey())
                ->first();
        }

        if (! $meta && filled($routeName)) {
            $meta = (clone $query)->where('route_name', $routeName)->first();
        }

        $siteName = site_setting('site_name', config('app.name', 'Chapung Art'));
        $siteDescription = site_setting('site_description', 'Galeri seni, fotografi budaya, dan media kreatif Papua Selatan.');
        $defaultOgImage = asset('images/og-image.jpg');

        $ogImage = $meta?->og_image ?: ($fallback['og_image'] ?? $defaultOgImage);

        if (filled($ogImage) && ! str($ogImage)->startsWith(['http://', 'https://', '/'])) {
            $ogImage = asset('storage/'.$ogImage);
        }

        return [
            'title' => $meta?->meta_title ?: ($fallback['title'] ?? $siteName),
            'description' => $meta?->meta_description ?: ($fallback['description'] ?? $siteDescription),
            'keywords' => $meta?->meta_keywords ?: ($fallback['keywords'] ?? null),
            'og_image' => $ogImage,
            'canonical_url' => $meta?->canonical_url ?: ($fallback['canonical_url'] ?? url()->current()),
            'robots' => $meta?->robots ?: ($fallback['robots'] ?? 'index, follow'),
            'schema_json' => $meta?->schema_json ?: ($fallback['schema_json'] ?? null),
        ];
    }
}
