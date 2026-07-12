<?php

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;

test('critical marketplace routes are registered', function () {
    expect(Route::has('home'))->toBeTrue()
        ->and(Route::has('artworks.index'))->toBeTrue()
        ->and(Route::has('gallery'))->toBeTrue()
        ->and(Route::has('artwork.show'))->toBeTrue()
        ->and(Route::has('cart.index'))->toBeTrue()
        ->and(Route::has('checkout.create'))->toBeTrue()
        ->and(Route::has('orders.index'))->toBeTrue()
        ->and(Route::has('orders.show'))->toBeTrue()
        ->and(Route::has('invoice.show'))->toBeTrue()
        ->and(Route::has('artwork.download'))->toBeTrue()
        ->and(Route::has('favorites.index'))->toBeTrue()
        ->and(Route::has('search.index'))->toBeTrue();
});

test('core marketplace public pages render without exposing private storage internals', function (string $uri) {
    $this->withSession(['locale' => 'en'])
        ->get($uri)
        ->assertOk()
        ->assertDontSee('storage/app/private', false)
        ->assertDontSee('digital_file_path', false)
        ->assertDontSee('payment_secret', false);
})->with([
    '/',
    '/artworks',
    '/gallery',
    '/photography',
    '/artists',
    '/news',
    '/about',
    '/contact',
    '/cart',
    '/search?q=chapung',
]);

test('sensitive marketplace customer routes require authentication', function () {
    $this->get(route('dashboard'))->assertRedirect(route('login'));
    $this->get(route('orders.index'))->assertRedirect(route('login'));
    $this->get(route('favorites.index'))->assertRedirect(route('login'));
});

test('marketplace views keep responsive and loading states in place', function () {
    $gallery = File::get(resource_path('views/gallery.blade.php'));
    $product = File::get(resource_path('views/artwork-detail.blade.php'));
    $cart = File::get(resource_path('views/cart/index.blade.php'));
    $checkout = File::get(resource_path('views/checkout/create.blade.php'));

    expect($gallery)->toContain('data-catalog-skeleton')
        ->and($gallery)->toContain('md:grid-cols-3')
        ->and($gallery)->toContain('xl:grid-cols-4')
        ->and($product)->toContain('lg:grid-cols')
        ->and($cart)->toContain('lg:grid-cols')
        ->and($checkout)->toContain('lg:grid-cols')
        ->and($gallery.$product.$cart.$checkout)->not->toContain('storage/app/private');
});

test('chapung art brand assets are integrated across primary surfaces', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('images/brand/chapung-art-logo-dark.svg', false)
        ->assertSee('images/brand/chapung-art-icon.svg', false)
        ->assertSee('alt="Chapung Art"', false)
        ->assertSee('og:logo', false);

    $this->get(route('login'))
        ->assertOk()
        ->assertSee('images/brand/chapung-art-logo.svg', false)
        ->assertSee('images/brand/chapung-art-icon.svg', false)
        ->assertSee('alt="Chapung Art"', false);

    $adminProvider = File::get(app_path('Providers/Filament/AdminPanelProvider.php'));
    $filamentLogo = File::get(resource_path('views/filament/brand-logo.blade.php'));
    $invoiceView = File::get(resource_path('views/invoice/show.blade.php'));
    $mailHeader = File::get(resource_path('views/mail/partials/brand-header.blade.php'));

    expect($adminProvider)->toContain("view('filament.brand-logo', ['variant' => 'normal']")
        ->and($adminProvider)->toContain("view('filament.brand-logo', ['variant' => 'dark']")
        ->and($adminProvider)->toContain("favicon(asset('images/brand/chapung-art-icon.svg'))")
        ->and($filamentLogo)->toContain('width="108"')
        ->and($filamentLogo)->toContain('height="135"')
        ->and($invoiceView)->toContain('variant="normal"')
        ->and($invoiceView)->toContain('width="72"')
        ->and($invoiceView)->toContain('height="90"')
        ->and($mailHeader)->toContain("asset('images/brand/chapung-art-logo.svg')")
        ->and($mailHeader)->toContain('alt="Chapung Art"')
        ->and($mailHeader)->toContain('width="96"')
        ->and($mailHeader)->toContain('height="120"');
});

test('production safety files protect secrets and build artifacts', function () {
    $gitignore = File::get(base_path('.gitignore'));
    $filesystem = config('filesystems.disks.local.root');

    expect($gitignore)->toContain('.env')
        ->and($gitignore)->toContain('/public/build')
        ->and($gitignore)->toContain('/public/storage')
        ->and(str_replace('\\', '/', (string) $filesystem))->toContain('storage/app/private');
});

test('final marketplace qa documentation exists', function () {
    $document = File::get(base_path('docs/MARKETPLACE_FINAL_QA.md'));

    expect($document)->toContain('PHASE 10')
        ->and($document)->toContain('UI')
        ->and($document)->toContain('Responsive')
        ->and($document)->toContain('Performance')
        ->and($document)->toContain('Security')
        ->and($document)->toContain('Testing')
        ->and($document)->toContain('Manual Verification');
});
