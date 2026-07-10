<?php

use App\Models\Artwork;
use App\Models\Category;
use App\Models\HomepageSection;
use App\Models\SiteSetting;
use App\Support\PerformanceCache;
use Illuminate\Support\Facades\Cache;

test('site settings cache is invalidated when setting changes', function () {
    Cache::put(PerformanceCache::SITE_SETTINGS, ['site_name' => 'Cached Name'], 3600);

    SiteSetting::create([
        'key' => 'site_name',
        'value' => 'Fresh Name',
        'type' => 'text',
        'group' => 'general',
    ]);

    expect(Cache::has(PerformanceCache::SITE_SETTINGS))->toBeFalse();
});

test('homepage cache is invalidated when homepage section changes', function () {
    Cache::put(PerformanceCache::HOMEPAGE_SECTIONS, collect(), 3600);
    Cache::put(PerformanceCache::HOMEPAGE_PAYLOAD, ['cached' => true], 3600);

    HomepageSection::create([
        'section_key' => 'performance_test_section',
        'title' => 'New Hero',
        'sort_order' => 1,
        'is_active' => true,
    ]);

    expect(Cache::has(PerformanceCache::HOMEPAGE_SECTIONS))->toBeFalse()
        ->and(Cache::has(PerformanceCache::HOMEPAGE_PAYLOAD))->toBeFalse();
});

test('taxonomy and homepage cache is invalidated when content category changes', function () {
    Cache::put(PerformanceCache::HOMEPAGE_PAYLOAD, ['cached' => true], 3600);
    Cache::put('categories.active.options.artwork', collect(), 3600);

    Category::create([
        'name' => 'Painting',
        'slug' => 'painting',
        'type' => 'artwork',
        'is_active' => true,
    ]);

    expect(Cache::has(PerformanceCache::HOMEPAGE_PAYLOAD))->toBeFalse()
        ->and(Cache::has('categories.active.options.artwork'))->toBeFalse();
});

test('homepage payload cache is invalidated when featured artwork changes', function () {
    Cache::put(PerformanceCache::HOMEPAGE_PAYLOAD, ['cached' => true], 3600);

    Artwork::create([
        'title' => 'Cached Artwork',
        'slug' => 'cached-artwork',
        'is_featured' => true,
    ]);

    expect(Cache::has(PerformanceCache::HOMEPAGE_PAYLOAD))->toBeFalse();
});
