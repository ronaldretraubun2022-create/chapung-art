<?php

use App\Models\Artist;
use App\Models\Artwork;
use App\Models\Collection;
use App\Models\HomepageSection;
use App\Models\Photography;
use App\Models\Post;
use App\Models\SiteSetting;
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
        ->assertSee('<picture class="block h-full w-full">', false)
        ->assertSee('media="(max-width: 767px)"', false)
        ->assertSee('images/hero/chapung-art-hero-background-mobile.webp', false)
        ->assertSee('images/hero/chapung-art-hero-background.webp', false)
        ->assertSee('width="1080" height="1350"', false)
        ->assertSee('width="1920" height="1000"', false)
        ->assertSee('alt=""', false)
        ->assertSee('aria-hidden="true"', false)
        ->assertSee('fetchpriority="high"', false)
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
        ->assertSee('"@type":"Organization"', false)
        ->assertSee('"@type":"WebSite"', false)
        ->assertSee('"@type":"BreadcrumbList"', false)
        ->assertSee('"@type":"SearchAction"', false)
        ->assertSee('loading="lazy"', false)
        ->assertSee('sizes="', false)
        ->assertSee(route('artworks.index'), false)
        ->assertSee(route('contact'), false);
});

test('homepage html renders the normalized Chapung Art contact address', function () {
    Cache::flush();

    SiteSetting::updateOrCreate(
        ['key' => 'address'],
        [
            'value' => (string) config('chapung.address'),
            'type' => 'textarea',
            'group' => 'contact',
        ],
    );

    $response = $this->get(route('home'));

    $response
        ->assertOk()
        ->assertSee('JL. SESATE NO. 242, RT 007/RW 002, BAMBU PEMALI', false)
        ->assertSee('KABUPATEN MERAUKE, PAPUA SELATAN 99616', false)
        ->assertDontSee('MERAUKE MERAUKE', false)
        ->assertDontSee('KAB. 99616', false)
        ->assertDontSee('JL SESATE NO 242 RT.007 RW.002 BAMBU PEMALI', false);

    expect($response->getContent())
        ->toContain('<footer')
        ->toContain('JL. SESATE NO. 242, RT 007/RW 002, BAMBU PEMALI')
        ->toContain('KABUPATEN MERAUKE, PAPUA SELATAN 99616')
        ->not->toContain('MERAUKE MERAUKE')
        ->not->toContain('KAB. 99616');
});
