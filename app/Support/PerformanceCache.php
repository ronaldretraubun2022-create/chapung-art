<?php

namespace App\Support;

use App\Models\Artist;
use App\Models\Artwork;
use App\Models\Category;
use App\Models\Collection as ArtCollection;
use App\Models\HomepageSection;
use App\Models\Photography;
use App\Models\Post;
use App\Models\SiteSetting;
use App\Models\Tag;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;

class PerformanceCache
{
    public const SITE_SETTINGS = 'site_settings';
    public const HOMEPAGE_SECTIONS = 'homepage_sections.active';
    public const HOMEPAGE_PAYLOAD = 'homepage.payload';
    public const HOMEPAGE_SECTION_IDS = 'homepage.section_ids';
    public const HOMEPAGE_FEATURED_ARTWORK_IDS = 'homepage.featured_artwork_ids';
    public const HOMEPAGE_FEATURED_PHOTOGRAPHY_IDS = 'homepage.featured_photography_ids';
    public const HOMEPAGE_FEATURED_ARTIST_IDS = 'homepage.featured_artist_ids';
    public const HOMEPAGE_FEATURED_COLLECTION_IDS = 'homepage.featured_collection_ids';
    public const HOMEPAGE_LATEST_POST_IDS = 'homepage.latest_post_ids';
    public const ACTIVE_COLLECTIONS = 'collections.active.options';
    public const ACTIVE_COLLECTION_IDS = 'collections.active.ids';
    public const ACTIVE_TAGS = 'tags.active.options';
    public const ACTIVE_TAG_IDS = 'tags.active.ids';

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

    public static function homepageSections(): Collection
    {
        if (self::bypassObjectCache()) {
            return self::homepageSectionsQuery()->get()->keyBy('section_key');
        }

        $sectionIds = Cache::remember(self::HOMEPAGE_SECTION_IDS, self::TTL, fn (): array => self::homepageSectionsQuery()
            ->pluck('id')
            ->all());

        return self::orderedModelsByIds(self::homepageSectionsQuery(), $sectionIds)->keyBy('section_key');
    }

    /**
     * @return array<string, mixed>
     */
    public static function homepagePayload(): array
    {
        return [
            'homepageSections' => self::homepageSections(),
            'featuredArtworks' => self::featuredArtworks(),
            'featuredPhotographies' => self::featuredPhotographies(),
            'featuredArtists' => self::featuredArtists(),
            'featuredCollections' => self::featuredCollections(),
            'latestPosts' => self::latestPosts(),
        ];
    }

    public static function activeCategories(?string $type = null): EloquentCollection
    {
        $key = 'categories.active.ids.'.($type ?: 'all');

        $query = Category::query()
            ->select(['id', 'name', 'slug', 'type', 'is_active'])
            ->where('is_active', true)
            ->when($type, fn ($query) => $query->where('type', $type))
            ->orderBy('name');

        if (self::bypassObjectCache()) {
            return $query->get();
        }

        $ids = Cache::remember($key, self::TTL, fn (): array => (clone $query)->pluck('id')->all());

        return self::orderedModelsByIds($query, $ids);
    }

    public static function activeCollections(): EloquentCollection
    {
        $query = ArtCollection::query()
            ->select(['id', 'name', 'slug', 'description', 'is_active'])
            ->active()
            ->orderBy('name');

        if (self::bypassObjectCache()) {
            return $query->get();
        }

        $ids = Cache::remember(self::ACTIVE_COLLECTION_IDS, self::TTL, fn (): array => (clone $query)->pluck('id')->all());

        return self::orderedModelsByIds($query, $ids);
    }

    public static function activeTags(?string $type = null): EloquentCollection
    {
        $key = self::ACTIVE_TAG_IDS.'.'.($type ?: 'all');

        $query = Tag::query()
            ->select(['id', 'name', 'slug', 'type', 'is_active'])
            ->active()
            ->when($type, fn ($query) => $query->where(function ($query) use ($type): void {
                $query->where('type', $type)->orWhereNull('type');
            }))
            ->orderBy('name');

        if (self::bypassObjectCache()) {
            return $query->get();
        }

        $ids = Cache::remember($key, self::TTL, fn (): array => (clone $query)->pluck('id')->all());

        return self::orderedModelsByIds($query, $ids);
    }

    public static function flushSiteSettings(): void
    {
        Cache::forget(self::SITE_SETTINGS);
    }

    public static function flushHomepage(): void
    {
        Cache::forget(self::HOMEPAGE_SECTIONS);
        Cache::forget(self::HOMEPAGE_PAYLOAD);
        Cache::forget(self::HOMEPAGE_SECTION_IDS);
        Cache::forget(self::HOMEPAGE_FEATURED_ARTWORK_IDS);
        Cache::forget(self::HOMEPAGE_FEATURED_PHOTOGRAPHY_IDS);
        Cache::forget(self::HOMEPAGE_FEATURED_ARTIST_IDS);
        Cache::forget(self::HOMEPAGE_FEATURED_COLLECTION_IDS);
        Cache::forget(self::HOMEPAGE_LATEST_POST_IDS);
    }

    public static function flushTaxonomy(): void
    {
        Cache::forget(self::ACTIVE_COLLECTIONS);
        Cache::forget(self::ACTIVE_COLLECTION_IDS);
        Cache::forget(self::ACTIVE_TAGS);

        foreach (['all', 'artwork', 'photography', 'post'] as $type) {
            Cache::forget('categories.active.options.'.$type);
            Cache::forget('categories.active.ids.'.$type);
            Cache::forget(self::ACTIVE_TAGS.'.'.$type);
            Cache::forget(self::ACTIVE_TAG_IDS.'.'.$type);
        }
    }

    public static function flushContent(): void
    {
        self::flushHomepage();
        self::flushTaxonomy();
    }

    private static function bypassObjectCache(): bool
    {
        return app()->environment(['local', 'testing']);
    }

    private static function homepageSectionsQuery(): Builder
    {
        return HomepageSection::query()
            ->active()
            ->orderBy('sort_order');
    }

    private static function featuredArtworks(): EloquentCollection
    {
        $query = Artwork::query()
            ->select(['id', 'title', 'slug', 'artist_id', 'category_id', 'artist_name', 'thumbnail', 'price', 'status', 'is_featured', 'created_at'])
            ->with(['artist:id,name', 'category:id,name'])
            ->where('is_featured', true)
            ->latest()
            ->take(6);

        return self::homepageModels(self::HOMEPAGE_FEATURED_ARTWORK_IDS, $query);
    }

    private static function featuredPhotographies(): EloquentCollection
    {
        $query = Photography::query()
            ->select(['id', 'title', 'slug', 'artist_id', 'category_id', 'photographer_name', 'location', 'province', 'license', 'thumbnail', 'price', 'status', 'is_featured', 'created_at'])
            ->with(['artist:id,name', 'category:id,name'])
            ->where('is_featured', true)
            ->latest()
            ->take(6);

        return self::homepageModels(self::HOMEPAGE_FEATURED_PHOTOGRAPHY_IDS, $query);
    }

    private static function featuredArtists(): EloquentCollection
    {
        $query = Artist::query()
            ->select(['id', 'name', 'slug', 'photo', 'origin_area', 'specialization', 'is_active', 'is_featured', 'created_at'])
            ->active()
            ->featured()
            ->withCount(['artworks', 'photographies'])
            ->latest()
            ->take(4);

        return self::homepageModels(self::HOMEPAGE_FEATURED_ARTIST_IDS, $query);
    }

    private static function featuredCollections(): EloquentCollection
    {
        $query = ArtCollection::query()
            ->select(['id', 'name', 'slug', 'description', 'cover_image', 'banner_image', 'is_active', 'is_featured', 'created_at'])
            ->active()
            ->featured()
            ->withCount(['artworks', 'photographies'])
            ->latest()
            ->take(4);

        return self::homepageModels(self::HOMEPAGE_FEATURED_COLLECTION_IDS, $query);
    }

    private static function latestPosts(): EloquentCollection
    {
        $query = Post::query()
            ->select(['id', 'title', 'slug', 'category_id', 'excerpt', 'featured_image', 'thumbnail', 'author_name', 'status', 'published_at', 'created_at'])
            ->with(['category:id,name'])
            ->where('status', 'published')
            ->latest('published_at')
            ->take(3);

        return self::homepageModels(self::HOMEPAGE_LATEST_POST_IDS, $query);
    }

    private static function homepageModels(string $cacheKey, Builder $query): EloquentCollection
    {
        if (self::bypassObjectCache()) {
            return $query->get();
        }

        $ids = Cache::remember($cacheKey, 600, fn (): array => (clone $query)->pluck('id')->all());

        return self::orderedModelsByIds($query, $ids);
    }

    /**
     * @param  array<int, int|string>  $ids
     */
    private static function orderedModelsByIds(Builder $query, array $ids): EloquentCollection
    {
        $ids = array_values(array_filter(array_map('intval', $ids)));

        if ($ids === []) {
            return $query->getModel()->newCollection();
        }

        return (clone $query)
            ->reorder()
            ->whereIn($query->getModel()->getQualifiedKeyName(), $ids)
            ->get()
            ->sortBy(fn ($model): int|false => array_search((int) $model->getKey(), $ids, true))
            ->values();
    }
}
