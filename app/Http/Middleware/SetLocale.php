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
        $locale = session('locale') ?: $request->user()?->locale ?: config('locales.default', 'id');

        if (! in_array($locale, $available, true)) {
            $locale = config('locales.default', 'id');
        }

        session(['locale' => $locale]);
        app()->setLocale($locale);

        return $next($request);
    }
}
