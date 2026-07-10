<?php

use App\Models\Artist;
use App\Models\Artwork;
use App\Models\Collection;

function publicProfileArtist(array $overrides = []): Artist
{
    return Artist::create(array_merge([
        'name' => 'Maria Ndiken',
        'slug' => 'maria-ndiken',
        'photo' => 'artists/maria.jpg',
        'bio' => '<p>Perupa Merauke yang mendokumentasikan cerita visual Papua Selatan.</p>',
        'origin_area' => 'Merauke',
        'city' => 'Merauke',
        'province' => 'Papua Selatan',
        'country' => 'Indonesia',
        'specialization' => 'Seni rupa kontemporer',
        'education' => 'Komunitas seni lokal',
        'website' => 'https://example.test/maria',
        'is_active' => true,
    ], $overrides));
}

test('artist public profile shows cover photo biography artworks collections and seo metadata', function () {
    $artist = publicProfileArtist();
    $collection = Collection::create([
        'name' => 'Tanah Selatan',
        'slug' => 'tanah-selatan',
        'description' => 'Kurasi karya dari Papua Selatan.',
        'cover_image' => 'collections/tanah.jpg',
        'is_active' => true,
    ]);

    Artwork::create([
        'title' => 'Jejak Sungai Maro',
        'slug' => 'jejak-sungai-maro',
        'artist_id' => $artist->id,
        'collection_id' => $collection->id,
        'thumbnail' => 'artworks/jejak.jpg',
        'price' => 1500000,
        'is_featured' => true,
    ]);

    $this->get(route('artists.show', $artist->slug))
        ->assertOk()
        ->assertSee('Maria Ndiken')
        ->assertSee('Perupa Merauke yang mendokumentasikan cerita visual Papua Selatan.', false)
        ->assertSee('Jejak Sungai Maro')
        ->assertSee('Tanah Selatan')
        ->assertSee('artworks/jejak.jpg')
        ->assertSee('<meta property="og:image" content="'.asset('storage/artworks/jejak.jpg').'">', false)
        ->assertSee('<meta property="og:type" content="website">', false)
        ->assertSee('<script type="application/ld+json">', false);
});

test('artist profile paginates artworks safely', function () {
    $artist = publicProfileArtist([
        'name' => 'Yohan Gebze',
        'slug' => 'yohan-gebze',
    ]);

    foreach (range(1, 9) as $index) {
        Artwork::create([
            'title' => 'Karya Artist '.$index,
            'slug' => 'karya-artist-'.$index,
            'artist_id' => $artist->id,
            'created_at' => now()->subMinutes(10 - $index),
        ]);
    }

    $this->get(route('artists.show', ['slug' => $artist->slug, 'artworks_page' => 2]))
        ->assertOk()
        ->assertSee('Karya Artist 1')
        ->assertSee('Artwork');
});

test('inactive artist profile is not public', function () {
    $artist = publicProfileArtist([
        'name' => 'Inactive Artist',
        'slug' => 'inactive-artist',
        'is_active' => false,
    ]);

    $this->get(route('artists.show', $artist->slug))->assertNotFound();
});
