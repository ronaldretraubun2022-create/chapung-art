<?php

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;

function startPublicWebServer(): array
{
    $port = random_int(9100, 9999);
    $process = new Process([PHP_BINARY, '-S', "127.0.0.1:{$port}", '-t', public_path()], base_path());
    $process->start();

    $started = false;

    for ($attempt = 0; $attempt < 30; $attempt++) {
        $connection = @fsockopen('127.0.0.1', $port, $errorCode, $errorMessage, 0.1);

        if ($connection !== false) {
            fclose($connection);
            $started = true;
            break;
        }

        usleep(100000);
    }

    expect($started)->toBeTrue();

    return [$process, "http://127.0.0.1:{$port}"];
}

function httpGetStatus(string $url): int
{
    $context = stream_context_create([
        'http' => [
            'ignore_errors' => true,
            'timeout' => 5,
        ],
    ]);

    @file_get_contents($url, false, $context);

    $statusLine = $http_response_header[0] ?? '';

    preg_match('/\s(\d{3})\s/', $statusLine, $matches);

    return (int) ($matches[1] ?? 0);
}

function setApplicationEnvironment(string $environment, string $debug): void
{
    putenv("APP_ENV={$environment}");
    putenv("APP_DEBUG={$debug}");

    $_ENV['APP_ENV'] = $environment;
    $_ENV['APP_DEBUG'] = $debug;
    $_SERVER['APP_ENV'] = $environment;
    $_SERVER['APP_DEBUG'] = $debug;
}

test('core public laravel routes return successful responses', function (string $uri) {
    $this->get($uri)->assertOk();
})->with([
    '/',
    '/gallery',
    '/photography',
    '/media',
    '/sitemap.xml',
]);

test('robots file returns successful response from the public web root', function () {
    [$server, $baseUrl] = startPublicWebServer();

    try {
        expect(httpGetStatus("{$baseUrl}/robots.txt"))->toBe(200);
    } finally {
        $server->stop();
    }
});

test('robots file exists and is not empty', function () {
    expect(public_path('robots.txt'))->toBeFile()
        ->and(File::get(public_path('robots.txt')))->not->toBeEmpty();
});

test('admin panel redirects guests to the admin login screen', function () {
    $this->get('/admin')->assertRedirect('/admin/login');
});

test('registration route is not registered in production', function () {
    setApplicationEnvironment('production', 'false');
    $this->refreshApplication();

    try {
        $this->get('/register')->assertNotFound();
    } finally {
        setApplicationEnvironment('testing', 'true');
        $this->refreshApplication();
    }
});

test('public storage image url works only when the file exists', function () {
    Storage::disk('public')->put('test/security-image.png', 'image-content');
    [$server, $baseUrl] = startPublicWebServer();

    try {
        expect(httpGetStatus("{$baseUrl}/storage/test/security-image.png"))->toBe(200);

        Storage::disk('public')->delete('test/security-image.png');

        expect(httpGetStatus("{$baseUrl}/storage/test/security-image.png"))->toBe(404);
    } finally {
        Storage::disk('public')->delete('test/security-image.png');
        $server->stop();
    }
});

test('production environment example disables debug mode', function () {
    $env = parse_ini_file(base_path('.env.example'), false, INI_SCANNER_RAW);

    expect(strtolower((string) ($env['APP_ENV'] ?? null)))->toBe('production')
        ->and(strtolower((string) ($env['APP_DEBUG'] ?? null)))->toBe('false');
});
