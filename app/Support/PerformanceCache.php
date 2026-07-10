<?php

namespace App\Support;

use App\Models\Artist;
use App\Models\Artwork;
use App\Models\Category;
use App\Models\Collection;
use App\Models\HomepageSection;
use App\Models\Photography;
use App\Models\Post;
use App\Models\SiteSetting;
use App\Models\Tag;
use Illuminate\Support\Facades\Cache;

class PerformanceCache
{
    public const SITE_SETTINGS = 'site_settings';
    public const HOMEPAGE_SECTIONS = 'homepage_sections.active';
    public const HOMEPAGE_PAYLOAD = 'homepage.payload';
    public const ACTIVE_COLLECTIONS = 'collections.active.options';
    public const ACTIVE_TAGS = 'tags.active.options';

    private const TTL = 3600;

    /**
     * @return array<string, mixed>
     */
    public static function siteSettings(): array
    {
        return Cache::remember(self::SITE_SETTINGS, self::TTL, fn (): array => SiteSetting::query()
            ->pluck('value', 'key')
            ->all());
    }

    public static function homepageSections()
    {
        return Cache::remember(self::HOMEPAGE_SECTIONS, self::TTL, fn () => HomepageSection::query()
            ->active()
            ->orderBy('sort_order')
            ->get()
            ->keyBy('section_key'));
    }

    /**
     * @return array<string, mixed>
     */
    public static function homepagePayload(): array
    {
        return Cache::remember(self::HOMEPAGE_PAYLOAD, 600, fn (): array => [
            'homepageSections' => self::homepageSections(),
            'featuredArtworks' => Artwork::query()
                ->select(['id', 'title', 'slug', 'artist_id', 'category_id', 'artist_name', 'thumbnail', 'price', 'status', 'is_featured', 'created_at'])
                ->with(['artist:id,name', 'category:id,name'])
                ->where('is_featured', true)
                ->latest()
                ->take(6)
                ->get(),
            'featuredPhotographies' => Photography::query()
                ->select(['id', 'title', 'slug', 'artist_id', 'category_id', 'photographer_name', 'location', 'province', 'license', 'thumbnail', 'price', 'status', 'is_featured', 'created_at'])
                ->with(['artist:id,name', 'category:id,name'])
                ->where('is_featured', true)
                ->latest()
                ->take(6)
                ->get(),
            'featuredArtists' => Artist::query()
                ->select(['id', 'name', 'slug', 'photo', 'origin_area', 'specialization', 'is_active', 'is_featured', 'created_at'])
                ->active()
                ->featured()
                ->withCount(['artworks', 'photographies'])
                ->latest()
                ->take(4)
                ->get(),
            'featuredCollections' => Collection::query()
                ->select(['id', 'name', 'slug', 'description', 'cover_image', 'banner_image', 'is_active', 'is_featured', 'created_at'])
                ->active()
                ->featured()
                ->withCount(['artworks', 'photographies'])
                ->latest()
                ->take(4)
                ->get(),
            'latestPosts' => Post::query()
                ->select(['id', 'title', 'slug', 'category_id', 'excerpt', 'featured_image', 'thumbnail', 'author_name', 'status', 'published_at', 'created_at'])
                ->with(['category:id,name'])
                ->where('status', 'published')
                ->latest('published_at')
                ->take(3)
                ->get(),
        ]);
    }

    public static function activeCategories(?string $type = null)
    {
        $key = 'categories.active.options.'.($type ?: 'all');

        return Cache::remember($key, self::TTL, fn () => Category::query()
            ->select(['id', 'name', 'slug', 'type', 'is_active'])
            ->where('is_active', true)
            ->when($type, fn ($query) => $query->where('type', $type))
            ->orderBy('name')
            ->get());
    }

    public static function activeCollections()
    {
        return Cache::remember(self::ACTIVE_COLLECTIONS, self::TTL, fn () => Collection::query()
            ->select(['id', 'name', 'slug', 'description', 'is_active'])
            ->active()
            ->orderBy('name')
            ->get());
    }

    public static function activeTags(?string $type = null)
    {
        $key = self::ACTIVE_TAGS.'.'.($type ?: 'all');

        return Cache::remember($key, self::TTL, fn () => Tag::query()
            ->select(['id', 'name', 'slug', 'type', 'is_active'])
            ->active()
            ->when($type, fn ($query) => $query->where(function ($query) use ($type): void {
                $query->where('type', $type)->orWhereNull('type');
            }))
            ->orderBy('name')
            ->get());
    }

    public static function flushSiteSettings(): void
    {
        Cache::forget(self::SITE_SETTINGS);
    }

    public static function flushHomepage(): void
    {
        Cache::forget(self::HOMEPAGE_SECTIONS);
        Cache::forget(self::HOMEPAGE_PAYLOAD);
    }

    public static function flushTaxonomy(): void
    {
        Cache::forget(self::ACTIVE_COLLECTIONS);
        Cache::forget(self::ACTIVE_TAGS);

        foreach (['all', 'artwork', 'photography', 'post'] as $type) {
            Cache::forget('categories.active.options.'.$type);
            Cache::forget(self::ACTIVE_TAGS.'.'.$type);
        }
    }

    public static function flushContent(): void
    {
        self::flushHomepage();
        self::flushTaxonomy();
    }
}
