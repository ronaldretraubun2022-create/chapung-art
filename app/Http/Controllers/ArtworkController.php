<?php

namespace App\Http\Controllers;

use App\Models\Artwork;
use Illuminate\Contracts\View\View;

class ArtworkController extends Controller
{
    public function index(): View
    {
        $artworks = Artwork::query()
            ->latest()
            ->get();

        $featuredArtworks = $artworks
            ->where('is_featured', true)
            ->take(4);

        return view('welcome', [
            'artworks' => $artworks,
            'featuredArtworks' => $featuredArtworks->isNotEmpty()
                ? $featuredArtworks
                : $artworks->take(4),
        ]);
    }

    public function gallery(): View
    {
        return view('gallery', [
            'artworks' => Artwork::query()
                ->latest()
                ->get(),
        ]);
    }

    public function show(string $slug): View
    {
        $artwork = Artwork::where('slug', $slug)->firstOrFail();

        return view('artwork-detail', [
            'artwork' => $artwork,
        ]);
    }
}
