<?php

namespace App\Support;

class HtmlSanitizer
{
    private const ALLOWED_TAGS = '<p><br><strong><b><em><i><u><ul><ol><li><blockquote><h2><h3><h4><a>';

    public static function clean(?string $html): ?string
    {
        if ($html === null) {
            return null;
        }

        $html = preg_replace('/<(script|style|iframe|object|embed|form|input|button|meta|link)[^>]*>.*?<\/\\1>/is', '', $html) ?? '';
        $html = preg_replace('/\s+on[a-z]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $html) ?? '';
        $html = preg_replace('/\s+(style|srcdoc)\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $html) ?? '';
        $html = preg_replace('/(href)\s*=\s*("|\')\s*javascript:[^"\']*("|\')/i', '$1="#"', $html) ?? '';
        $html = preg_replace('/(href)\s*=\s*javascript:[^\s>]*/i', '$1="#"', $html) ?? '';

        return trim(strip_tags($html, self::ALLOWED_TAGS));
    }
}
