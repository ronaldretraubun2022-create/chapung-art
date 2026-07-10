<?php

namespace App\Http\Controllers;

use App\Models\Collection;
use Illuminate\Contracts\View\View;

class CollectionController extends Controller
{
    public function show(string $slug): View
    {
        $collection = Collection::query()
            ->active()
            ->with(['artworks.artist', 'artworks.category', 'photographies.artist', 'photographies.category'])
            ->where('slug', $slug)
            ->firstOrFail();

        return view('collections.show', [
            'collection' => $collection,
            'artworks' => $collection->artworks()->with(['artist', 'category'])->latest()->paginate(8, ['*'], 'artworks_page'),
            'photographies' => $collection->photographies()->with(['artist', 'category'])->latest()->paginate(8, ['*'], 'photos_page'),
        ]);
    }
}
