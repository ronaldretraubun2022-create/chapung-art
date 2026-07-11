<?php

use App\Models\User;

test('language switch stores valid locale in session and redirects back', function () {
    $this->from(route('gallery'))
        ->get(route('language.switch', 'en'))
        ->assertRedirect(route('gallery'))
        ->assertSessionHas('locale', 'en');
});

test('language switch rejects unsupported locale', function () {
    $this->get('/language/fr')
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
        ->assertSee('Jelajahi Karya');
});

test('session locale renders english frontend copy', function () {
    $this->withSession(['locale' => 'en'])
        ->get(route('home'))
        ->assertOk()
        ->assertSee('lang="en"', false)
        ->assertSee('Explore Artwork')
        ->assertSee('South Papua');
});
