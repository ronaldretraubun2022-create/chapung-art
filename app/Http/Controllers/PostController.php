<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Post;
use App\Services\PageViewTracker;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function index(Request $request): View
    {
        return view('news.index', [
            'posts' => Post::with(['category', 'author', 'tags'])
                ->where('status', 'published')
                ->when($request->filled('q'), function ($query) use ($request): void {
                    $keyword = $request->string('q')->toString();

                    $query->where(function ($query) use ($keyword): void {
                        $query->where('title', 'like', "%{$keyword}%")
                            ->orWhere('excerpt', 'like', "%{$keyword}%")
                            ->orWhere('content', 'like', "%{$keyword}%")
                            ->orWhere('author_name', 'like', "%{$keyword}%");
                    });
                })
                ->when($request->filled('category'), fn ($query) => $query->where('category_id', $request->integer('category')))
                ->when($request->input('sort') === 'popular', fn ($query) => $query->orderByDesc('views'))
                ->latest('published_at')
                ->paginate(9)
                ->withQueryString(),
            'categories' => Category::query()->where('type', 'post')->where('is_active', true)->orderBy('name')->get(),
            'filters' => $request->only(['q', 'category', 'sort']),
        ]);
    }

    public function show(string $slug, Request $request, PageViewTracker $pageViewTracker): View
    {
        $post = Post::with(['category', 'author', 'tags', 'mediaItems'])
            ->where('status', 'published')
            ->where('slug', $slug)
            ->firstOrFail();

        $pageViewTracker->track($request, $post);

        return view('news.show', [
            'post' => $post,
            'relatedPosts' => Post::with('category')
                ->where('status', 'published')
                ->whereKeyNot($post->id)
                ->when($post->category_id, fn ($query) => $query->where('category_id', $post->category_id))
                ->latest('published_at')
                ->take(3)
                ->get(),
        ]);
    }
}
