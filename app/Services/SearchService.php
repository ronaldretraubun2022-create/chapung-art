<?php

namespace App\Services;

use App\Models\Artist;
use App\Models\Artwork;
use App\Models\Collection;
use App\Models\Photography;
use App\Models\Post;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Str;

class SearchService
{
    private const DEFAULT_LIMIT = 5;
    private const MAX_LIMIT = 12;

    /**
     * @return array<string, mixed>
     */
    public function search(?string $term, int $limit = self::DEFAULT_LIMIT): array
    {
        $term = $this->normalizeTerm($term);
        $limit = max(1, min($limit, self::MAX_LIMIT));

        if ($term === '') {
            return $this->emptyPayload($term);
        }

        $groups = collect([
            'artworks' => [
                'label' => 'Artwork',
                'items' => $this->artworks($term, $limit),
            ],
            'artists' => [
                'label' => 'Artist',
                'items' => $this->artists($term, $limit),
            ],
            'photographies' => [
                'label' => 'Photography',
                'items' => $this->photographies($term, $limit),
            ],
            'news' => [
                'label' => 'News',
                'items' => $this->news($term, $limit),
            ],
            'collections' => [
                'label' => 'Collection',
                'items' => $this->collections($term, $limit),
            ],
        ]);

        return [
            'query' => $term,
            'total' => $groups->sum(fn (array $group): int => $group['items']->count()),
            'groups' => $groups,
        ];
    }

    public function normalizeTerm(?string $term): string
    {
        return Str::of((string) $term)
            ->squish()
            ->limit(80, '')
            ->toString();
    }

    /**
     * @return array<string, mixed>
     */
    public function live(?string $term): array
    {
        $payload = $this->search($term);

        return [
            'query' => $payload['query'],
            'total' => $payload['total'],
            'groups' => $payload['groups']->map(fn (array $group): array => [
                'label' => $group['label'],
                'items' => $group['items']->map(fn (array $item): array => $item)->values(),
            ])->all(),
        ];
    }

    private function artworks(string $term, int $limit): SupportCollection
    {
        return Artwork::query()
            ->select(['id', 'title', 'slug', 'artist_id', 'artist_name', 'thumbnail', 'excerpt', 'description', 'status', 'created_at'])
            ->with(['artist:id,name'])
            ->where(function ($query): void {
                $query->whereNull('artist_id')
                    ->orWhereHas('artist', fn ($query) => $query->active());
            })
            ->where(function ($query) use ($term): void {
                $query->where('title', 'like', "%{$term}%")
                    ->orWhere('artist_name', 'like', "%{$term}%")
                    ->orWhere('excerpt', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%")
                    ->orWhere('medium', 'like', "%{$term}%");
            })
            ->latest()
            ->limit($limit)
            ->get()
            ->map(fn (Artwork $artwork): array => $this->resultItem(
                type: 'artwork',
                title: $artwork->title,
                url: route('artwork.show', $artwork->slug),
                subtitle: $artwork->artist_display_name ?: 'Artwork',
                image: $artwork->thumbnail,
                excerpt: $artwork->excerpt ?: $artwork->description,
            ));
    }

    private function artists(string $term, int $limit): SupportCollection
    {
        return Artist::query()
            ->select(['id', 'name', 'slug', 'photo', 'bio', 'origin_area', 'city', 'specialization', 'is_active', 'created_at'])
            ->active()
            ->where(function ($query) use ($term): void {
                $query->where('name', 'like', "%{$term}%")
                    ->orWhere('origin_area', 'like', "%{$term}%")
                    ->orWhere('city', 'like', "%{$term}%")
                    ->orWhere('specialization', 'like', "%{$term}%")
                    ->orWhere('bio', 'like', "%{$term}%");
            })
            ->latest()
            ->limit($limit)
            ->get()
            ->map(fn (Artist $artist): array => $this->resultItem(
                type: 'artist',
                title: $artist->name,
                url: route('artists.show', $artist->slug),
                subtitle: $artist->specialization ?: $artist->origin_area ?: 'Artist',
                image: $artist->photo,
                excerpt: $artist->bio,
            ));
    }

    private function photographies(string $term, int $limit): SupportCollection
    {
        return Photography::query()
            ->select(['id', 'title', 'slug', 'artist_id', 'photographer_name', 'location', 'province', 'thumbnail', 'excerpt', 'description', 'created_at'])
            ->with(['artist:id,name'])
            ->where(function ($query): void {
                $query->whereNull('artist_id')
                    ->orWhereHas('artist', fn ($query) => $query->active());
            })
            ->where(function ($query) use ($term): void {
                $query->where('title', 'like', "%{$term}%")
                    ->orWhere('photographer_name', 'like', "%{$term}%")
                    ->orWhere('location', 'like', "%{$term}%")
                    ->orWhere('province', 'like', "%{$term}%")
                    ->orWhere('excerpt', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%");
            })
            ->latest()
            ->limit($limit)
            ->get()
            ->map(fn (Photography $photo): array => $this->resultItem(
                type: 'photography',
                title: $photo->title,
                url: route('photography.show', $photo->slug),
                subtitle: $photo->artist_display_name ?: $photo->location ?: 'Photography',
                image: $photo->thumbnail,
                excerpt: $photo->excerpt ?: $photo->description,
            ));
    }

    private function news(string $term, int $limit): SupportCollection
    {
        return Post::query()
            ->select(['id', 'title', 'slug', 'excerpt', 'content', 'featured_image', 'thumbnail', 'author_name', 'status', 'published_at', 'created_at'])
            ->where('status', 'published')
            ->where(function ($query) use ($term): void {
                $query->where('title', 'like', "%{$term}%")
                    ->orWhere('excerpt', 'like', "%{$term}%")
                    ->orWhere('content', 'like', "%{$term}%")
                    ->orWhere('author_name', 'like', "%{$term}%");
            })
            ->latest('published_at')
            ->limit($limit)
            ->get()
            ->map(fn (Post $post): array => $this->resultItem(
                type: 'news',
                title: $post->title,
                url: route('news.show', $post->slug),
                subtitle: $post->author_name ?: 'News',
                image: $post->display_image,
                excerpt: $post->excerpt ?: $post->content,
            ));
    }

    private function collections(string $term, int $limit): SupportCollection
    {
        return Collection::query()
            ->select(['id', 'name', 'slug', 'description', 'cover_image', 'banner_image', 'is_active', 'created_at'])
            ->active()
            ->where(function ($query) use ($term): void {
                $query->where('name', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%");
            })
            ->latest()
            ->limit($limit)
            ->get()
            ->map(fn (Collection $collection): array => $this->resultItem(
                type: 'collection',
                title: $collection->name,
                url: route('collections.show', $collection->slug),
                subtitle: 'Collection',
                image: $collection->banner_image ?: $collection->cover_image,
                excerpt: $collection->description,
            ));
    }

    /**
     * @return array<string, string|null>
     */
    private function resultItem(string $type, string $title, string $url, ?string $subtitle = null, ?string $image = null, ?string $excerpt = null): array
    {
        $image = ImageUploadService::normalizePath($image);

        return [
            'type' => $type,
            'title' => $title,
            'url' => $url,
            'subtitle' => $subtitle,
            'image' => $image ? asset('storage/'.$image) : ImageUploadService::fallbackUrl(),
            'excerpt' => Str::of(strip_tags((string) $excerpt))->squish()->limit(120)->toString(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function emptyPayload(string $term): array
    {
        return [
            'query' => $term,
            'total' => 0,
            'groups' => collect([
                'artworks' => ['label' => 'Artwork', 'items' => collect()],
                'artists' => ['label' => 'Artist', 'items' => collect()],
                'photographies' => ['label' => 'Photography', 'items' => collect()],
                'news' => ['label' => 'News', 'items' => collect()],
                'collections' => ['label' => 'Collection', 'items' => collect()],
            ]),
        ];
    }
}
