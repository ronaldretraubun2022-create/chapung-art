<?php

use App\Models\Artwork;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\CartService;

function checkoutArtwork(array $overrides = []): Artwork
{
    return Artwork::create(array_merge([
        'title' => 'Checkout Artwork '.fake()->unique()->numberBetween(1000, 9999),
        'slug' => 'checkout-artwork-'.fake()->unique()->numberBetween(1000, 9999),
        'price' => 300000,
        'stock' => 4,
        'status' => 'available',
    ], $overrides));
}

function checkoutPayload(array $overrides = []): array
{
    return array_merge([
        'checkout_token' => session('checkout.token'),
        'customer_name' => 'Maria Animha',
        'customer_email' => 'maria@example.com',
        'customer_phone' => '081234567890',
        'customer_whatsapp' => '081234567890',
        'province' => 'Papua Selatan',
        'city' => 'Merauke',
        'address' => 'Jl. Raya Mandala No. 1',
        'notes' => 'Konfirmasi melalui WhatsApp.',
    ], $overrides);
}

test('checkout form renders with order summary', function () {
    $artwork = checkoutArtwork(['price' => 150000]);

    $this->post(route('cart.store'), [
        'artwork_id' => $artwork->id,
        'quantity' => 2,
    ]);

    $this->get(route('checkout.create'))
        ->assertOk()
        ->assertSee('Checkout')
        ->assertSee('Order Summary')
        ->assertSee('Rp 300.000')
        ->assertSessionHas('checkout.token');
});

test('checkout validates required customer fields', function () {
    $artwork = checkoutArtwork();

    $this->post(route('cart.store'), [
        'artwork_id' => $artwork->id,
        'quantity' => 1,
    ]);

    $this->get(route('checkout.create'))->assertOk();

    $this->from(route('checkout.create'))
        ->post(route('checkout.store'), [
            'checkout_token' => session('checkout.token'),
        ])
        ->assertRedirect(route('checkout.create'))
        ->assertSessionHasErrors(['customer_name', 'customer_email', 'customer_phone', 'province', 'city', 'address']);
});

test('checkout creates order in transaction and clears cart', function () {
    $artwork = checkoutArtwork(['price' => 200000, 'stock' => 3]);

    $this->post(route('cart.store'), [
        'artwork_id' => $artwork->id,
        'quantity' => 2,
    ]);

    $this->get(route('checkout.create'))->assertOk();

    $response = $this->post(route('checkout.store'), checkoutPayload());

    $order = Order::query()->with('items')->firstOrFail();

    $response->assertRedirect(route('checkout.success', $order->order_number));

    expect($order->order_number)->toMatch('/^CA-\d{8}-\d{5}$/')
        ->and((float) $order->subtotal)->toBe(400000.0)
        ->and((float) $order->grand_total)->toBe(400000.0)
        ->and($order->items)->toHaveCount(1)
        ->and($order->items->first()->title)->toBe($artwork->title)
        ->and(Customer::query()->where('email', 'maria@example.com')->exists())->toBeTrue()
        ->and(app(CartService::class)->summary()['count'])->toBe(0)
        ->and($artwork->fresh()->stock)->toBe(1);
});

test('checkout prevents double submit with same token', function () {
    $artwork = checkoutArtwork(['price' => 100000, 'stock' => 5]);

    $this->post(route('cart.store'), [
        'artwork_id' => $artwork->id,
        'quantity' => 2,
    ]);

    $this->get(route('checkout.create'))->assertOk();
    $payload = checkoutPayload();

    $this->post(route('checkout.store'), $payload)->assertRedirect();
    $this->post(route('checkout.store'), $payload)->assertRedirect();

    expect(Order::query()->count())->toBe(1)
        ->and(OrderItem::query()->count())->toBe(1)
        ->and($artwork->fresh()->stock)->toBe(3);
});

test('checkout rolls back when stock changes before submit', function () {
    $artwork = checkoutArtwork(['price' => 100000, 'stock' => 2]);

    $this->post(route('cart.store'), [
        'artwork_id' => $artwork->id,
        'quantity' => 2,
    ]);

    $this->get(route('checkout.create'))->assertOk();
    $artwork->forceFill(['stock' => 1])->save();

    $this->from(route('checkout.create'))
        ->post(route('checkout.store'), checkoutPayload())
        ->assertRedirect(route('checkout.create'))
        ->assertSessionHasErrors('quantity');

    expect(Order::query()->count())->toBe(0)
        ->and(OrderItem::query()->count())->toBe(0)
        ->and(Customer::query()->count())->toBe(0)
        ->and($artwork->fresh()->stock)->toBe(1)
        ->and(app(CartService::class)->summary()['count'])->toBe(1);
});

test('checkout success page displays order number and totals', function () {
    $artwork = checkoutArtwork(['price' => 175000]);

    $this->post(route('cart.store'), [
        'artwork_id' => $artwork->id,
        'quantity' => 1,
    ]);

    $this->get(route('checkout.create'))->assertOk();
    $this->post(route('checkout.store'), checkoutPayload())->assertRedirect();

    $order = Order::query()->firstOrFail();

    $this->get(route('checkout.success', $order->order_number))
        ->assertOk()
        ->assertSee($order->order_number)
        ->assertSee('Rp 175.000')
        ->assertSee('Order Items');
});
