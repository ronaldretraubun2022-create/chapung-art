<?php

use App\Services\ImageUploadService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

test('image upload service stores safe image with unique name and thumbnail', function () {
    Storage::fake('public');

    $file = UploadedFile::fake()->image('papua-art.jpg', 1200, 800)->size(500);
    $path = app(ImageUploadService::class)->store($file, 'artworks');

    expect($path)
        ->toStartWith('artworks/')
        ->toEndWith('.jpg')
        ->not->toContain('papua-art');

    Storage::disk('public')->assertExists($path);
    Storage::disk('public')->assertExists(ImageUploadService::thumbnailPath($path));
});

test('image validation rejects executable upload', function () {
    $file = UploadedFile::fake()->create('shell.php', 1, 'image/jpeg');

    $validator = Validator::make(['file' => $file], [
        'file' => ImageUploadService::rules(),
    ]);

    expect($validator->fails())->toBeTrue();
});

test('image validation rejects invalid mime type', function () {
    $file = UploadedFile::fake()->create('payload.jpg', 1, 'application/x-msdownload');

    $validator = Validator::make(['file' => $file], [
        'file' => ImageUploadService::rules(),
    ]);

    expect($validator->fails())->toBeTrue();
});

test('image validation rejects oversized file', function () {
    $file = UploadedFile::fake()
        ->image('large.jpg')
        ->size(ImageUploadService::maxKilobytes() + 1);

    $validator = Validator::make(['file' => $file], [
        'file' => ImageUploadService::rules(),
    ]);

    expect($validator->fails())->toBeTrue();
});

test('image upload replace deletes old original and thumbnail', function () {
    Storage::fake('public');

    $service = app(ImageUploadService::class);
    $oldPath = $service->store(UploadedFile::fake()->image('old.jpg', 900, 600), 'artists');
    $oldThumbnail = ImageUploadService::thumbnailPath($oldPath);

    $newPath = $service->replace($oldPath, UploadedFile::fake()->image('new.png', 900, 600), 'artists');

    Storage::disk('public')->assertMissing($oldPath);
    Storage::disk('public')->assertMissing($oldThumbnail);
    Storage::disk('public')->assertExists($newPath);
    Storage::disk('public')->assertExists(ImageUploadService::thumbnailPath($newPath));
});

test('image upload service throws validation exception for disguised executable content', function () {
    $file = UploadedFile::fake()->createWithContent('payload.png', "<?php echo 'bad';", 'image/png');

    expect(fn () => app(ImageUploadService::class)->store($file, 'unsafe'))
        ->toThrow(ValidationException::class);
});

test('image upload storage failure is converted to validation error', function () {
    $blockedRoot = storage_path('framework/testing/disks/not-a-directory');
    File::ensureDirectoryExists(dirname($blockedRoot));
    File::put($blockedRoot, 'blocked');

    config([
        'filesystems.disks.broken_upload' => [
            'driver' => 'local',
            'root' => $blockedRoot,
            'throw' => false,
        ],
    ]);

    $file = UploadedFile::fake()->image('valid.jpg', 600, 400)->size(200);

    expect(fn () => app(ImageUploadService::class)->store($file, 'artworks', 'broken_upload'))
        ->toThrow(ValidationException::class);

    File::delete($blockedRoot);
});
