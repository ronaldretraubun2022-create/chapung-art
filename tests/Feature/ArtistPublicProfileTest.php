<?php

use App\Models\Artist;
use App\Models\Artwork;
use App\Models\ArtworkReview;
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

    $artwork = Artwork::create([
        'title' => 'Jejak Sungai Maro',
        'slug' => 'jejak-sungai-maro',
        'artist_id' => $artist->id,
        'collection_id' => $collection->id,
        'thumbnail' => 'artworks/jejak.jpg',
        'price' => 1500000,
        'stock' => 3,
        'views' => 80,
        'likes' => 12,
        'is_featured' => true,
    ]);

    Artwork::create([
        'title' => 'Arsip Rawa Terjual',
        'slug' => 'arsip-rawa-terjual',
        'artist_id' => $artist->id,
        'collection_id' => $collection->id,
        'thumbnail' => 'artworks/arsip.jpg',
        'price' => 950000,
        'status' => 'sold',
        'stock' => 0,
    ]);

    ArtworkReview::create([
        'artwork_id' => $artwork->id,
        'reviewer_name' => 'Verified Collector',
        'reviewer_email' => 'collector@example.test',
        'rating' => 5,
        'title' => 'Karya yang kuat',
        'body' => 'Karya ini hadir dengan kualitas visual yang kuat.',
        'status' => ArtworkReview::STATUS_APPROVED,
        'is_verified_purchase' => true,
    ]);

    $this->withSession(['locale' => 'en'])
        ->get(route('artists.show', $artist->slug))
        ->assertOk()
        ->assertSee('Artist Storefront')
        ->assertSee('Verified Chapung Art profile')
        ->assertSee('Maria Ndiken')
        ->assertSee('Perupa Merauke yang mendokumentasikan cerita visual Papua Selatan.', false)
        ->assertSee('Available artworks')
        ->assertSee('Collector rating')
        ->assertSee('Reviews & Collector Signals')
        ->assertSee('Karya ini hadir dengan kualitas visual yang kuat.')
        ->assertSee('Contact Artist')
        ->assertSee('Favorite Store')
        ->assertSee('Artist Bio')
        ->assertSee('Store Info')
        ->assertSee('Jejak Sungai Maro')
        ->assertSee('Available Artworks')
        ->assertSee('Sold Artworks')
        ->assertSee('Arsip Rawa Terjual')
        ->assertSee('Tanah Selatan')
        ->assertSee('artworks/jejak.jpg')
        ->assertSee('<meta property="og:image" content="'.asset('storage/artworks/jejak.jpg').'">', false)
        ->assertSee('<meta property="og:type" content="website">', false)
        ->assertSee('<script type="application/ld+json">', false);
});

test('artist storefront renders empty review state safely', function () {
    $artist = publicProfileArtist([
        'name' => 'Empty Review Artist',
        'slug' => 'empty-review-artist',
    ]);

    $this->withSession(['locale' => 'en'])
        ->get(route('artists.show', $artist->slug))
        ->assertOk()
        ->assertSee('Reviews & Collector Signals')
        ->assertSee('No written reviews or collector signals are available yet.')
        ->assertSee('Artwork Catalog');
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

    $this->withSession(['locale' => 'en'])
        ->get(route('artists.show', ['slug' => $artist->slug, 'artworks_page' => 2]))
        ->assertOk()
        ->assertSee('Karya Artist 1')
        ->assertSee('Artwork');
});

test('artist index renders marketplace profile cards with safe fallback data', function () {
    $artist = publicProfileArtist([
        'name' => 'Card Artist',
        'slug' => 'card-artist',
        'photo' => null,
        'specialization' => null,
        'origin_area' => 'Merauke',
    ]);

    Artwork::create([
        'title' => 'Card Artist Work',
        'slug' => 'card-artist-work',
        'artist_id' => $artist->id,
    ]);

    $this->withSession(['locale' => 'en'])
        ->get(route('artists.index'))
        ->assertOk()
        ->assertSee('Card Artist')
        ->assertSee('Merauke')
        ->assertSee(route('artists.show', $artist->slug), false)
        ->assertSee('1 artworks / 0 photos');
});

test('inactive artist profile is not public', function () {
    $artist = publicProfileArtist([
        'name' => 'Inactive Artist',
        'slug' => 'inactive-artist',
        'is_active' => false,
    ]);

    $this->get(route('artists.show', $artist->slug))->assertNotFound();
});
