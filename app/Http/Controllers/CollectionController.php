<?php

namespace App\Http\Controllers;

use App\Models\Collection;
use Illuminate\Contracts\View\View;

class CollectionController extends Controller
{
    public function show(string $slug): View
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
                ->select(['id', 'title', 'slug', 'artist_id', 'category_id', 'collection_id', 'artist_name', 'thumbnail', 'price', 'status', 'created_at'])
                ->with(['artist:id,name', 'category:id,name'])
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
