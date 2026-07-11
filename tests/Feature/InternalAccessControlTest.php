<?php

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

function seedInternalRoles(object $testCase): void
{
    app(PermissionRegistrar::class)->forgetCachedPermissions();
    $testCase->seed(RolePermissionSeeder::class);
    app(PermissionRegistrar::class)->forgetCachedPermissions();
}

test('role seeder provides five internal access roles plus customer boundary', function () {
    seedInternalRoles($this);

    expect(Role::query()->whereIn('name', [
        'Super Admin',
        'Pengelola Karya',
        'Pengelola Transaksi',
        'Pengelola Konten',
        'Operator Viewer',
        'Customer',
    ])->count())->toBe(6)
        ->and(Role::findByName('Pengelola Karya')->hasPermissionTo('update artwork'))->toBeTrue()
        ->and(Role::findByName('Pengelola Transaksi')->hasPermissionTo('view_any order'))->toBeTrue()
        ->and(Role::findByName('Pengelola Konten')->hasPermissionTo('update post'))->toBeTrue()
        ->and(Role::findByName('Operator Viewer')->hasPermissionTo('view_any artwork'))->toBeTrue()
        ->and(Role::findByName('Operator Viewer')->hasPermissionTo('update artwork'))->toBeFalse()
        ->and(Role::findByName('Customer')->permissions)->toHaveCount(0);
});

test('admin panel access requires configured internal email and internal role', function () {
    seedInternalRoles($this);

    config(['chapung.admin_emails' => ['content@chapungart.test']]);

    $allowed = User::factory()->create(['email' => 'content@chapungart.test']);
    $allowed->assignRole('Pengelola Konten');

    $blocked = User::factory()->create(['email' => 'outsider@chapungart.test']);
    $blocked->assignRole('Pengelola Konten');

    $this->actingAs($allowed)
        ->get('/admin/posts')
        ->assertOk();

    $this->actingAs($blocked)
        ->get('/admin/posts')
        ->assertForbidden();
});

test('least privilege prevents transaction manager from opening content resources', function () {
    seedInternalRoles($this);

    config(['chapung.admin_emails' => ['finance@chapungart.test']]);

    $user = User::factory()->create(['email' => 'finance@chapungart.test']);
    $user->assignRole('Pengelola Transaksi');

    $this->actingAs($user)
        ->get('/admin/orders')
        ->assertOk();

    $this->actingAs($user)
        ->get('/admin/posts')
        ->assertForbidden();
});

test('public registration creates customer boundary instead of internal access', function () {
    seedInternalRoles($this);

    $this->post('/register', [
        'name' => 'Public Collector',
        'email' => 'collector@example.test',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ])->assertRedirect(route('dashboard', absolute: false));

    $user = User::query()->where('email', 'collector@example.test')->firstOrFail();

    expect($user->hasRole('Customer'))->toBeTrue()
        ->and($user->hasAnyRole(['Pengelola Karya', 'Pengelola Transaksi', 'Pengelola Konten', 'Operator Viewer']))->toBeFalse();

    $this->get('/admin')->assertForbidden();
});

test('configured admin emails are capped to five internal managers', function () {
    expect(count(config('chapung.admin_emails', [])))->toBeLessThanOrEqual(5);
});
