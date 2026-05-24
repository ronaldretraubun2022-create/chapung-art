<?php

namespace App\Http\Controllers;

use App\Models\Artwork;
use App\Models\Photography;
use App\Models\Post;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        return view('welcome', [
            'featuredArtworks' => Artwork::where('is_featured', true)->latest()->take(6)->get(),
            'featuredPhotographies' => Photography::where('is_featured', true)->latest()->take(6)->get(),
            'latestPosts' => Post::where('status', 'published')->latest()->take(3)->get(),
        ]);
    }
}
