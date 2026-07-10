<?php

namespace App\Http\Controllers;

use App\Models\Artist;
use App\Models\Artwork;
use App\Models\Collection;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ArtistController extends Controller
{
    public function index(Request $request): View
    {
        return view('artists.index', [
            'artists' => Artist::query()
                ->active()
                ->withCount(['artworks', 'photographies'])
                ->when($request->filled('q'), function ($query) use ($request): void {
                    $keyword = $request->string('q')->toString();

                    $query->where(function ($query) use ($keyword): void {
                        $query->where('name', 'like', "%{$keyword}%")
                            ->orWhere('origin_area', 'like', "%{$keyword}%")
                            ->orWhere('city', 'like', "%{$keyword}%")
                            ->orWhere('specialization', 'like', "%{$keyword}%")
                            ->orWhere('bio', 'like', "%{$keyword}%");
                    });
                })
                ->when($request->input('sort') === 'featured', fn ($query) => $query->orderByDesc('is_featured'))
                ->orderBy('name')
                ->paginate(12)
                ->withQueryString(),
            'filters' => $request->only(['q', 'sort']),
        ]);
    }

    public function show(string $slug): View
    {
        $artist = Artist::query()
            ->select(['id', 'name', 'slug', 'photo', 'bio', 'origin_area', 'city', 'province', 'country', 'specialization', 'education', 'website', 'is_active'])
            ->active()
            ->withCount(['artworks', 'photographies'])
            ->where('slug', $slug)
            ->firstOrFail();

        $coverImage = Artwork::query()
            ->where('artist_id', $artist->id)
            ->whereNotNull('thumbnail')
            ->orderByDesc('is_featured')
            ->latest()
            ->value('thumbnail') ?: $artist->photo;

        return view('artists.show', [
            'artist' => $artist,
            'coverImage' => $coverImage,
            'artworks' => $artist->artworks()
                ->select(['id', 'title', 'slug', 'artist_id', 'category_id', 'collection_id', 'artist_name', 'thumbnail', 'price', 'status', 'is_featured', 'created_at'])
                ->with(['artist:id,name', 'category:id,name', 'collection:id,name,slug'])
                ->latest()
                ->paginate(8, ['*'], 'artworks_page')
                ->withQueryString(),
            'collections' => Collection::query()
                ->select(['id', 'name', 'slug', 'description', 'cover_image', 'banner_image', 'is_active', 'created_at'])
                ->active()
                ->whereHas('artworks', fn ($query) => $query->where('artist_id', $artist->id))
                ->withCount(['artworks' => fn ($query) => $query->where('artist_id', $artist->id)])
                ->latest()
                ->paginate(6, ['*'], 'collections_page')
                ->withQueryString(),
            'photographies' => $artist->photographies()
                ->select(['id', 'title', 'slug', 'artist_id', 'category_id', 'collection_id', 'photographer_name', 'location', 'province', 'license', 'thumbnail', 'price', 'status', 'created_at'])
                ->with(['artist:id,name', 'category:id,name', 'collection:id,name,slug'])
                ->latest()
                ->paginate(8, ['*'], 'photos_page')
                ->withQueryString(),
        ]);
    }
}
