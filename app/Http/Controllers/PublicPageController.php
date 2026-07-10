<?php

namespace App\Http\Controllers;

use App\Models\Artist;
use App\Models\Artwork;
use App\Models\Photography;
use Illuminate\Contracts\View\View;

class PublicPageController extends Controller
{
    public function about(): View
    {
        return view('pages.about', [
            'artistCount' => Artist::active()->count(),
            'artworkCount' => Artwork::count(),
            'photographyCount' => Photography::count(),
        ]);
    }

    public function contact(): View
    {
        return view('pages.contact');
    }
}
