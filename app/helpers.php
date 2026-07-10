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
