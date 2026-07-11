<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Services\PageViewTracker;
use App\Support\PerformanceCache;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function index(Request $request): View
    {
        $indexRouteName = $request->routeIs('media.*') ? 'media.index' : 'news.index';
        $showRouteName = $request->routeIs('media.*') ? 'media.show' : 'news.show';

        return view('news.index', [
            'posts' => Post::query()
                ->select(['id', 'title', 'slug', 'category_id', 'author_id', 'excerpt', 'featured_image', 'thumbnail', 'author_name', 'content', 'status', 'published_at', 'reading_time', 'views', 'created_at'])
                ->with(['category:id,name', 'author:id,name', 'tags:id,name,slug'])
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
            'categories' => PerformanceCache::activeCategories('post'),
            'filters' => $request->only(['q', 'category', 'sort']),
            'indexRouteName' => $indexRouteName,
            'showRouteName' => $showRouteName,
        ]);
    }

    public function show(string $slug, Request $request, PageViewTracker $pageViewTracker): View
    {
        $post = Post::with(['category', 'author', 'tags', 'mediaItems'])
            ->where('status', 'published')
            ->where('slug', $slug)
            ->firstOrFail();

        $pageViewTracker->track($request, $post);

        $showRouteName = $request->routeIs('media.*') ? 'media.show' : 'news.show';
        $authorName = $post->author?->name ?: $post->author_name ?: __('chapung.pages.news.author_fallback');

        return view('news.show', [
            'post' => $post,
            'relatedPosts' => Post::with(['category:id,name', 'author:id,name'])
                ->where('status', 'published')
                ->whereKeyNot($post->id)
                ->when($post->category_id, fn ($query) => $query->where('category_id', $post->category_id))
                ->latest('published_at')
                ->take(3)
                ->get(),
            'showRouteName' => $showRouteName,
            'articleSchema' => [
                '@context' => 'https://schema.org',
                '@type' => 'Article',
                'headline' => $post->title,
                'description' => str(strip_tags($post->excerpt ?: $post->content ?: __('chapung.pages.detail.news_description')))->limit(160)->toString(),
                'image' => $post->display_image ? asset('storage/'.$post->display_image) : asset('images/og-image.jpg'),
                'datePublished' => optional($post->published_at ?: $post->created_at)->toIso8601String(),
                'author' => [
                    '@type' => 'Person',
                    'name' => $authorName,
                ],
                'publisher' => [
                    '@type' => 'Organization',
                    'name' => site_setting('site_name', 'Chapung Art'),
                ],
                'mainEntityOfPage' => route($showRouteName, $post->slug),
            ],
        ]);
    }
}
