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

        $this->setHeader($response, 'X-Frame-Options', config('security.headers.x_frame_options', 'SAMEORIGIN'));
        $this->setHeader($response, 'X-Content-Type-Options', 'nosniff');
        $this->setHeader($response, 'X-Permitted-Cross-Domain-Policies', 'none');
        $this->setHeader($response, 'Referrer-Policy', config('security.headers.referrer_policy', 'strict-origin-when-cross-origin'));
        $this->setHeader($response, 'Permissions-Policy', config('security.headers.permissions_policy', 'camera=(), microphone=(), geolocation=(), payment=()'));

        if ($this->booleanConfig('security.csp.enabled', true)) {
            $this->setHeader($response, $this->cspHeaderName(), $this->cspHeaderValue());
        }

        if ($this->shouldSendHsts($request)) {
            $this->setHeader($response, 'Strict-Transport-Security', $this->hstsHeaderValue());
        }

        return $response;
    }

    private function setHeader(Response $response, string $name, mixed $value): void
    {
        $value = $this->normalizeHeaderValue($value);

        if ($value === '') {
            return;
        }

        $response->headers->set($name, $value);
    }

    private function cspHeaderName(): string
    {
        return $this->booleanConfig('security.csp.report_only', false)
            ? 'Content-Security-Policy-Report-Only'
            : 'Content-Security-Policy';
    }

    private function cspHeaderValue(): string
    {
        $directives = config('security.csp.directives', []);

        if (! is_array($directives)) {
            return '';
        }

        return collect($directives)
            ->mapWithKeys(function (mixed $sources, string $directive): array {
                $directive = trim($directive);

                if (! preg_match('/^[a-zA-Z0-9-]+$/', $directive)) {
                    return [];
                }

                $sources = $this->normalizeCspSources($sources);

                return $sources === [] ? [] : [$directive => $sources];
            })
            ->map(fn (array $sources, string $directive): string => $directive.' '.implode(' ', $sources))
            ->implode('; ');
    }

    private function shouldSendHsts(Request $request): bool
    {
        return $this->booleanConfig('security.hsts.enabled', true)
            && config('app.env') === 'production'
            && $request->isSecure();
    }

    private function hstsHeaderValue(): string
    {
        $directives = ['max-age='.max(0, (int) config('security.hsts.max_age', 31536000))];

        if ($this->booleanConfig('security.hsts.include_subdomains', true)) {
            $directives[] = 'includeSubDomains';
        }

        if ($this->booleanConfig('security.hsts.preload', true)) {
            $directives[] = 'preload';
        }

        return implode('; ', $directives);
    }

    private function normalizeCspSources(mixed $sources): array
    {
        if (is_string($sources)) {
            $sources = preg_split('/[\s,]+/', $sources, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        }

        if (! is_iterable($sources)) {
            return [];
        }

        $normalized = [];

        foreach ($sources as $source) {
            if (! is_scalar($source)) {
                continue;
            }

            $source = $this->normalizeHeaderValue($source);

            if ($source !== '') {
                $normalized[] = $source;
            }
        }

        return array_values(array_unique($normalized));
    }

    private function normalizeHeaderValue(mixed $value): string
    {
        if (! is_scalar($value)) {
            return '';
        }

        return trim((string) preg_replace('/[\x00-\x1F\x7F]+/', ' ', (string) $value));
    }

    private function booleanConfig(string $key, bool $default): bool
    {
        return filter_var(config($key, $default), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? $default;
    }
}
