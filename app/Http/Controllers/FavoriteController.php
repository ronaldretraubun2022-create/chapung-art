<?php

namespace App\Http\Controllers;

use App\Models\Artwork;
use App\Services\FavoriteService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function index(Request $request): View
    {
        $artworks = $request->user()
            ->favoriteArtworks()
            ->select([
                'artworks.id',
                'artworks.title',
                'artworks.slug',
                'artworks.category_id',
                'artworks.artist_id',
                'artworks.collection_id',
                'artworks.artist_name',
                'artworks.thumbnail',
                'artworks.price',
                'artworks.status',
                'artworks.medium',
                'artworks.stock',
                'artworks.is_featured',
                'artworks.created_at',
            ])
            ->with(['artist:id,name', 'category:id,name', 'collection:id,name,slug'])
            ->withCount('approvedReviews')
            ->withAvg('approvedReviews', 'rating')
            ->withExists(['favoritedByUsers as is_favorited' => fn ($query) => $query->whereKey($request->user()->id)])
            ->orderByDesc('artwork_favorites.created_at')
            ->paginate(12)
            ->withQueryString();

        return view('favorites.index', [
            'artworks' => $artworks,
        ]);
    }

    public function store(Request $request, Artwork $artwork, FavoriteService $favorites): JsonResponse|RedirectResponse
    {
        $favorites->add($request->user(), $artwork);

        return $this->response($request, true, $favorites->count($request->user()), __('chapung.favorites.added'));
    }

    public function destroy(Request $request, Artwork $artwork, FavoriteService $favorites): JsonResponse|RedirectResponse
    {
        $favorites->remove($request->user(), $artwork);

        return $this->response($request, false, $favorites->count($request->user()), __('chapung.favorites.removed'));
    }

    private function response(Request $request, bool $favorited, int $count, string $message): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            return response()->json([
                'favorited' => $favorited,
                'count' => $count,
                'message' => $message,
            ]);
        }

        return back()->with('toast', ['message' => $message]);
    }
}
