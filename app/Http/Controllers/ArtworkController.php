<?php

namespace App\Http\Controllers;

use App\Models\Artist;
use App\Models\Artwork;
use App\Services\ArtworkReviewService;
use App\Services\DigitalDownloadService;
use App\Services\PageViewTracker;
use App\Support\PerformanceCache;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;

class ArtworkController extends Controller
{
    public function index(Request $request): View
    {
        $request->validate([
            'q' => ['nullable', 'string', 'max:80'],
            'category' => ['nullable', 'integer'],
            'type' => ['nullable', Rule::in(['painting', 'photography', 'digital', 'craft', 'papua'])],
            'artist' => ['nullable', 'integer'],
            'collection' => ['nullable', 'integer'],
            'tag' => ['nullable', 'integer'],
            'price_min' => ['nullable', 'numeric', 'min:0', 'max:999999999999'],
            'price_max' => ['nullable', 'numeric', 'min:0', 'max:999999999999'],
            'location' => ['nullable', 'string', 'max:80'],
            'rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'stock' => ['nullable', 'boolean'],
            'limited' => ['nullable', 'boolean'],
            'downloadable' => ['nullable', 'boolean'],
            'customizable' => ['nullable', 'boolean'],
            'featured' => ['nullable', 'boolean'],
            'sort' => ['nullable', Rule::in(['newest', 'oldest', 'price_asc', 'price_desc', 'popular', 'rating', 'featured'])],
        ]);

        $artworks = $this->filteredQuery($request)
            ->paginate(16)
            ->withQueryString();

        $featuredArtworks = Artwork::with(['artist', 'category'])
            ->select(['id', 'title', 'slug', 'artist_id', 'category_id', 'artist_name', 'thumbnail', 'price', 'status', 'medium', 'stock', 'year', 'width', 'height', 'is_featured', 'created_at'])
            ->withCount('approvedReviews')
            ->withAvg('approvedReviews', 'rating')
            ->when($request->user(), fn ($query, $user) => $query->withExists([
                'favoritedByUsers as is_favorited' => fn ($favorites) => $favorites->whereKey($user->id),
            ]))
            ->where('is_featured', true)
            ->latest()
            ->take(4)
            ->get();

        return view('gallery', [
            'artworks' => $artworks,
            'featuredArtworks' => $featuredArtworks,
            'categories' => PerformanceCache::activeCategories('artwork'),
            'artists' => Artist::query()->select(['id', 'name', 'slug', 'is_active'])->active()->orderBy('name')->get(),
            'collections' => PerformanceCache::activeCollections(),
            'tags' => PerformanceCache::activeTags('artwork'),
            'locations' => $this->availableLocations(),
            'filters' => $request->only([
                'q',
                'category',
                'type',
                'artist',
                'collection',
                'tag',
                'price_min',
                'price_max',
                'location',
                'rating',
                'stock',
                'limited',
                'downloadable',
                'customizable',
                'featured',
                'sort',
            ]),
        ]);
    }

    public function gallery(Request $request): View
    {
        return $this->index($request);
    }

    public function show(string $slug, Request $request, PageViewTracker $pageViewTracker, ArtworkReviewService $reviews, DigitalDownloadService $downloads): View
    {
        $artwork = Artwork::with([
            'artist' => fn ($query) => $query->withCount(['artworks', 'photographies']),
            'category',
            'collection',
            'tags',
            'mediaItems',
            'approvedReviews' => fn ($query) => $query->with('user:id,name')->latest()->take(12),
        ])
            ->withCount('approvedReviews')
            ->withAvg('approvedReviews', 'rating')
            ->when($request->user(), fn ($query, $user) => $query->withExists([
                'favoritedByUsers as is_favorited' => fn ($favorites) => $favorites->whereKey($user->id),
            ]))
            ->where('slug', $slug)
            ->firstOrFail();

        $pageViewTracker->track($request, $artwork);

        return view('artwork-detail', [
            'artwork' => $artwork,
            'canReview' => $reviews->canReview($artwork, $request->user()),
            'hasReviewed' => $request->user() ? $reviews->hasReviewed($artwork, $request->user()) : false,
            'hasDigitalDownload' => $downloads->hasDownload($artwork),
            'canDownloadDigital' => $downloads->canDownload($artwork, $request->user()),
            'relatedArtworks' => Artwork::with(['artist', 'category'])
                ->withCount('approvedReviews')
                ->withAvg('approvedReviews', 'rating')
                ->when($request->user(), fn ($query, $user) => $query->withExists([
                    'favoritedByUsers as is_favorited' => fn ($favorites) => $favorites->whereKey($user->id),
                ]))
                ->whereKeyNot($artwork->id)
                ->when($artwork->category_id, fn ($query) => $query->where('category_id', $artwork->category_id))
                ->latest()
                ->take(4)
                ->get(),
        ]);
    }

    private function filteredQuery(Request $request)
    {
        return Artwork::query()
            ->select([
                'id',
                'title',
                'slug',
                'category_id',
                'artist_id',
                'collection_id',
                'artist_name',
                'description',
                'thumbnail',
                'price',
                'status',
                'medium',
                'material',
                'year',
                'location',
                'license',
                'digital_download_enabled',
                'stock',
                'views',
                'likes',
                'width',
                'height',
                'certificate_number',
                'is_featured',
                'created_at',
            ])
            ->with(['artist:id,name', 'category:id,name', 'collection:id,name,slug'])
            ->withCount('approvedReviews')
            ->withAvg('approvedReviews', 'rating')
            ->when($request->user(), fn ($query, $user) => $query->withExists([
                'favoritedByUsers as is_favorited' => fn ($favorites) => $favorites->whereKey($user->id),
            ]))
            ->when($request->filled('q'), function ($query) use ($request): void {
                $keyword = $request->string('q')->toString();

                $query->where(function ($query) use ($keyword): void {
                    $query->where('title', 'like', "%{$keyword}%")
                        ->orWhere('artist_name', 'like', "%{$keyword}%")
                        ->orWhere('description', 'like', "%{$keyword}%")
                        ->orWhere('medium', 'like', "%{$keyword}%")
                        ->orWhere('material', 'like', "%{$keyword}%")
                        ->orWhereHas('artist', fn ($query) => $query->where('name', 'like', "%{$keyword}%"))
                        ->orWhereHas('category', fn ($query) => $query->where('name', 'like', "%{$keyword}%"));
                });
            })
            ->when($request->filled('category'), fn ($query) => $query->where('category_id', $request->integer('category')))
            ->when($request->filled('type'), fn ($query) => $this->applyMarketplaceType($query, (string) $request->input('type')))
            ->when($request->filled('artist'), fn ($query) => $query->where('artist_id', $request->integer('artist')))
            ->when($request->filled('collection'), fn ($query) => $query->where('collection_id', $request->integer('collection')))
            ->when($request->filled('tag'), fn ($query) => $query->whereHas('tags', fn ($query) => $query
                ->where('tags.id', $request->integer('tag'))
                ->where('tags.is_active', true)))
            ->when($request->filled('price_min'), fn ($query) => $query->where('price', '>=', (float) $request->input('price_min')))
            ->when($request->filled('price_max'), fn ($query) => $query->where('price', '<=', (float) $request->input('price_max')))
            ->when($request->filled('location'), fn ($query) => $query->where('location', $request->string('location')->toString()))
            ->when($request->filled('rating'), fn ($query) => $query->whereHas('approvedReviews', fn ($reviews) => $reviews->where('rating', '>=', $request->integer('rating'))))
            ->when($request->boolean('stock'), fn ($query) => $query->where('status', 'available')->where('stock', '>', 0))
            ->when($request->boolean('limited'), fn ($query) => $query->where(function ($query): void {
                $query->where('is_featured', true)->orWhereNotNull('certificate_number');
            }))
            ->when($request->boolean('downloadable'), fn ($query) => $query->where(function ($query): void {
                $query->where('digital_download_enabled', true)
                    ->orWhere('license', 'like', '%digital%')
                    ->orWhere('license', 'like', '%download%')
                    ->orWhere('medium', 'like', '%digital%');
            }))
            ->when($request->boolean('customizable'), fn ($query) => $query->where(function ($query): void {
                $query->where('medium', 'like', '%custom%')
                    ->orWhere('material', 'like', '%custom%')
                    ->orWhere('description', 'like', '%custom%');
            }))
            ->when($request->boolean('featured') || $request->input('sort') === 'featured', fn ($query) => $query->where('is_featured', true))
            ->when($request->input('sort') === 'oldest', fn ($query) => $query->oldest())
            ->when($request->input('sort') === 'price_asc', fn ($query) => $query->orderByRaw('price IS NULL')->orderBy('price')->latest())
            ->when($request->input('sort') === 'price_desc', fn ($query) => $query->orderByRaw('price IS NULL')->orderByDesc('price')->latest())
            ->when($request->input('sort') === 'popular', fn ($query) => $query->orderByDesc('views')->orderByDesc('likes')->latest())
            ->when($request->input('sort') === 'rating', fn ($query) => $query->orderByDesc('approved_reviews_avg_rating')->orderByDesc('approved_reviews_count')->latest())
            ->when(in_array($request->input('sort'), [null, '', 'newest', 'featured'], true), fn ($query) => $query->latest());
    }

    private function applyMarketplaceType($query, string $type): void
    {
        $terms = match ($type) {
            'painting' => ['lukisan', 'painting', 'paint', 'kanvas', 'canvas'],
            'photography' => ['fotografi', 'photography', 'foto', 'photo', 'print'],
            'digital' => ['digital', 'media', 'illustration', 'ilustrasi'],
            'craft' => ['kerajinan', 'craft', 'woven', 'anyam', 'ukir'],
            'papua' => ['papua', 'merauke', 'asmat', 'marind', 'koleksi'],
            default => [],
        };

        $query->where(function ($query) use ($terms): void {
            foreach ($terms as $term) {
                $query->orWhere('medium', 'like', "%{$term}%")
                    ->orWhere('material', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%")
                    ->orWhereHas('category', fn ($query) => $query->where('name', 'like', "%{$term}%"));
            }
        });
    }

    /**
     * @return Collection<int, string>
     */
    private function availableLocations(): Collection
    {
        return Artwork::query()
            ->whereNotNull('location')
            ->where('location', '!=', '')
            ->distinct()
            ->orderBy('location')
            ->pluck('location')
            ->values();
    }
}
