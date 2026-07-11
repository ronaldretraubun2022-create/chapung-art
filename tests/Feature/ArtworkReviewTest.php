<?php

use App\Models\Artwork;
use App\Models\ArtworkReview;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;

function reviewArtwork(array $overrides = []): Artwork
{
    return Artwork::create(array_merge([
        'title' => 'Reviewable Artwork',
        'slug' => 'reviewable-artwork',
        'artist_name' => 'Chapung Artist',
        'price' => 1200000,
        'status' => 'available',
        'stock' => 1,
        'thumbnail' => 'artworks/reviewable.jpg',
    ], $overrides));
}

function paidOrderItemFor(User $user, Artwork $artwork): OrderItem
{
    $order = Order::create([
        'customer_name' => $user->name,
        'customer_email' => $user->email,
        'customer_phone' => '081344001427',
        'subtotal' => 1200000,
        'discount_total' => 0,
        'shipping_total' => 0,
        'status' => 'completed',
        'payment_status' => 'paid',
    ]);

    return OrderItem::create([
        'order_id' => $order->id,
        'product_type' => 'artwork',
        'product_id' => $artwork->id,
        'title' => $artwork->title,
        'price' => 1200000,
        'quantity' => 1,
    ]);
}

test('guest cannot submit artwork review', function () {
    $artwork = reviewArtwork();

    $this->post(route('artwork.reviews.store', $artwork->slug), [
        'rating' => 5,
        'body' => 'Karya sangat baik dan pengemasan rapi.',
    ])->assertRedirect(route('login'));

    expect(ArtworkReview::count())->toBe(0);
});

test('user without verified purchase cannot review artwork', function () {
    $artwork = reviewArtwork();
    $user = User::factory()->create();

    $this->actingAs($user)
        ->from(route('artwork.show', $artwork->slug))
        ->post(route('artwork.reviews.store', $artwork->slug), [
            'rating' => 5,
            'body' => 'Saya ingin memberi ulasan tanpa order.',
        ])
        ->assertRedirect(route('artwork.show', $artwork->slug))
        ->assertSessionHasErrors('review');

    expect(ArtworkReview::count())->toBe(0);
});

test('verified buyer can submit pending sanitized review', function () {
    $artwork = reviewArtwork();
    $user = User::factory()->create(['name' => 'Maya Collector']);
    $orderItem = paidOrderItemFor($user, $artwork);

    $this->actingAs($user)
        ->post(route('artwork.reviews.store', $artwork->slug), [
            'rating' => 5,
            'title' => '<b>Karya istimewa</b>',
            'body' => '<script>alert(1)</script>Karya tiba aman dan kualitasnya sangat baik.',
        ])
        ->assertRedirect()
        ->assertSessionHas('status');

    $review = ArtworkReview::first();

    expect($review)->not->toBeNull()
        ->and($review->artwork_id)->toBe($artwork->id)
        ->and($review->user_id)->toBe($user->id)
        ->and($review->order_item_id)->toBe($orderItem->id)
        ->and($review->status)->toBe(ArtworkReview::STATUS_PENDING)
        ->and($review->is_verified_purchase)->toBeTrue()
        ->and($review->title)->toBe('Karya istimewa')
        ->and($review->body)->not->toContain('script');
});

test('only approved reviews appear on artwork detail', function () {
    $artwork = reviewArtwork();
    $approvedUser = User::factory()->create(['name' => 'Approved Collector']);
    $pendingUser = User::factory()->create(['name' => 'Pending Collector']);

    ArtworkReview::create([
        'artwork_id' => $artwork->id,
        'user_id' => $approvedUser->id,
        'reviewer_name' => $approvedUser->name,
        'reviewer_email' => $approvedUser->email,
        'rating' => 5,
        'title' => 'Approved review title',
        'body' => 'Review ini sudah disetujui admin.',
        'status' => ArtworkReview::STATUS_APPROVED,
        'is_verified_purchase' => true,
    ]);

    ArtworkReview::create([
        'artwork_id' => $artwork->id,
        'user_id' => $pendingUser->id,
        'reviewer_name' => $pendingUser->name,
        'reviewer_email' => $pendingUser->email,
        'rating' => 2,
        'title' => 'Pending review title',
        'body' => 'Review ini belum dimoderasi.',
        'status' => ArtworkReview::STATUS_PENDING,
        'is_verified_purchase' => true,
    ]);

    $this->withSession(['locale' => 'en'])
        ->get(route('artwork.show', $artwork->slug))
        ->assertOk()
        ->assertSee('Approved review title')
        ->assertSee('5.0')
        ->assertDontSee('Pending review title');
});

test('duplicate review for the same artwork is rejected', function () {
    $artwork = reviewArtwork();
    $user = User::factory()->create();
    paidOrderItemFor($user, $artwork);

    ArtworkReview::create([
        'artwork_id' => $artwork->id,
        'user_id' => $user->id,
        'reviewer_name' => $user->name,
        'reviewer_email' => $user->email,
        'rating' => 4,
        'body' => 'Review pertama sudah masuk.',
        'status' => ArtworkReview::STATUS_PENDING,
        'is_verified_purchase' => true,
    ]);

    $this->actingAs($user)
        ->from(route('artwork.show', $artwork->slug))
        ->post(route('artwork.reviews.store', $artwork->slug), [
            'rating' => 5,
            'body' => 'Review kedua seharusnya ditolak.',
        ])
        ->assertRedirect(route('artwork.show', $artwork->slug))
        ->assertSessionHasErrors('review');

    expect(ArtworkReview::count())->toBe(1);
});
