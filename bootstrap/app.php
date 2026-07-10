<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\SetLocale;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(
            at: env('TRUSTED_PROXIES', '*'),
            headers: Request::HEADER_X_FORWARDED_FOR
                | Request::HEADER_X_FORWARDED_HOST
                | Request::HEADER_X_FORWARDED_PORT
                | Request::HEADER_X_FORWARDED_PROTO
        );

        $middleware->web(append: [
            SecurityHeaders::class,
            SetLocale::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->dontFlash([
            'password',
            'password_confirmation',
            'current_password',
            'token',
            'api_key',
            'secret',
            'payment_secret',
        ]);

        $exceptions->context(function (): array {
            if (! app()->bound('request')) {
                return [];
            }

            $request = request();

            return array_filter([
                'request_method' => $request->method(),
                'request_path' => $request->path(),
                'route_name' => $request->route()?->getName(),
                'client_ip' => $request->ip(),
                'admin_area' => str_starts_with($request->path(), 'admin'),
                'user_id' => $request->user()?->getAuthIdentifier(),
            ], fn ($value): bool => $value !== null && $value !== '');
        });
    })->create();
