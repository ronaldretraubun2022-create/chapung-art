<?php

use Illuminate\Support\Facades\File;

test('public layout exposes theme bootstrap and toggles', function () {
    $this->withSession(['locale' => 'en'])
        ->get(route('home'))
        ->assertOk()
        ->assertSee('chapung-theme', false)
        ->assertSee('data-theme="dark"', false)
        ->assertSee('data-theme-toggle', false)
        ->assertSee('Switch to light mode')
        ->assertSee('ca-page-shell', false);
});

test('public css supports light theme responsive motion and reduced motion', function () {
    $css = File::get(resource_path('css/app.css'));

    expect($css)->toContain("html[data-theme='light']")
        ->and($css)->toContain('@media (prefers-reduced-motion: reduce)')
        ->and($css)->toContain('@keyframes ca-fade-up')
        ->and($css)->toContain('.ca-page-shell')
        ->and($css)->toContain('scroll-padding-top')
        ->and($css)->toContain(':focus-visible');
});

test('public javascript initializes theme toggle safely', function () {
    $js = File::get(resource_path('js/app.js'));

    expect($js)->toContain('initThemeToggle')
        ->and($js)->toContain('prefers-color-scheme: light')
        ->and($js)->toContain('data-theme-toggle')
        ->and($js)->toContain('aria-pressed')
        ->and($js)->toContain('chapung-theme');
});
