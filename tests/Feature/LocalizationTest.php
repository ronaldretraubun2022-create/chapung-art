<?php

use App\Filament\Resources\ArtworkResource;
use App\Filament\Resources\OrderResource;
use App\Models\User;

test('language switch stores valid locale in session and redirects back', function () {
    $this->from(route('gallery'))
        ->get(route('language.switch', 'en'))
        ->assertRedirect(route('gallery'))
        ->assertSessionHas('locale', 'en');
});

test('language switch accepts indonesian locale', function () {
    $this->withSession(['locale' => 'en'])
        ->from(route('home'))
        ->get(route('language.switch', 'id'))
        ->assertRedirect(route('home'))
        ->assertSessionHas('locale', 'id');
});

test('language switch rejects unsupported locale', function () {
    $this->withSession(['locale' => 'en'])
        ->get('/language/fr')
        ->assertSessionHas('locale', 'en')
        ->assertNotFound();
});

test('authenticated language switch persists locale to user profile', function () {
    $user = User::factory()->create(['locale' => 'id']);

    $this->actingAs($user)
        ->get(route('language.switch', 'en'))
        ->assertRedirect();

    expect($user->refresh()->locale)->toBe('en');
});

test('set locale middleware falls back to indonesian for invalid session locale', function () {
    $this->withSession(['locale' => 'fr'])
        ->get(route('home'))
        ->assertOk()
        ->assertSessionHas('locale', 'id')
        ->assertSee('Karya Tersedia');
});

test('selected locale persists across navigation requests', function () {
    $this->from(route('home'))
        ->get(route('language.switch', 'en'))
        ->assertSessionHas('locale', 'en');

    $this->get(route('gallery'))
        ->assertOk()
        ->assertSee('lang="en"', false)
        ->assertSee('Artwork Gallery');
});

test('session locale renders english frontend copy', function () {
    $this->withSession(['locale' => 'en'])
        ->get(route('home'))
        ->assertOk()
        ->assertSee('lang="en"', false)
        ->assertSee('Available Artworks')
        ->assertSee('South Papua');
});

test('public navbar shows language choices and favorite shortcut', function () {
    $this->withSession(['locale' => 'id'])
        ->get(route('home'))
        ->assertOk()
        ->assertSee('Indonesia')
        ->assertSee('English')
        ->assertSee('data-favorite-nav', false);
});

test('admin language switcher shows active locale clearly', function () {
    app()->setLocale('en');

    $html = view('partials.language-switcher', ['context' => 'admin'])->render();

    expect($html)->toContain('Language')
        ->toContain('English')
        ->toContain('Active')
        ->toContain('aria-current="page"')
        ->toContain(route('language.switch', 'id'))
        ->toContain(route('language.switch', 'en'));
});

test('filament navigation labels follow active locale', function () {
    app()->setLocale('id');

    expect(ArtworkResource::getNavigationLabel())->toBe('Karya')
        ->and(ArtworkResource::getNavigationGroup())->toBe('Marketplace')
        ->and(OrderResource::getNavigationLabel())->toBe('Pesanan')
        ->and(OrderResource::getNavigationGroup())->toBe('Perdagangan');

    app()->setLocale('en');

    expect(ArtworkResource::getNavigationLabel())->toBe('Artworks')
        ->and(OrderResource::getNavigationLabel())->toBe('Orders')
        ->and(OrderResource::getNavigationGroup())->toBe('Commerce');
});
