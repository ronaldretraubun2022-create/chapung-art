<?php

namespace App\Http\Controllers;

use App\Models\Artwork;
use App\Models\Photography;
use App\Models\Post;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $baseUrl = rtrim(config('app.url'), '/');

        $urls = collect([
            $baseUrl . '/',
            $baseUrl . '/gallery',
            $baseUrl . '/photography',
            $baseUrl . '/media',
        ]);

        $artworkUrls = Artwork::query()
            ->whereNotNull('slug')
            ->pluck('slug')
            ->map(fn (string $slug): string => $baseUrl . route('artwork.show', $slug, false));

        $photographyUrls = Photography::query()
            ->whereNotNull('slug')
            ->pluck('slug')
            ->map(fn (string $slug): string => $baseUrl . route('photography.show', $slug, false));

        $postUrls = Post::query()
            ->where('status', 'published')
            ->whereNotNull('slug')
            ->pluck('slug')
            ->map(fn (string $slug): string => $baseUrl . route('media.show', $slug, false));

        $xmlUrls = $urls
            ->merge($artworkUrls)
            ->merge($photographyUrls)
            ->merge($postUrls)
            ->unique()
            ->map(fn (string $url): string => '    <url><loc>' . e($url) . '</loc></url>')
            ->implode("\n");

        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n"
            . "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n"
            . $xmlUrls
            . "\n</urlset>\n";

        return response($xml, 200)->header('Content-Type', 'application/xml');
    }
}
