<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    public function __invoke(string $locale, Request $request): RedirectResponse
    {
        abort_unless(in_array($locale, config('locales.available', ['id', 'en']), true), 404);

        session(['locale' => $locale]);
        app()->setLocale($locale);

        if ($request->user() && $request->user()->locale !== $locale) {
            $request->user()->forceFill(['locale' => $locale])->saveQuietly();
        }

        return redirect()->back(fallback: route('home'));
    }
}
