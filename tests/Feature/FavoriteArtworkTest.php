<?php

use App\Models\Artwork;
use App\Models\ArtworkFavorite;
use App\Models\User;

function favoriteArtworkFixture(array $overrides = []): Artwork
{
    return Artwork::create(array_merge([
        'title' => 'Favorite Test Artwork',
        'slug' => 'favorite-test-artwork',
        'artist_name' => 'Chapung Favorite Artist',
        'price' => 750000,
        'status' => 'available',
        'stock' => 2,
        'thumbnail' => 'artworks/favorite.jpg',
    ], $overrides));
}

test('guest cannot open favorites page or toggle favorite', function () {
    $artwork = favoriteArtworkFixture();

    $this->get(route('favorites.index'))->assertRedirect(route('login'));

    $this->postJson(route('favorites.store', $artwork->slug))
        ->assertUnauthorized();

    expect(ArtworkFavorite::count())->toBe(0);
});

test('authenticated user can add and remove favorite through json endpoint', function () {
    $user = User::factory()->create();
    $artwork = favoriteArtworkFixture();

    $this->actingAs($user)
        ->postJson(route('favorites.store', $artwork->slug))
        ->assertOk()
        ->assertJson([
            'favorited' => true,
            'count' => 1,
        ]);

    expect(ArtworkFavorite::query()->where('user_id', $user->id)->where('artwork_id', $artwork->id)->exists())->toBeTrue();

    $this->actingAs($user)
        ->deleteJson(route('favorites.destroy', $artwork->slug))
        ->assertOk()
        ->assertJson([
            'favorited' => false,
            'count' => 0,
        ]);

    expect(ArtworkFavorite::count())->toBe(0);
});

test('favorite toggle is idempotent and avoids duplicates', function () {
    $user = User::factory()->create();
    $artwork = favoriteArtworkFixture();

    $this->actingAs($user)->postJson(route('favorites.store', $artwork->slug))->assertOk();
    $this->actingAs($user)->postJson(route('favorites.store', $artwork->slug))->assertOk();

    expect(ArtworkFavorite::count())->toBe(1);
});

test('favorites page renders saved artworks and active indicator', function () {
    $user = User::factory()->create();
    $artwork = favoriteArtworkFixture();

    ArtworkFavorite::create([
        'user_id' => $user->id,
        'artwork_id' => $artwork->id,
    ]);

    $this->actingAs($user)
        ->withSession(['locale' => 'en'])
        ->get(route('favorites.index'))
        ->assertOk()
        ->assertSee('My Favorites')
        ->assertSee('Favorite Test Artwork')
        ->assertSee('aria-pressed="true"', false)
        ->assertSee('Remove Favorite');
});

test('catalog and detail expose favorite indicator for saved artwork', function () {
    $user = User::factory()->create();
    $artwork = favoriteArtworkFixture();

    ArtworkFavorite::create([
        'user_id' => $user->id,
        'artwork_id' => $artwork->id,
    ]);

    $this->actingAs($user)
        ->withSession(['locale' => 'en'])
        ->get(route('artworks.index'))
        ->assertOk()
        ->assertSee('Favorite Test Artwork')
        ->assertSee('aria-pressed="true"', false);

    $this->actingAs($user)
        ->withSession(['locale' => 'en'])
        ->get(route('artwork.show', $artwork->slug))
        ->assertOk()
        ->assertSee('Remove Favorite');
});
