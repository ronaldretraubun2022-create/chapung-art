<?php

use App\Models\Collection as ArtCollection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

function collectionForCoverPlaceholder(string $name, ?string $coverImage = null, ?string $bannerImage = null): ArtCollection
{
    return ArtCollection::create([
        'name' => $name,
        'slug' => str($name)->slug()->toString(),
        'description' => 'Koleksi karya Chapung Art.',
        'cover_image' => $coverImage,
        'banner_image' => $bannerImage,
        'is_featured' => true,
        'is_active' => true,
    ]);
}

test('homepage collection cards render valid covers and blank placeholders for empty or missing images', function () {
    Cache::flush();
    Storage::fake('public');
    Storage::disk('public')->put('collections/valid-cover.jpg', 'valid image content');

    collectionForCoverPlaceholder('Valid Cover Collection', 'collections/valid-cover.jpg');
    collectionForCoverPlaceholder('Empty Cover Collection');
    collectionForCoverPlaceholder('Missing Cover Collection', 'collections/missing-cover.jpg');

    $response = $this->get(route('home'))->assertOk();
    $content = $response->getContent();
    preg_match('/<section id="collections".*?<\/section>/s', $content, $matches);
    $collectionSection = $matches[0] ?? '';

    $response
        ->assertSee('data-collection-cover-state="image"', false)
        ->assertSee('data-collection-cover-state="placeholder"', false)
        ->assertSee('src="'.asset('storage/collections/valid-cover.jpg').'"', false)
        ->assertSee('alt="Valid Cover Collection"', false)
        ->assertDontSee('storage/collections/missing-cover.jpg', false);

    expect(substr_count($content, 'data-collection-cover-state="image"'))->toBe(1)
        ->and(substr_count($content, 'data-collection-cover-state="placeholder"'))->toBe(2)
        ->and($content)->toMatch('/data-collection-cover-state="placeholder"[^>]*>\s*<\/div>/')
        ->and($collectionSection)->not->toContain('src="'.asset('images/logo.svg').'"')
        ->and($collectionSection)->not->toContain('src="'.asset('images/brand/chapung-art-logo.svg').'"')
        ->and($collectionSection)->not->toContain('src="'.asset('images/brand/chapung-art-logo-dark.svg').'"')
        ->and($collectionSection)->not->toContain('src="'.asset('images/og-image.jpg').'"')
        ->and($collectionSection)->not->toContain('src="'.asset('images/artwork-placeholder.svg').'"');
});
