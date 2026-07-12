<?php

use App\Http\Controllers\ArtworkController;
use App\Http\Controllers\ArtworkReviewController;
use App\Http\Controllers\ArtistController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CertificateVerificationController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CollectionController;
use App\Http\Controllers\CustomerOrderController;
use App\Http\Controllers\DigitalDownloadController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\PhotographyController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicPageController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

Route::get('/language/{locale}', LocaleController::class)
    ->whereIn('locale', config('locales.available', ['id', 'en']))
    ->name('language.switch');
Route::get('/lang/{locale}', LocaleController::class)
    ->whereIn('locale', config('locales.available', ['id', 'en']))
    ->name('lang.switch');
Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');
Route::get('/search', [SearchController::class, 'index'])->middleware('throttle:global-search')->name('search.index');
Route::get('/search/live', [SearchController::class, 'live'])->middleware('throttle:global-search')->name('search.live');
Route::get('/certificates/verify/{certificateNumber}', CertificateVerificationController::class)
    ->where('certificateNumber', '[A-Z0-9\-]{4,64}')
    ->middleware('throttle:certificate-verification')
    ->name('certificates.verify');
Route::get('/', HomeController::class)->name('home');
Route::get('/artworks', [ArtworkController::class, 'index'])->name('artworks.index');
Route::get('/gallery', [ArtworkController::class, 'gallery'])->name('gallery');
Route::get('/artwork/{slug}', [ArtworkController::class, 'show'])->name('artwork.show');
Route::get('/photography', [PhotographyController::class, 'index'])->name('photography.index');
Route::get('/photography/{slug}', [PhotographyController::class, 'show'])->name('photography.show');
Route::get('/artists', [ArtistController::class, 'index'])->name('artists.index');
Route::get('/artists/{slug}', [ArtistController::class, 'show'])->name('artists.show');
Route::get('/collections/{slug}', [CollectionController::class, 'show'])->name('collections.show');
Route::get('/news', [PostController::class, 'index'])->name('news.index');
Route::get('/news/{slug}', [PostController::class, 'show'])->name('news.show');
Route::get('/media', [PostController::class, 'index'])->name('media.index');
Route::get('/media/{slug}', [PostController::class, 'show'])->name('media.show');
Route::get('/about', [PublicPageController::class, 'about'])->name('about');
Route::get('/contact', [PublicPageController::class, 'contact'])->name('contact');
Route::post('/contact', [PublicPageController::class, 'sendContact'])->middleware('throttle:public-form')->name('contact.send');
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/items', [CartController::class, 'store'])->middleware('throttle:public-form')->name('cart.store');
Route::patch('/cart/items/{artwork}', [CartController::class, 'update'])->middleware('throttle:public-form')->name('cart.update');
Route::delete('/cart/items/{artwork}', [CartController::class, 'destroy'])->middleware('throttle:public-form')->name('cart.destroy');
Route::post('/cart/coupon', [CartController::class, 'applyCoupon'])->middleware('throttle:public-form')->name('cart.coupon.apply');
Route::delete('/cart/coupon', [CartController::class, 'removeCoupon'])->middleware('throttle:public-form')->name('cart.coupon.remove');
Route::post('/cart/shipping-estimate', [CartController::class, 'estimateShipping'])->middleware('throttle:public-form')->name('cart.shipping.estimate');
Route::delete('/cart/shipping-estimate', [CartController::class, 'removeShipping'])->middleware('throttle:public-form')->name('cart.shipping.remove');
Route::get('/checkout', [CheckoutController::class, 'create'])->name('checkout.create');
Route::post('/checkout', [CheckoutController::class, 'store'])->middleware('throttle:public-form')->name('checkout.store');
Route::get('/checkout/success/{orderNumber}', [CheckoutController::class, 'success'])
    ->where('orderNumber', 'CA-[0-9]{8}-[0-9]{5}')
    ->name('checkout.success');

Route::get('/dashboard', [CustomerOrderController::class, 'dashboard'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/favorites', [FavoriteController::class, 'index'])->name('favorites.index');
    Route::post('/favorites/{artwork:slug}', [FavoriteController::class, 'store'])->middleware('throttle:public-form')->name('favorites.store');
    Route::delete('/favorites/{artwork:slug}', [FavoriteController::class, 'destroy'])->middleware('throttle:public-form')->name('favorites.destroy');
    Route::post('/artwork/{artwork:slug}/reviews', [ArtworkReviewController::class, 'store'])
        ->middleware('throttle:public-form')
        ->name('artwork.reviews.store');
    Route::get('/artwork/{artwork:slug}/download', DigitalDownloadController::class)
        ->middleware('throttle:public-form')
        ->name('artwork.download');
    Route::get('/orders', [CustomerOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [CustomerOrderController::class, 'show'])->name('orders.show');
    Route::get('/orders/{order}/invoice', [InvoiceController::class, 'show'])->name('invoice.show');
    Route::get('/orders/{order}/invoice.pdf', [InvoiceController::class, 'download'])->name('invoice.download');
});

require __DIR__.'/auth.php';
