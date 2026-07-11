<?php

use App\Models\Artwork;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Services\DigitalDownloadService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

function digitalDownloadArtwork(array $overrides = []): Artwork
{
    return Artwork::create(array_merge([
        'title' => 'Digital Artwork Merauke',
        'slug' => 'digital-artwork-merauke',
        'artist_name' => 'Chapung Artist',
        'price' => 250000,
        'status' => 'available',
        'medium' => 'Digital',
        'stock' => 1,
        'digital_download_enabled' => true,
        'digital_file_path' => 'artworks/digital/master.pdf',
        'digital_file_name' => 'Original Master.pdf',
    ], $overrides));
}

function digitalDownloadOrder(User $user, Artwork $artwork, array $overrides = []): Order
{
    $order = Order::create(array_merge([
        'customer_name' => $user->name,
        'customer_email' => $user->email,
        'customer_phone' => '081344001427',
        'subtotal' => 250000,
        'discount_total' => 0,
        'shipping_total' => 0,
        'status' => 'pending',
        'payment_status' => 'paid',
    ], $overrides));

    OrderItem::create([
        'order_id' => $order->id,
        'product_type' => 'artwork',
        'product_id' => $artwork->id,
        'title' => $artwork->title,
        'price' => 250000,
        'quantity' => 1,
    ]);

    return $order->fresh(['items']);
}

test('guest cannot download digital artwork file', function () {
    Storage::fake('local');
    Storage::disk('local')->put('artworks/digital/master.pdf', "%PDF-1.4\nprivate content");
    $artwork = digitalDownloadArtwork();

    $this->get(route('artwork.download', $artwork->slug))->assertRedirect('/login');
});

test('authenticated user without verified purchase cannot download digital file', function () {
    Storage::fake('local');
    Storage::disk('local')->put('artworks/digital/master.pdf', "%PDF-1.4\nprivate content");
    $artwork = digitalDownloadArtwork();
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('artwork.download', $artwork->slug))
        ->assertForbidden();
});

test('buyer with unpaid order cannot download digital file', function () {
    Storage::fake('local');
    Storage::disk('local')->put('artworks/digital/master.pdf', "%PDF-1.4\nprivate content");
    $artwork = digitalDownloadArtwork();
    $user = User::factory()->create(['email' => 'buyer@example.com']);
    digitalDownloadOrder($user, $artwork, ['payment_status' => 'unpaid', 'status' => 'pending']);

    $this->actingAs($user)
        ->get(route('artwork.download', $artwork->slug))
        ->assertForbidden();
});

test('buyer with paid order can download private digital file safely', function () {
    Storage::fake('local');
    Storage::disk('local')->put('artworks/digital/master.pdf', "%PDF-1.4\nprivate content");
    $artwork = digitalDownloadArtwork();
    $user = User::factory()->create(['email' => 'buyer@example.com']);
    digitalDownloadOrder($user, $artwork);

    $response = $this->actingAs($user)->get(route('artwork.download', $artwork->slug));

    $response->assertOk();
    $response->assertHeader('X-Content-Type-Options', 'nosniff');

    expect($response->headers->get('Content-Disposition'))
        ->toContain('original-master.pdf')
        ->not->toContain('artworks/digital')
        ->not->toContain('storage/app/private')
        ->and($response->streamedContent())->toContain('private content');
});

test('missing private digital file returns safe not found response', function () {
    Storage::fake('local');
    $artwork = digitalDownloadArtwork();
    $user = User::factory()->create(['email' => 'buyer@example.com']);
    digitalDownloadOrder($user, $artwork);

    $this->actingAs($user)
        ->get(route('artwork.download', $artwork->slug))
        ->assertNotFound()
        ->assertDontSee('artworks/digital/master.pdf')
        ->assertDontSee('storage/app/private');
});

test('artwork detail shows secure download access without exposing private path', function () {
    Storage::fake('local');
    Storage::disk('local')->put('artworks/digital/master.pdf', "%PDF-1.4\nprivate content");
    $artwork = digitalDownloadArtwork();
    $user = User::factory()->create(['email' => 'buyer@example.com']);
    digitalDownloadOrder($user, $artwork);

    $this->actingAs($user)
        ->withSession(['locale' => 'id'])
        ->get(route('artwork.show', $artwork->slug))
        ->assertOk()
        ->assertSee('Unduh File Digital')
        ->assertDontSee('artworks/digital/master.pdf')
        ->assertDontSee('storage/app/private');
});

test('dangerous digital upload is rejected', function () {
    $file = UploadedFile::fake()->createWithContent('payload.pdf', "MZ<?php echo 'bad';");

    app(DigitalDownloadService::class)->assertSafeFile($file);
})->throws(ValidationException::class);

test('oversized digital upload is rejected', function () {
    $file = UploadedFile::fake()->create('oversized.pdf', DigitalDownloadService::maxKilobytes() + 1, 'application/pdf');

    app(DigitalDownloadService::class)->assertSafeFile($file);
})->throws(ValidationException::class);
