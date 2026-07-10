<?php

namespace App\Http\Controllers;

use App\Models\Artwork;
use App\Models\Artist;
use App\Models\Collection;
use App\Models\HomepageSection;
use App\Models\Photography;
use App\Models\Post;
use App\Services\PageViewTracker;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function __invoke(Request $request, PageViewTracker $pageViewTracker): View
    {
        $pageViewTracker->track($request);

        return view('welcome', [
            'homepageSections' => HomepageSection::active()->orderBy('sort_order')->get()->keyBy('section_key'),
            'featuredArtworks' => Artwork::with(['artist', 'category'])->where('is_featured', true)->latest()->take(6)->get(),
            'featuredPhotographies' => Photography::with(['artist', 'category'])->where('is_featured', true)->latest()->take(6)->get(),
            'featuredArtists' => Artist::active()->featured()->latest()->take(3)->get(),
            'featuredCollections' => Collection::active()->featured()->latest()->take(5)->get(),
            'latestPosts' => Post::where('status', 'published')->latest()->take(3)->get(),
        ]);
    }
}
