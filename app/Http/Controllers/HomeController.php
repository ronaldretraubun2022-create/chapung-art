<?php

namespace App\Http\Controllers;

use App\Services\PageViewTracker;
use App\Support\PerformanceCache;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function __invoke(Request $request, PageViewTracker $pageViewTracker): View
    {
        $pageViewTracker->track($request);

        return view('welcome', PerformanceCache::homepagePayload());
    }
}
