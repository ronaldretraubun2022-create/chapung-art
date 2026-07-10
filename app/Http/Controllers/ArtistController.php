<?php

namespace App\Http\Controllers;

use App\Models\Artist;
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
            ->active()
            ->with(['artworks.category', 'photographies.category'])
            ->where('slug', $slug)
            ->firstOrFail();

        return view('artists.show', [
            'artist' => $artist,
            'artworks' => $artist->artworks()->with(['category', 'collection'])->latest()->take(8)->get(),
            'photographies' => $artist->photographies()->with(['category', 'collection'])->latest()->take(8)->get(),
        ]);
    }
}
