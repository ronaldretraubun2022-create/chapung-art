<?php

namespace App\Http\Controllers;

use App\Models\Artist;
use App\Models\Artwork;
use App\Models\ArtworkReview;
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

    public function show(string $slug, Request $request): View
    {
        $artist = Artist::query()
            ->select([
                'id',
                'name',
                'slug',
                'photo',
                'bio',
                'origin_area',
                'city',
                'province',
                'country',
                'specialization',
                'education',
                'achievements',
                'exhibitions',
                'instagram',
                'facebook',
                'website',
                'is_active',
                'created_at',
            ])
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

        $artworkStats = $artist->artworks()
            ->selectRaw('COALESCE(SUM(views), 0) as views_total, COALESCE(SUM(likes), 0) as likes_total, COALESCE(SUM(stock), 0) as stock_total, COALESCE(AVG(price), 0) as average_price')
            ->toBase()
            ->first();

        $photoViews = (int) $artist->photographies()->sum('views');
        $viewsTotal = (int) ($artworkStats->views_total ?? 0) + $photoViews;
        $likesTotal = (int) ($artworkStats->likes_total ?? 0);
        $reviewStats = ArtworkReview::query()
            ->approved()
            ->whereHas('artwork', fn ($query) => $query->where('artist_id', $artist->id))
            ->selectRaw('COUNT(*) as review_count, COALESCE(AVG(rating), 0) as rating')
            ->toBase()
            ->first();
        $reviewCount = (int) ($reviewStats->review_count ?? 0);
        $rating = (float) ($reviewStats->rating ?? 0);

        return view('artists.show', [
            'artist' => $artist,
            'coverImage' => $coverImage,
            'storefrontStats' => [
                'available_artworks' => $artist->artworks()->where('status', 'available')->where('stock', '>', 0)->count(),
                'stock_total' => (int) ($artworkStats->stock_total ?? 0),
                'views_total' => $viewsTotal,
                'likes_total' => $likesTotal,
                'review_count' => $reviewCount,
                'rating' => $rating,
                'average_price' => (float) ($artworkStats->average_price ?? 0),
            ],
            'reviewSignals' => ArtworkReview::query()
                ->approved()
                ->with(['artwork' => fn ($query) => $query
                    ->select(['id', 'title', 'slug', 'artist_id', 'category_id', 'artist_name', 'thumbnail', 'price', 'status', 'medium', 'stock', 'is_featured', 'created_at'])
                    ->with(['artist:id,name', 'category:id,name'])])
                ->whereHas('artwork', fn ($query) => $query->where('artist_id', $artist->id))
                ->latest()
                ->take(3)
                ->get(),
            'artworks' => $artist->artworks()
                ->select(['id', 'title', 'slug', 'artist_id', 'category_id', 'collection_id', 'artist_name', 'thumbnail', 'price', 'status', 'medium', 'stock', 'views', 'likes', 'is_featured', 'created_at'])
                ->with(['artist:id,name', 'category:id,name', 'collection:id,name,slug'])
                ->withCount('approvedReviews')
                ->withAvg('approvedReviews', 'rating')
                ->when($request->user(), fn ($query, $user) => $query->withExists([
                    'favoritedByUsers as is_favorited' => fn ($favorites) => $favorites->whereKey($user->id),
                ]))
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
