<?php

use App\Models\Artist;
use App\Models\Artwork;
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

    $this->get(route('gallery'))
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

test('legacy featured sort behaves as featured filter', function () {
    galleryFilterFixtures();

    $this->get(route('gallery', ['sort' => 'featured']))
        ->assertOk()
        ->assertSee('Filtered Fiber Artwork')
        ->assertDontSee('Newest Stone Artwork')
        ->assertDontSee('Oldest Fiber Artwork');
});
