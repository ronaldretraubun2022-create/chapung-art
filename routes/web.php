<?php

use App\Http\Controllers\ArtworkController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\PhotographyController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

Route::get('/lang/{locale}', LocaleController::class)->name('lang.switch');
Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');
Route::get('/', HomeController::class)->name('home');
Route::get('/gallery', [ArtworkController::class, 'gallery'])->name('gallery');
Route::get('/artwork/{slug}', [ArtworkController::class, 'show'])->name('artwork.show');
Route::get('/photography', [PhotographyController::class, 'index'])->name('photography.index');
Route::get('/photography/{slug}', [PhotographyController::class, 'show'])->name('photography.show');
Route::get('/media', [PostController::class, 'index'])->name('media.index');
Route::get('/media/{slug}', [PostController::class, 'show'])->name('media.show');

Route::view('/dashboard', 'dashboard')->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
