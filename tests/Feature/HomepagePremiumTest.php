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

    $t = fn (string $key, array $replace = []): string => trans($key, $replace, 'en');

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

    $this->withSession(['locale' => 'en'])
        ->get(route('home'))
        ->assertOk()
        ->assertSeeText($t('chapung.home.hero_marketplace'))
        ->assertSeeText('Langit Wasur')
        ->assertSee(route('artwork.show', 'langit-wasur'), false)
        ->assertSeeText($t('chapung.home.latest_artworks'))
        ->assertSeeText($t('chapung.home.featured_artist'))
        ->assertSeeText($t('chapung.home.selected_artists'))
        ->assertSeeText('Agnes Mahuze')
        ->assertSee(route('artists.show', 'agnes-mahuze'), false)
        ->assertSeeText($t('chapung.home.collections'))
        ->assertSeeText($t('chapung.home.artwork_collections'))
        ->assertSeeText('Rawa Selatan')
        ->assertSee(route('collections.show', 'rawa-selatan'), false)
        ->assertSeeText($t('chapung.home.photography'))
        ->assertSeeText($t('chapung.home.photography_digital'))
        ->assertSeeText('Pagi di Maro')
        ->assertSee(route('photography.index'), false)
        ->assertSeeText($t('chapung.home.news'))
        ->assertSeeText($t('chapung.home.supporting_updates'))
        ->assertSeeText('Cerita Visual Merauke')
        ->assertSee(route('news.index'), false)
        ->assertSeeText($t('chapung.home.explore_gallery'))
        ->assertSee('<meta property="og:image" content="'.asset('storage/artworks/langit.jpg').'">', false)
        ->assertSee('<script type="application/ld+json">', false)
        ->assertSee('"@type":"WebSite"', false)
        ->assertSee('loading="lazy"', false)
        ->assertSee(route('artworks.index'), false)
        ->assertSee(route('contact'), false);
});
