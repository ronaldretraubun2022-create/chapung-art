<?php

namespace App\Http\Controllers;

use App\Models\Artist;
use App\Models\Artwork;
use App\Models\Category;
use App\Models\Collection;
use App\Services\PageViewTracker;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ArtworkController extends Controller
{
    public function index(Request $request): View
    {
        $artworks = $this->filteredQuery($request)
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $featuredArtworks = Artwork::with(['artist', 'category'])
            ->where('is_featured', true)
            ->latest()
            ->take(4)
            ->get();

        return view('gallery', [
            'artworks' => $artworks,
            'featuredArtworks' => $featuredArtworks,
            'categories' => Category::query()->where('type', 'artwork')->where('is_active', true)->orderBy('name')->get(),
            'artists' => Artist::active()->orderBy('name')->get(),
            'collections' => Collection::active()->orderBy('name')->get(),
            'filters' => $request->only(['q', 'category', 'artist', 'collection', 'sort']),
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
        return Artwork::with(['artist', 'category', 'collection'])
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
            ->when($request->input('sort') === 'price_asc', fn ($query) => $query->orderBy('price'))
            ->when($request->input('sort') === 'price_desc', fn ($query) => $query->orderByDesc('price'))
            ->when($request->input('sort') === 'featured', fn ($query) => $query->orderByDesc('is_featured'));
    }
}
