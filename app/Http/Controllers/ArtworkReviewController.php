<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreArtworkReviewRequest;
use App\Models\Artwork;
use App\Services\ArtworkReviewService;
use Illuminate\Http\RedirectResponse;

class ArtworkReviewController extends Controller
{
    public function store(StoreArtworkReviewRequest $request, Artwork $artwork, ArtworkReviewService $reviews): RedirectResponse
    {
        $reviews->submit($artwork, $request->user(), $request->validated(), $request);

        return back()->with('status', __('chapung.reviews.submitted'));
    }
}
