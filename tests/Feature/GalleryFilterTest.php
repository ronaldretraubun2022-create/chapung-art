<?php

use App\Models\Artist;
use App\Models\Artwork;
use App\Models\ArtworkReview;
use App\Models\Category;
use App\Models\Collection;
use App\Models\Tag;

function galleryFilterFixtures(): array
{
    $painting = Category::create([
        'name' => 'Painting',
        'slug' => 'painting',
        'type' => 'artwork',
        'is_active' => true,
    ]);

    $sculpture = Category::create([
        'name' => 'Sculpture',
        'slug' => 'sculpture',
        'type' => 'artwork',
        'is_active' => true,
    ]);

    $artist = Artist::create([
        'name' => 'Yusuf Gebze',
        'slug' => 'yusuf-gebze',
        'is_active' => true,
    ]);

    $otherArtist = Artist::create([
        'name' => 'Rina Mahuze',
        'slug' => 'rina-mahuze',
        'is_active' => true,
    ]);

    $collection = Collection::create([
        'name' => 'Southern Lines',
        'slug' => 'southern-lines',
        'is_active' => true,
    ]);

    $otherCollection = Collection::create([
        'name' => 'Other Lines',
        'slug' => 'other-lines',
        'is_active' => true,
    ]);

    $tag = Tag::create([
        'name' => 'Fiber',
        'slug' => 'fiber',
        'type' => 'artwork',
        'is_active' => true,
    ]);

    $otherTag = Tag::create([
        'name' => 'Stone',
        'slug' => 'stone',
        'type' => 'artwork',
        'is_active' => true,
    ]);

    $target = Artwork::create([
        'title' => 'Filtered Fiber Artwork',
        'slug' => 'filtered-fiber-artwork',
        'category_id' => $painting->id,
        'artist_id' => $artist->id,
        'collection_id' => $collection->id,
        'price' => 200000,
        'is_featured' => true,
    ]);
    $target->forceFill(['created_at' => now()->subDay(), 'updated_at' => now()->subDay()])->saveQuietly();
    $target->tags()->attach($tag);

    $newest = Artwork::create([
        'title' => 'Newest Stone Artwork',
        'slug' => 'newest-stone-artwork',
        'category_id' => $sculpture->id,
        'artist_id' => $otherArtist->id,
        'collection_id' => $otherCollection->id,
        'price' => 900000,
        'is_featured' => false,
    ]);
    $newest->forceFill(['created_at' => now(), 'updated_at' => now()])->saveQuietly();
    $newest->tags()->attach($otherTag);

    $oldest = Artwork::create([
        'title' => 'Oldest Fiber Artwork',
        'slug' => 'oldest-fiber-artwork',
        'category_id' => $painting->id,
        'artist_id' => $otherArtist->id,
        'collection_id' => $collection->id,
        'price' => 100000,
        'is_featured' => false,
    ]);
    $oldest->forceFill(['created_at' => now()->subDays(3), 'updated_at' => now()->subDays(3)])->saveQuietly();
    $oldest->tags()->attach($tag);

    return compact('painting', 'artist', 'collection', 'tag');
}

test('gallery can filter by category artist collection tag and featured', function () {
    $fixtures = galleryFilterFixtures();

    $this->get(route('gallery', [
        'category' => $fixtures['painting']->id,
        'artist' => $fixtures['artist']->id,
        'collection' => $fixtures['collection']->id,
        'tag' => $fixtures['tag']->id,
        'featured' => 1,
    ]))
        ->assertOk()
        ->assertSee('Filtered Fiber Artwork')
        ->assertDontSee('Newest Stone Artwork')
        ->assertDontSee('Oldest Fiber Artwork');
});

test('gallery supports newest oldest and price sorting', function () {
    galleryFilterFixtures();

    $this->get(route('gallery', ['sort' => 'newest']))
        ->assertOk()
        ->assertSeeInOrder(['Newest Stone Artwork', 'Filtered Fiber Artwork', 'Oldest Fiber Artwork']);

    $this->get(route('gallery', ['sort' => 'oldest']))
        ->assertOk()
        ->assertSeeInOrder(['Oldest Fiber Artwork', 'Filtered Fiber Artwork', 'Newest Stone Artwork']);

    $this->get(route('gallery', ['sort' => 'price_asc']))
        ->assertOk()
        ->assertSeeInOrder(['Oldest Fiber Artwork', 'Filtered Fiber Artwork', 'Newest Stone Artwork']);

    $this->get(route('gallery', ['sort' => 'price_desc']))
        ->assertOk()
        ->assertSeeInOrder(['Newest Stone Artwork', 'Filtered Fiber Artwork', 'Oldest Fiber Artwork']);
});

test('gallery renders tag featured and sort filter controls', function () {
    galleryFilterFixtures();

    $this->withSession(['locale' => 'en'])
        ->get(route('gallery'))
        ->assertOk()
        ->assertSee('All tags')
        ->assertSee('Featured')
        ->assertSee('Newest')
        ->assertSee('Oldest')
        ->assertSee('Price low')
        ->assertSee('Price high')
        ->assertSee('Fiber')
        ->assertSee('Southern Lines');
});

test('artworks route renders premium marketplace catalog ui and safe fallback image', function () {
    galleryFilterFixtures();

    $this->withSession(['locale' => 'en'])
        ->get(route('artworks.index'))
        ->assertOk()
        ->assertSee('Papua Art Marketplace')
        ->assertSee('Categories')
        ->assertSee('Painting')
        ->assertSee('Digital Artwork')
        ->assertSee('Artwork Catalog')
        ->assertSee('In stock')
        ->assertSee('Limited edition')
        ->assertSee('Downloadable')
        ->assertSee('Customizable')
        ->assertSee('Most popular')
        ->assertSee('Filtered Fiber Artwork')
        ->assertSee('aria-label="Favorite"', false)
        ->assertSee('Add to Cart')
        ->assertSee(asset('images/og-image.jpg'), false)
        ->assertSee('data-catalog-skeleton', false);
});

test('marketplace catalog filters by type price location rating stock and commerce flags', function () {
    $category = Category::create([
        'name' => 'Digital Artwork',
        'slug' => 'digital-artwork',
        'type' => 'artwork',
        'is_active' => true,
    ]);

    $target = Artwork::create([
        'title' => 'Digital Custom Download Merauke',
        'slug' => 'digital-custom-download-merauke',
        'category_id' => $category->id,
        'price' => 350000,
        'medium' => 'Digital Custom Print',
        'material' => 'Custom archival file',
        'license' => 'digital download preview license',
        'location' => 'Merauke',
        'certificate_number' => 'CA-LTD-001',
        'stock' => 2,
        'likes' => 5,
        'views' => 40,
        'is_featured' => true,
    ]);

    ArtworkReview::create([
        'artwork_id' => $target->id,
        'reviewer_name' => 'Verified Collector',
        'reviewer_email' => 'collector@example.test',
        'rating' => 5,
        'body' => 'Kualitas karya digital sangat baik.',
        'status' => ArtworkReview::STATUS_APPROVED,
        'is_verified_purchase' => true,
    ]);

    Artwork::create([
        'title' => 'Physical Only Jayapura',
        'slug' => 'physical-only-jayapura',
        'category_id' => $category->id,
        'price' => 900000,
        'medium' => 'Canvas',
        'location' => 'Jayapura',
        'stock' => 0,
        'likes' => 0,
    ]);

    $this->get(route('artworks.index', [
        'q' => 'Custom',
        'type' => 'digital',
        'price_min' => 100000,
        'price_max' => 400000,
        'location' => 'Merauke',
        'rating' => 5,
        'stock' => 1,
        'limited' => 1,
        'downloadable' => 1,
        'customizable' => 1,
        'sort' => 'rating',
    ]))
        ->assertOk()
        ->assertSee('Digital Custom Download Merauke')
        ->assertSee('-12%')
        ->assertDontSee('Physical Only Jayapura');
});

test('legacy featured sort behaves as featured filter', function () {
    galleryFilterFixtures();

    $this->get(route('gallery', ['sort' => 'featured']))
        ->assertOk()
        ->assertSee('Filtered Fiber Artwork')
        ->assertDontSee('Newest Stone Artwork')
        ->assertDontSee('Oldest Fiber Artwork');
});
