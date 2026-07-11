<?php

use App\Models\Category;
use App\Models\Post;
use Illuminate\Support\Facades\Cache;

function editorialCategory(): Category
{
    return Category::create([
        'name' => 'Pameran',
        'slug' => 'pameran',
        'type' => 'post',
        'is_active' => true,
    ]);
}

function editorialPost(array $overrides = []): Post
{
    return Post::withoutEvents(fn () => Post::create(array_merge([
        'title' => 'Cerita Seni Maro',
        'slug' => 'cerita-seni-maro',
        'excerpt' => 'Catatan komunitas seni dan budaya dari Merauke.',
        'content' => '<p>Karya dan perupa tetap menjadi fokus utama Chapung Art.</p>',
        'author_name' => 'Redaksi Chapung',
        'status' => 'published',
        'published_at' => now(),
        'reading_time' => 4,
        'thumbnail' => 'posts/maro-thumb.jpg',
        'featured_image' => 'posts/maro.jpg',
        'category_id' => editorialCategory()->id,
    ], $overrides)));
}

test('news index renders editorial cards with filters and localized copy', function () {
    Cache::flush();
    $post = editorialPost();

    $this->withSession(['locale' => 'en'])
        ->get(route('news.index'))
        ->assertOk()
        ->assertSeeText('Newsroom')
        ->assertSeeText('News and Culture')
        ->assertSeeText('All categories')
        ->assertSeeText('Popular')
        ->assertSeeText('Redaksi Chapung')
        ->assertSeeText('4 min read')
        ->assertSeeText('Read')
        ->assertSee(route('news.show', $post->slug), false)
        ->assertSee('loading="lazy"', false);
});

test('media index keeps cards within media route namespace', function () {
    Cache::flush();
    $post = editorialPost(['slug' => 'media-komunitas-maro']);

    $this->withSession(['locale' => 'en'])
        ->get(route('media.index'))
        ->assertOk()
        ->assertSee(route('media.show', $post->slug), false)
        ->assertDontSee(route('news.show', $post->slug), false);
});

test('news detail renders article seo eager image and lazy supporting youtube embed', function () {
    $post = editorialPost();

    $post->mediaItems()->create([
        'collection_name' => 'supporting-media',
        'file_path' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        'file_type' => 'video',
        'title' => 'Dokumentasi Pameran',
        'sort_order' => 1,
    ]);

    $this->withSession(['locale' => 'en'])
        ->get(route('news.show', $post->slug))
        ->assertOk()
        ->assertSeeText('Supporting Media')
        ->assertSee('<script type="application/ld+json">', false)
        ->assertSee('"@type":"Article"', false)
        ->assertSee('fetchpriority="high"', false)
        ->assertSee('loading="eager"', false)
        ->assertSee('https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ', false)
        ->assertSee('loading="lazy"', false)
        ->assertDontSee('youtube.com/watch?v=dQw4w9WgXcQ', false)
        ->assertDontSee('storage/app/private', false);
});
