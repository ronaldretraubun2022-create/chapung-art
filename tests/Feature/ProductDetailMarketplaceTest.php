<?php

use App\Models\Artist;
use App\Models\Artwork;
use App\Models\Category;
use App\Models\Collection;
use App\Models\Tag;

function productDetailArtwork(array $overrides = []): Artwork
{
    $artist = Artist::create([
        'name' => 'Marta Mahuze',
        'slug' => 'marta-mahuze',
        'photo' => 'artists/marta.jpg',
        'bio' => '<p>Seniman visual dari Merauke dengan fokus material lokal.</p>',
        'city' => 'Merauke',
        'province' => 'Papua Selatan',
        'country' => 'Indonesia',
        'specialization' => 'Lukisan dan media campuran',
        'is_active' => true,
    ]);

    $category = Category::create([
        'name' => 'Painting',
        'slug' => 'painting-detail',
        'type' => 'artwork',
        'is_active' => true,
    ]);

    $collection = Collection::create([
        'name' => 'Merauke Collector Edit',
        'slug' => 'merauke-collector-edit',
        'is_active' => true,
    ]);

    $tag = Tag::create([
        'name' => 'Limited',
        'slug' => 'limited-detail',
        'type' => 'artwork',
        'is_active' => true,
    ]);

    $artwork = Artwork::create(array_merge([
        'title' => 'Marketplace Detail Artwork',
        'slug' => 'marketplace-detail-artwork',
        'artist_id' => $artist->id,
        'category_id' => $category->id,
        'collection_id' => $collection->id,
        'description' => '<p>Karya premium untuk kolektor Chapung Art.</p>',
        'thumbnail' => 'artworks/detail-main.jpg',
        'price' => 1500000,
        'status' => 'available',
        'medium' => 'Acrylic on canvas',
        'material' => 'Canvas',
        'technique' => 'Layered brush',
        'frame' => 'Black wooden frame',
        'size' => '80 x 100 cm',
        'width' => 80,
        'height' => 100,
        'condition' => 'New',
        'location' => 'Merauke',
        'certificate_number' => 'CA-ART-001',
        'license' => 'Collector display license',
        'stock' => 3,
        'views' => 88,
        'likes' => 12,
        'is_featured' => true,
    ], $overrides));

    $artwork->tags()->attach($tag);

    $artwork->mediaItems()->create([
        'collection_name' => 'gallery',
        'file_path' => 'artworks/detail-side.jpg',
        'file_type' => 'image',
        'title' => 'Side detail',
        'alt_text' => 'Side view',
        'sort_order' => 1,
    ]);

    Artwork::create([
        'title' => 'Related Detail Artwork',
        'slug' => 'related-detail-artwork',
        'artist_id' => $artist->id,
        'category_id' => $category->id,
        'price' => 900000,
        'status' => 'available',
        'stock' => 1,
    ]);

    return $artwork->fresh(['artist', 'category', 'collection', 'tags', 'mediaItems']);
}

test('artwork detail renders complete marketplace product page', function () {
    $artwork = productDetailArtwork();

    $this->withSession(['locale' => 'en'])
        ->get(route('artwork.show', $artwork->slug))
        ->assertOk()
        ->assertSee('Back to catalog')
        ->assertSee('Marketplace Detail Artwork')
        ->assertSee('Artwork by')
        ->assertSee('Marta Mahuze')
        ->assertSee('Rp 1.500.000')
        ->assertSee('-12%')
        ->assertSee('Artwork variations')
        ->assertSee('Acrylic on canvas')
        ->assertSee('Black wooden frame')
        ->assertSee('Product description')
        ->assertSee('Specifications')
        ->assertSee('CA-ART-001')
        ->assertSee('Artist profile')
        ->assertSee('View artist')
        ->assertSee('Buy now')
        ->assertSee('Add to Cart')
        ->assertSee('Related Artwork')
        ->assertSee('Related Detail Artwork')
        ->assertSee(asset('storage/artworks/detail-main.jpg'), false)
        ->assertSee(asset('storage/artworks/detail-side.jpg'), false)
        ->assertDontSee('storage/app/private', false);
});

test('artwork detail uses safe fallback image when preview is empty', function () {
    $artwork = productDetailArtwork([
        'title' => 'Fallback Detail Artwork',
        'slug' => 'fallback-detail-artwork',
        'thumbnail' => null,
        'og_image' => null,
    ]);

    $this->withSession(['locale' => 'en'])
        ->get(route('artwork.show', $artwork->slug))
        ->assertOk()
        ->assertSee('Fallback Detail Artwork')
        ->assertSee(asset('images/og-image.jpg'), false);
});
