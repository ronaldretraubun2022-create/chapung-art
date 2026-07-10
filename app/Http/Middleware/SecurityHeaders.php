<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Frame-Options', (string) config('security.headers.x_frame_options', 'SAMEORIGIN'));
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Permitted-Cross-Domain-Policies', 'none');
        $response->headers->set('Referrer-Policy', (string) config('security.headers.referrer_policy', 'strict-origin-when-cross-origin'));
        $response->headers->set('Permissions-Policy', (string) config('security.headers.permissions_policy', 'camera=(), microphone=(), geolocation=(), payment=()'));

        if (config('security.csp.enabled', true)) {
            $response->headers->set($this->cspHeaderName(), $this->cspHeaderValue());
        }

        if ($this->shouldSendHsts($request)) {
            $response->headers->set('Strict-Transport-Security', $this->hstsHeaderValue());
        }

        return $response;
    }

    private function cspHeaderName(): string
    {
        return config('security.csp.report_only', false)
            ? 'Content-Security-Policy-Report-Only'
            : 'Content-Security-Policy';
    }

    private function cspHeaderValue(): string
    {
        $directives = config('security.csp.directives', []);

        return collect($directives)
            ->filter(fn (array $sources): bool => $sources !== [])
            ->map(fn (array $sources, string $directive): string => $directive.' '.implode(' ', array_unique($sources)))
            ->implode('; ');
    }

    private function shouldSendHsts(Request $request): bool
    {
        return (bool) config('security.hsts.enabled', true)
            && config('app.env') === 'production'
            && $request->isSecure();
    }

    private function hstsHeaderValue(): string
    {
        $directives = ['max-age='.(int) config('security.hsts.max_age', 31536000)];

        if (config('security.hsts.include_subdomains', true)) {
            $directives[] = 'includeSubDomains';
        }

        if (config('security.hsts.preload', true)) {
            $directives[] = 'preload';
        }

        return implode('; ', $directives);
    }
}
