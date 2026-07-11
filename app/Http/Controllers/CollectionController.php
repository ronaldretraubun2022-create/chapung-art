<?php

namespace App\Http\Controllers;

use App\Models\Collection;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class CollectionController extends Controller
{
    public function show(string $slug, Request $request): View
    {
        $collection = Collection::query()
            ->select(['id', 'name', 'slug', 'description', 'cover_image', 'banner_image', 'is_active'])
            ->active()
            ->withCount(['artworks', 'photographies'])
            ->where('slug', $slug)
            ->firstOrFail();

        return view('collections.show', [
            'collection' => $collection,
            'artworks' => $collection->artworks()
                ->select(['id', 'title', 'slug', 'artist_id', 'category_id', 'collection_id', 'artist_name', 'thumbnail', 'price', 'status', 'medium', 'stock', 'is_featured', 'created_at'])
                ->with(['artist:id,name', 'category:id,name'])
                ->withCount('approvedReviews')
                ->withAvg('approvedReviews', 'rating')
                ->when($request->user(), fn ($query, $user) => $query->withExists([
                    'favoritedByUsers as is_favorited' => fn ($favorites) => $favorites->whereKey($user->id),
                ]))
                ->latest()
                ->paginate(8, ['*'], 'artworks_page'),
            'photographies' => $collection->photographies()
                ->select(['id', 'title', 'slug', 'artist_id', 'category_id', 'collection_id', 'photographer_name', 'location', 'province', 'license', 'thumbnail', 'price', 'status', 'created_at'])
                ->with(['artist:id,name', 'category:id,name'])
                ->latest()
                ->paginate(8, ['*'], 'photos_page'),
        ]);
    }
}
