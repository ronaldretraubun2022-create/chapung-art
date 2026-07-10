<?php

namespace App\Http\Controllers;

use App\Models\Artist;
use App\Models\Photography;
use App\Services\PageViewTracker;
use App\Support\PerformanceCache;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class PhotographyController extends Controller
{
    public function index(Request $request): View
    {
        return view('photography', [
            'photographies' => $this->filteredQuery($request)->latest()->paginate(12)->withQueryString(),
            'categories' => PerformanceCache::activeCategories('photography'),
            'artists' => Artist::query()->select(['id', 'name', 'slug', 'is_active'])->active()->orderBy('name')->get(),
            'collections' => PerformanceCache::activeCollections(),
            'filters' => $request->only(['q', 'category', 'artist', 'collection', 'sort']),
        ]);
    }

    public function show(string $slug, Request $request, PageViewTracker $pageViewTracker): View
    {
        $photo = Photography::with(['artist', 'category', 'collection', 'tags', 'mediaItems'])
            ->where('slug', $slug)
            ->firstOrFail();

        $pageViewTracker->track($request, $photo);

        return view('photography-detail', [
            'photo' => $photo,
            'relatedPhotographies' => Photography::with(['artist', 'category'])
                ->whereKeyNot($photo->id)
                ->when($photo->category_id, fn ($query) => $query->where('category_id', $photo->category_id))
                ->latest()
                ->take(3)
                ->get(),
        ]);
    }

    private function filteredQuery(Request $request)
    {
        return Photography::query()
            ->select(['id', 'title', 'slug', 'category_id', 'artist_id', 'collection_id', 'photographer_name', 'location', 'province', 'license', 'description', 'thumbnail', 'price', 'status', 'is_featured', 'created_at'])
            ->with(['artist:id,name', 'category:id,name', 'collection:id,name,slug'])
            ->when($request->filled('q'), function ($query) use ($request): void {
                $keyword = $request->string('q')->toString();

                $query->where(function ($query) use ($keyword): void {
                    $query->where('title', 'like', "%{$keyword}%")
                        ->orWhere('photographer_name', 'like', "%{$keyword}%")
                        ->orWhere('location', 'like', "%{$keyword}%")
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
