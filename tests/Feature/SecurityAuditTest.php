<?php

use App\Models\Artist;
use App\Models\Artwork;
use App\Models\Certificate;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Post;
use App\Models\User;
use App\Support\UploadSecurity;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

test('guest cannot open admin panel', function () {
    $this->get('/admin')->assertRedirect('/admin/login');
});

test('admin user without resource permission cannot access protected resource', function () {
    app(PermissionRegistrar::class)->forgetCachedPermissions();

    $role = Role::create(['name' => 'Curator', 'guard_name' => 'web']);
    $user = User::factory()->create();
    $user->assignRole($role);

    $this->actingAs($user)
        ->get('/admin/customers')
        ->assertForbidden();
});

test('dangerous upload is rejected by image upload security rules', function () {
    $file = UploadedFile::fake()->create('shell.php', 1, 'application/x-php');

    $validator = Validator::make(['file' => $file], [
        'file' => UploadSecurity::imageRules(),
    ]);

    expect($validator->fails())->toBeTrue();
});

test('oversized upload is rejected by image upload security rules', function () {
    $file = UploadedFile::fake()
        ->image('large.jpg')
        ->size(UploadSecurity::IMAGE_MAX_KB + 1);

    $validator = Validator::make(['file' => $file], [
        'file' => UploadSecurity::imageRules(),
    ]);

    expect($validator->fails())->toBeTrue();
});

test('rich html input is sanitized before storage', function () {
    $post = Post::create([
        'title' => 'Security Note',
        'slug' => 'security-note',
        'content' => '<p onclick="alert(1)">Safe</p><script>alert(1)</script><a href="javascript:alert(1)">bad</a>',
        'status' => 'draft',
    ]);

    expect($post->content)
        ->toContain('<p>Safe</p>')
        ->not->toContain('<script>')
        ->not->toContain('onclick')
        ->not->toContain('javascript:');
});

test('invalid certificate request is rejected safely', function () {
    $this->get('/certificates/verify/%3Cscript%3E')
        ->assertNotFound();
});

test('missing certificate request does not leak certificate data', function () {
    $artwork = Artwork::create([
        'title' => 'Private Artwork',
        'slug' => 'private-artwork',
    ]);

    Certificate::create([
        'artwork_id' => $artwork->id,
        'certificate_number' => 'CERT-PRIVATE-001',
        'owner_name' => 'Sensitive Collector',
        'is_verified' => true,
    ]);

    $this->get('/certificates/verify/CERT-MISSING-001')
        ->assertOk()
        ->assertDontSee('Sensitive Collector')
        ->assertDontSee('Private Artwork');
});

test('sensitive commerce endpoint does not leak data to guests', function () {
    $customer = Customer::create([
        'name' => 'Sensitive Customer',
        'email' => 'sensitive@example.test',
    ]);

    Order::create([
        'order_number' => 'ORDER-PRIVATE-001',
        'customer_id' => $customer->id,
        'customer_name' => 'Sensitive Customer',
        'customer_email' => 'sensitive@example.test',
        'grand_total' => 500000,
    ]);

    $this->get('/admin/orders')
        ->assertRedirect('/admin/login')
        ->assertDontSee('ORDER-PRIVATE-001')
        ->assertDontSee('sensitive@example.test');
});

test('sanitized artist and artwork html removes unsafe content', function () {
    $artist = Artist::create([
        'name' => 'Safe Artist',
        'slug' => 'safe-artist',
        'bio' => '<p>Bio</p><img src=x onerror=alert(1)>',
    ]);

    $artwork = Artwork::create([
        'title' => 'Safe Work',
        'slug' => 'safe-work',
        'description' => '<h2>Work</h2><iframe src="https://bad.test"></iframe>',
    ]);

    expect($artist->bio)->toBe('<p>Bio</p>')
        ->and($artwork->description)->toBe('<h2>Work</h2>');
});
