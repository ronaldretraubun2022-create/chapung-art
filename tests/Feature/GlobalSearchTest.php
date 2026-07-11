<?php

use App\Models\Artist;
use App\Models\Artwork;
use App\Models\Collection;
use App\Models\Photography;
use App\Models\Post;

function globalSearchFixtures(): void
{
    $artist = Artist::create([
        'name' => 'Mikael Wasur',
        'slug' => 'mikael-wasur',
        'photo' => 'artists/mikael.jpg',
        'bio' => 'Perupa yang meneliti motif Wasur.',
        'origin_area' => 'Merauke',
        'specialization' => 'Seni rupa Wasur',
        'is_active' => true,
    ]);

    $hiddenArtist = Artist::create([
        'name' => 'Hidden Wasur Artist',
        'slug' => 'hidden-wasur-artist',
        'is_active' => false,
    ]);

    $collection = Collection::create([
        'name' => 'Wasur Collection',
        'slug' => 'wasur-collection',
        'description' => 'Kurasi visual Wasur.',
        'cover_image' => 'collections/wasur.jpg',
        'is_active' => true,
    ]);

    Collection::create([
        'name' => 'Hidden Wasur Collection',
        'slug' => 'hidden-wasur-collection',
        'is_active' => false,
    ]);

    Artwork::create([
        'title' => 'Wasur Morning Artwork',
        'slug' => 'wasur-morning-artwork',
        'artist_id' => $artist->id,
        'collection_id' => $collection->id,
        'artist_name' => 'Mikael Wasur',
        'description' => 'Karya tentang lanskap Wasur.',
        'thumbnail' => 'artworks/wasur.jpg',
    ]);

    Photography::create([
        'title' => 'Wasur Field Notes',
        'slug' => 'wasur-field-notes',
        'artist_id' => $artist->id,
        'collection_id' => $collection->id,
        'photographer_name' => 'Mikael Wasur',
        'location' => 'Wasur',
        'thumbnail' => 'photography/wasur.jpg',
    ]);

    Post::withoutEvents(fn () => Post::create([
        'title' => 'Wasur Cultural Story',
        'slug' => 'wasur-cultural-story',
        'excerpt' => 'Catatan budaya dari Wasur.',
        'status' => 'published',
        'published_at' => now(),
        'thumbnail' => 'posts/wasur.jpg',
    ]));

    Post::withoutEvents(fn () => Post::create([
        'title' => 'Hidden Wasur Draft',
        'slug' => 'hidden-wasur-draft',
        'status' => 'draft',
    ]));

    Artwork::create([
        'title' => 'Hidden Artist Reference',
        'slug' => 'hidden-artist-reference',
        'artist_id' => $hiddenArtist->id,
        'artist_name' => 'Hidden Wasur Artist',
    ]);
}

test('live global search returns grouped public results', function () {
    globalSearchFixtures();

    $this->withSession(['locale' => 'en'])
        ->getJson(route('search.live', ['q' => 'Wasur']))
        ->assertOk()
        ->assertJsonPath('query', 'Wasur')
        ->assertJsonPath('groups.artworks.label', 'Artwork')
        ->assertJsonPath('groups.artists.label', 'Artist')
        ->assertJsonPath('groups.photographies.label', 'Photography')
        ->assertJsonPath('groups.news.label', 'News')
        ->assertJsonPath('groups.collections.label', 'Collection')
        ->assertJsonFragment(['title' => 'Wasur Morning Artwork'])
        ->assertJsonFragment(['title' => 'Mikael Wasur'])
        ->assertJsonFragment(['title' => 'Wasur Field Notes'])
        ->assertJsonFragment(['title' => 'Wasur Cultural Story'])
        ->assertJsonFragment(['title' => 'Wasur Collection'])
        ->assertJsonMissing(['title' => 'Hidden Wasur Artist'])
        ->assertJsonMissing(['title' => 'Hidden Wasur Collection'])
        ->assertJsonMissing(['title' => 'Hidden Wasur Draft']);
});

test('search page renders all content groups and keeps draft content private', function () {
    globalSearchFixtures();

    $this->withSession(['locale' => 'en'])
        ->get(route('search.index', ['q' => 'Wasur']))
        ->assertOk()
        ->assertSee('Search Chapung Art')
        ->assertSee('Wasur Morning Artwork')
        ->assertSee('Mikael Wasur')
        ->assertSee('Wasur Field Notes')
        ->assertSee('Wasur Cultural Story')
        ->assertSee('Wasur Collection')
        ->assertDontSee('Hidden Wasur Draft')
        ->assertDontSee('Hidden Wasur Collection');
});

test('public layout includes realtime global search hooks', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('data-global-search', false)
        ->assertSee('data-search-url="'.route('search.live').'"', false)
        ->assertSee('data-search-input', false);
});

test('live search normalizes empty query safely', function () {
    $this->withSession(['locale' => 'en'])
        ->getJson(route('search.live', ['q' => '   ']))
        ->assertOk()
        ->assertJsonPath('query', '')
        ->assertJsonPath('total', 0);
});
