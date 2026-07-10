<?php

namespace App\Http\Controllers;

use App\Models\Artist;
use App\Models\Artwork;
use App\Services\PageViewTracker;
use App\Support\PerformanceCache;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ArtworkController extends Controller
{
    public function index(Request $request): View
    {
        $request->validate([
            'q' => ['nullable', 'string', 'max:80'],
            'category' => ['nullable', 'integer'],
            'artist' => ['nullable', 'integer'],
            'collection' => ['nullable', 'integer'],
            'tag' => ['nullable', 'integer'],
            'featured' => ['nullable', 'boolean'],
            'sort' => ['nullable', Rule::in(['newest', 'oldest', 'price_asc', 'price_desc', 'featured'])],
        ]);

        $artworks = $this->filteredQuery($request)
            ->paginate(12)
            ->withQueryString();

        $featuredArtworks = Artwork::with(['artist', 'category'])
            ->select(['id', 'title', 'slug', 'artist_id', 'category_id', 'artist_name', 'thumbnail', 'price', 'status', 'is_featured', 'created_at'])
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
            'filters' => $request->only(['q', 'category', 'artist', 'collection', 'tag', 'featured', 'sort']),
        ]);
    }

    public function gallery(Request $request): View
    {
        return $this->index($request);
    }

    public function show(string $slug, Request $request, PageViewTracker $pageViewTracker): View
    {
        $artwork = Artwork::with(['artist', 'category', 'collection', 'tags', 'mediaItems'])
            ->where('slug', $slug)
            ->firstOrFail();

        $pageViewTracker->track($request, $artwork);

        return view('artwork-detail', [
            'artwork' => $artwork,
            'relatedArtworks' => Artwork::with(['artist', 'category'])
                ->whereKeyNot($artwork->id)
                ->when($artwork->category_id, fn ($query) => $query->where('category_id', $artwork->category_id))
                ->latest()
                ->take(3)
                ->get(),
        ]);
    }

    private function filteredQuery(Request $request)
    {
        return Artwork::query()
            ->select(['id', 'title', 'slug', 'category_id', 'artist_id', 'collection_id', 'artist_name', 'description', 'thumbnail', 'price', 'status', 'is_featured', 'created_at'])
            ->with(['artist:id,name', 'category:id,name', 'collection:id,name,slug'])
            ->when($request->filled('q'), function ($query) use ($request): void {
                $keyword = $request->string('q')->toString();

                $query->where(function ($query) use ($keyword): void {
                    $query->where('title', 'like', "%{$keyword}%")
                        ->orWhere('artist_name', 'like', "%{$keyword}%")
                        ->orWhere('description', 'like', "%{$keyword}%");
                });
            })
            ->when($request->filled('category'), fn ($query) => $query->where('category_id', $request->integer('category')))
            ->when($request->filled('artist'), fn ($query) => $query->where('artist_id', $request->integer('artist')))
            ->when($request->filled('collection'), fn ($query) => $query->where('collection_id', $request->integer('collection')))
            ->when($request->filled('tag'), fn ($query) => $query->whereHas('tags', fn ($query) => $query
                ->where('tags.id', $request->integer('tag'))
                ->where('tags.is_active', true)))
            ->when($request->boolean('featured') || $request->input('sort') === 'featured', fn ($query) => $query->where('is_featured', true))
            ->when($request->input('sort') === 'oldest', fn ($query) => $query->oldest())
            ->when($request->input('sort') === 'price_asc', fn ($query) => $query->orderByRaw('price IS NULL')->orderBy('price')->latest())
            ->when($request->input('sort') === 'price_desc', fn ($query) => $query->orderByRaw('price IS NULL')->orderByDesc('price')->latest())
            ->when(in_array($request->input('sort'), [null, '', 'newest', 'featured'], true), fn ($query) => $query->latest());
    }
}
