<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $available = config('locales.available', ['id', 'en']);
        $default = config('locales.default', 'id');
        $fallback = config('locales.fallback', 'en');
        $locale = session('locale') ?: $request->user()?->locale ?: $default;

        if (! in_array($locale, $available, true)) {
            $locale = $default;
        }

        session(['locale' => $locale]);
        app()->setLocale($locale);
        app()->setFallbackLocale($fallback);

        return $next($request);
    }
}
