<?php

namespace App\Logging;

use Illuminate\Log\Logger;
use Monolog\LogRecord;

class SanitizeLogContext
{
    private const SENSITIVE_KEYS = [
        'authorization',
        'cookie',
        'password',
        'password_confirmation',
        'remember_token',
        'token',
        'api_key',
        'secret',
        'payment_secret',
        'card_number',
        'cvv',
        'cvc',
        'pin',
        'private_key',
    ];

    public function __invoke(Logger $logger): void
    {
        $logger->getLogger()->pushProcessor(function (LogRecord $record): LogRecord {
            $context = $this->sanitize($record->context);
            $context['request'] = $this->safeRequestContext();

            return $record->with(
                message: $this->redactString($record->message),
                context: $context,
            );
        });
    }

    /**
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    private function sanitize(array $context): array
    {
        foreach ($context as $key => $value) {
            if ($this->isSensitiveKey((string) $key)) {
                $context[$key] = '[filtered]';

                continue;
            }

            if (is_array($value)) {
                $context[$key] = $this->sanitize($value);

                continue;
            }

            if (is_string($value)) {
                $context[$key] = $this->redactString($value);
            }
        }

        return $context;
    }

    private function isSensitiveKey(string $key): bool
    {
        $key = strtolower($key);

        foreach (self::SENSITIVE_KEYS as $sensitiveKey) {
            if (str_contains($key, $sensitiveKey)) {
                return true;
            }
        }

        return false;
    }

    private function redactString(string $value): string
    {
        $value = preg_replace('/\b(password|token|api_key|secret|payment_secret|cookie|authorization)=([^\s&]+)/i', '$1=[filtered]', $value) ?? $value;
        $value = preg_replace('/\bBearer\s+[A-Za-z0-9._\-]+/i', 'Bearer [filtered]', $value) ?? $value;

        return $value;
    }

    /**
     * @return array<string, mixed>
     */
    private function safeRequestContext(): array
    {
        if (! app()->bound('request')) {
            return [];
        }

        $request = request();

        if (! $request) {
            return [];
        }

        return array_filter([
            'id' => $request->headers->get('X-Request-Id'),
            'method' => $request->method(),
            'path' => $request->path(),
            'route' => $request->route()?->getName(),
            'ip' => $request->ip(),
            'admin' => str_starts_with($request->path(), 'admin'),
            'user_id' => $request->user()?->getAuthIdentifier(),
        ], fn ($value): bool => $value !== null && $value !== '');
    }
}
