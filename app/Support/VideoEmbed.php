<?php

namespace App\Support;

class VideoEmbed
{
    public static function youtubeNoCookieUrl(?string $value): ?string
    {
        $id = self::youtubeId($value);

        return $id ? 'https://www.youtube-nocookie.com/embed/'.$id : null;
    }

    public static function youtubeId(?string $value): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        if (self::isValidYoutubeId($value)) {
            return $value;
        }

        $parts = parse_url($value);

        if (! is_array($parts)) {
            return null;
        }

        $host = strtolower((string) ($parts['host'] ?? ''));
        $host = preg_replace('/^www\./', '', $host) ?: $host;
        $path = trim((string) ($parts['path'] ?? ''), '/');

        if ($host === 'youtu.be') {
            return self::sanitizeYoutubeId(str($path)->before('/')->toString());
        }

        if (! in_array($host, ['youtube.com', 'm.youtube.com', 'music.youtube.com', 'youtube-nocookie.com'], true)) {
            return null;
        }

        if (str_starts_with($path, 'embed/')) {
            return self::sanitizeYoutubeId(str($path)->after('embed/')->before('/')->toString());
        }

        if (str_starts_with($path, 'shorts/')) {
            return self::sanitizeYoutubeId(str($path)->after('shorts/')->before('/')->toString());
        }

        parse_str((string) ($parts['query'] ?? ''), $query);

        return self::sanitizeYoutubeId($query['v'] ?? null);
    }

    private static function sanitizeYoutubeId(mixed $value): ?string
    {
        $value = trim((string) $value);

        return self::isValidYoutubeId($value) ? $value : null;
    }

    private static function isValidYoutubeId(string $value): bool
    {
        return (bool) preg_match('/^[A-Za-z0-9_-]{11}$/', $value);
    }
}
