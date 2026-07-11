<?php

use App\Support\VideoEmbed;

test('youtube urls are normalized to no cookie embed urls', function (string $input) {
    expect(VideoEmbed::youtubeNoCookieUrl($input))->toBe('https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ');
})->with([
    'plain id' => 'dQw4w9WgXcQ',
    'watch url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
    'short url' => 'https://youtu.be/dQw4w9WgXcQ',
    'embed url' => 'https://www.youtube.com/embed/dQw4w9WgXcQ',
    'shorts url' => 'https://www.youtube.com/shorts/dQw4w9WgXcQ',
]);

test('non youtube urls are rejected for iframe embed', function () {
    expect(VideoEmbed::youtubeNoCookieUrl('https://example.com/watch?v=dQw4w9WgXcQ'))->toBeNull()
        ->and(VideoEmbed::youtubeNoCookieUrl('javascript:alert(1)'))->toBeNull()
        ->and(VideoEmbed::youtubeNoCookieUrl('not-a-youtube-video'))->toBeNull();
});
