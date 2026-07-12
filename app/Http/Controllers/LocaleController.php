<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    public function __invoke(string $locale, Request $request): RedirectResponse
    {
        $available = config('locales.available', ['id', 'en']);

        abort_unless(in_array($locale, $available, true), 404);

        session(['locale' => $locale]);
        app()->setLocale($locale);
        app()->setFallbackLocale(config('locales.fallback', 'en'));

        if ($request->user() && $request->user()->locale !== $locale) {
            $request->user()->forceFill(['locale' => $locale])->saveQuietly();
        }

        return redirect()->back(fallback: route('home'));
    }
}
