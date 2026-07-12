<?php

use App\Models\Artist;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

function artistForPhotoPlaceholder(string $name, ?string $photo, array $overrides = []): Artist
{
    return Artist::create(array_merge([
        'name' => $name,
        'slug' => str($name)->slug()->toString(),
        'photo' => $photo,
        'bio' => '<p>Perupa dari Papua Selatan.</p>',
        'origin_area' => 'Merauke',
        'specialization' => 'Seni rupa kontemporer',
        'is_featured' => true,
        'is_active' => true,
    ], $overrides));
}

test('artist profile cards render valid photos and blank placeholders for empty or missing photos', function () {
    Storage::fake('public');
    Storage::disk('public')->put('artists/valid-profile.jpg', 'valid image content');

    artistForPhotoPlaceholder('Valid Profile Artist', 'artists/valid-profile.jpg');
    artistForPhotoPlaceholder('Empty Profile Artist', null);
    artistForPhotoPlaceholder('Missing Profile Artist', 'artists/missing-profile.jpg');

    $response = $this->get(route('artists.index'))->assertOk();
    $content = $response->getContent();

    $response
        ->assertSee('data-artist-photo-state="image"', false)
        ->assertSee('data-artist-photo-state="placeholder"', false)
        ->assertSee('src="'.asset('storage/artists/valid-profile.jpg').'"', false)
        ->assertSee('alt="Valid Profile Artist"', false)
        ->assertDontSee('storage/artists/missing-profile.jpg', false)
        ->assertDontSee('images/artwork-placeholder.svg', false);

    expect(substr_count($content, 'data-artist-photo-state="image"'))->toBe(1)
        ->and(substr_count($content, 'data-artist-photo-state="placeholder"'))->toBe(2)
        ->and($content)->toMatch('/data-artist-photo-state="placeholder"[^>]*>\s*<\/div>/');
});

test('homepage artist story cards use blank placeholders when artist photos are absent or missing', function () {
    Cache::flush();
    Storage::fake('public');
    Storage::disk('public')->put('artists/valid-story.jpg', 'valid image content');

    artistForPhotoPlaceholder('Valid Story Artist', 'artists/valid-story.jpg');
    artistForPhotoPlaceholder('Empty Story Artist', null);
    artistForPhotoPlaceholder('Missing Story Artist', 'artists/missing-story.jpg');

    $response = $this->get(route('home'))->assertOk();
    $content = $response->getContent();

    $response
        ->assertSee('src="'.asset('storage/artists/valid-story.jpg').'"', false)
        ->assertSee('alt="Valid Story Artist"', false)
        ->assertDontSee('storage/artists/missing-story.jpg', false)
        ->assertDontSee('images/artwork-placeholder.svg', false);

    expect(substr_count($content, 'data-artist-photo-state="image"'))->toBeGreaterThanOrEqual(1)
        ->and(substr_count($content, 'data-artist-photo-state="placeholder"'))->toBeGreaterThanOrEqual(2)
        ->and($content)->toMatch('/data-artist-photo-state="placeholder"[^>]*>\s*<\/div>/');
});
