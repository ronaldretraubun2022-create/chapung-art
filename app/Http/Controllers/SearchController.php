<?php

namespace App\Http\Controllers;

use App\Services\SearchService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function __construct(private readonly SearchService $searchService)
    {
    }

    public function index(Request $request): View
    {
        $request->validate([
            'q' => ['nullable', 'string', 'max:80'],
        ]);

        return view('search.index', [
            'payload' => $this->searchService->search($request->query('q'), 12),
        ]);
    }

    public function live(Request $request): JsonResponse
    {
        $request->validate([
            'q' => ['nullable', 'string', 'max:80'],
        ]);

        return response()->json($this->searchService->live($request->query('q')));
    }
}
