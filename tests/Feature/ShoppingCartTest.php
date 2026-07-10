<?php

use App\Models\Artwork;
use App\Models\User;
use App\Services\CartService;

function cartArtwork(array $overrides = []): Artwork
{
    return Artwork::create(array_merge([
        'title' => 'Cart Artwork '.fake()->unique()->numberBetween(1000, 9999),
        'slug' => 'cart-artwork-'.fake()->unique()->numberBetween(1000, 9999),
        'price' => 250000,
        'stock' => 5,
        'status' => 'available',
    ], $overrides));
}

test('guest can add artwork to session cart', function () {
    $artwork = cartArtwork();

    $this->post(route('cart.store'), [
        'artwork_id' => $artwork->id,
        'quantity' => 2,
    ])->assertRedirect();

    $summary = app(CartService::class)->summary();

    expect($summary['count'])->toBe(2)
        ->and($summary['subtotal'])->toBe(500000.0)
        ->and($summary['total'])->toBe(500000.0);
});

test('cart quantity can be updated', function () {
    $artwork = cartArtwork(['stock' => 6, 'price' => 100000]);

    $this->post(route('cart.store'), [
        'artwork_id' => $artwork->id,
        'quantity' => 1,
    ]);

    $this->patch(route('cart.update', $artwork->id), [
        'quantity' => 4,
    ])->assertRedirect();

    $summary = app(CartService::class)->summary();

    expect($summary['count'])->toBe(4)
        ->and($summary['subtotal'])->toBe(400000.0);
});

test('cart item can be removed', function () {
    $artwork = cartArtwork();

    $this->post(route('cart.store'), [
        'artwork_id' => $artwork->id,
        'quantity' => 1,
    ]);

    $this->delete(route('cart.destroy', $artwork->id))->assertRedirect();

    expect(app(CartService::class)->summary()['count'])->toBe(0);
});

test('cart rejects quantity above stock', function () {
    $artwork = cartArtwork(['stock' => 1]);

    $this->from(route('artwork.show', $artwork->slug))
        ->post(route('cart.store'), [
            'artwork_id' => $artwork->id,
            'quantity' => 2,
        ])
        ->assertRedirect(route('artwork.show', $artwork->slug))
        ->assertSessionHasErrors('quantity');

    expect(app(CartService::class)->summary()['count'])->toBe(0);
});

test('cart page shows badge total and subtotal', function () {
    $artwork = cartArtwork(['price' => 125000]);

    $this->post(route('cart.store'), [
        'artwork_id' => $artwork->id,
        'quantity' => 2,
    ]);

    $this->get(route('cart.index'))
        ->assertOk()
        ->assertSee('Cart')
        ->assertSee('Rp 250.000')
        ->assertSee('Items')
        ->assertSee('(2)', false);
});

test('guest cart merges with user session cart after login', function () {
    $user = User::factory()->create();
    $artwork = cartArtwork(['stock' => 5]);

    $this->withSession([
        'cart.users.'.$user->id.'.items' => [
            (string) $artwork->id => ['quantity' => 1],
        ],
    ])->post(route('cart.store'), [
        'artwork_id' => $artwork->id,
        'quantity' => 2,
    ]);

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->assertRedirect(route('dashboard', absolute: false));

    expect(app(CartService::class)->summary()['count'])->toBe(3)
        ->and(session()->has('cart.items'))->toBeFalse();
});
