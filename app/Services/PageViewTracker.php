<?php

namespace App\Services;

use App\Models\PageView;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class PageViewTracker
{
    public function track(Request $request, ?Model $viewable = null): PageView
    {
        $userAgent = (string) $request->userAgent();

        return PageView::create([
            'viewable_type' => $viewable?->getMorphClass(),
            'viewable_id' => $viewable?->getKey(),
            'url' => $request->fullUrl(),
            'ip_hash' => $this->hashIp($request->ip()),
            'user_agent' => $userAgent,
            'browser' => $this->detectBrowser($userAgent),
            'device' => $this->detectDevice($userAgent),
            'referer' => $request->headers->get('referer'),
            'viewed_at' => now(),
        ]);
    }

    private function hashIp(?string $ip): ?string
    {
        if (blank($ip)) {
            return null;
        }

        $key = config('app.key') ?: config('app.name', 'chapung-art');

        return hash_hmac('sha256', $ip, $key);
    }

    private function detectBrowser(string $userAgent): string
    {
        return match (true) {
            str_contains($userAgent, 'Edg/') => 'Edge',
            str_contains($userAgent, 'Chrome/') => 'Chrome',
            str_contains($userAgent, 'Firefox/') => 'Firefox',
            str_contains($userAgent, 'Safari/') => 'Safari',
            default => 'Unknown',
        };
    }

    private function detectDevice(string $userAgent): string
    {
        return match (true) {
            str_contains($userAgent, 'Mobile') || str_contains($userAgent, 'Android') || str_contains($userAgent, 'iPhone') => 'Mobile',
            str_contains($userAgent, 'Tablet') || str_contains($userAgent, 'iPad') => 'Tablet',
            default => 'Desktop',
        };
    }
}
