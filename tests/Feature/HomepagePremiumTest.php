<?php

use App\Models\Artist;
use App\Models\Artwork;
use App\Models\Collection;
use App\Models\HomepageSection;
use App\Models\Photography;
use App\Models\Post;
use App\Support\PerformanceCache;
use Illuminate\Support\Facades\Cache;

test('homepage can be rendered without serialized eloquent collection errors', function () {
    Cache::put(PerformanceCache::HOMEPAGE_PAYLOAD, [
        'homepageSections' => HomepageSection::query()->get(),
    ], 600);

    $this->get('/')->assertOk();
});

test('premium homepage renders hero featured content seo and lazy images', function () {
    Cache::flush();

    HomepageSection::updateOrCreate(
        ['section_key' => 'hero'],
        [
            'title' => 'CHAPUNG ART',
            'subtitle' => 'Galeri Seni Rupa Papua Selatan',
            'content' => 'Ruang digital untuk karya seni rupa dan cerita visual dari Papua Selatan.',
            'image' => 'homepage/hero.jpg',
            'sort_order' => 1,
            'is_active' => true,
        ],
    );

    $artist = Artist::create([
        'name' => 'Agnes Mahuze',
        'slug' => 'agnes-mahuze',
        'photo' => 'artists/agnes.jpg',
        'origin_area' => 'Merauke',
        'specialization' => 'Seni rupa kontemporer',
        'is_featured' => true,
        'is_active' => true,
    ]);

    $collection = Collection::create([
        'name' => 'Rawa Selatan',
        'slug' => 'rawa-selatan',
        'description' => 'Kurasi karya visual Papua Selatan.',
        'cover_image' => 'collections/rawa.jpg',
        'is_featured' => true,
        'is_active' => true,
    ]);

    Artwork::create([
        'title' => 'Langit Wasur',
        'slug' => 'langit-wasur',
        'artist_id' => $artist->id,
        'collection_id' => $collection->id,
        'thumbnail' => 'artworks/langit.jpg',
        'price' => 1200000,
        'is_featured' => true,
    ]);

    Photography::create([
        'title' => 'Pagi di Maro',
        'slug' => 'pagi-di-maro',
        'artist_id' => $artist->id,
        'collection_id' => $collection->id,
        'photographer_name' => 'Agnes Mahuze',
        'location' => 'Merauke',
        'thumbnail' => 'photography/maro.jpg',
        'is_featured' => true,
    ]);

    Post::withoutEvents(fn () => Post::create([
        'title' => 'Cerita Visual Merauke',
        'slug' => 'cerita-visual-merauke',
        'excerpt' => 'Catatan dari ruang kreatif Papua Selatan.',
        'status' => 'published',
        'published_at' => now(),
        'thumbnail' => 'posts/cerita.jpg',
    ]));

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('CHAPUNG ART')
        ->assertSee('Featured Artwork')
        ->assertSee('Langit Wasur')
        ->assertSee('Featured Artist')
        ->assertSee('Agnes Mahuze')
        ->assertSee('Collections')
        ->assertSee('Rawa Selatan')
        ->assertSee('Photography')
        ->assertSee('Pagi di Maro')
        ->assertSee('News')
        ->assertSee('Cerita Visual Merauke')
        ->assertSee('Explore Gallery')
        ->assertSee('<meta property="og:image" content="'.asset('storage/homepage/hero.jpg').'">', false)
        ->assertSee('<script type="application/ld+json">', false)
        ->assertSee('loading="lazy"', false)
        ->assertSee(route('gallery'), false)
        ->assertSee(route('contact'), false);
});
