<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Contracts\View\View;

class PostController extends Controller
{
    public function index(): View
    {
        return view('media', [
            'posts' => Post::where('status', 'published')->latest()->get(),
        ]);
    }

    public function show(string $slug): View
    {
        $post = Post::query()
            ->where('status', 'published')
            ->where('slug', $slug)
            ->firstOrFail();

        return view('media-detail', [
            'post' => $post,
        ]);
    }
}
