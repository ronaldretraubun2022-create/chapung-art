<?php

namespace App\Http\Controllers;

use App\Models\Photography;
use Illuminate\Contracts\View\View;

class PhotographyController extends Controller
{
    public function index(): View
    {
        return view('photography', [
            'photographies' => Photography::latest()->get(),
        ]);
    }

    public function show(string $slug): View
    {
        $photo = Photography::where('slug', $slug)->firstOrFail();

        return view('photography-detail', [
            'photo' => $photo,
        ]);
    }
}
