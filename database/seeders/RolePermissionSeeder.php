<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    private const ROLES = [
        'Super Admin',
        'Pengelola Karya',
        'Pengelola Transaksi',
        'Pengelola Konten',
        'Operator Viewer',
        'Administrator',
        'Curator',
        'Artist',
        'Photographer',
        'Journalist',
        'Customer',
    ];

    private const RESOURCES = [
        'activity_log',
        'admin_notification',
        'artist',
        'artwork',
        'artwork_review',
        'backup',
        'category',
        'certificate',
        'collection',
        'customer',
        'exhibition',
        'homepage_section',
        'media_item',
        'order',
        'page_view',
        'payment',
        'permission',
        'photography',
        'post',
        'report',
        'role',
        'seo_meta',
        'shipment',
        'site_setting',
        'tag',
        'user',
    ];

    private const ACTIONS = [
        'view_any',
        'view',
        'create',
        'update',
        'delete',
    ];

    /**
     * Seed the application's roles and permissions.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach ($this->permissionNames() as $permissionName) {
            Permission::findOrCreate($permissionName, 'web');
        }

        foreach (self::ROLES as $roleName) {
            Role::findOrCreate($roleName, 'web');
        }

        Role::findByName('Super Admin', 'web')->syncPermissions(Permission::all());
        Role::findByName('Pengelola Karya', 'web')->syncPermissions($this->artworkManagerPermissions());
        Role::findByName('Pengelola Transaksi', 'web')->syncPermissions($this->transactionManagerPermissions());
        Role::findByName('Pengelola Konten', 'web')->syncPermissions($this->contentManagerPermissions());
        Role::findByName('Operator Viewer', 'web')->syncPermissions($this->viewerPermissions());
        Role::findByName('Administrator', 'web')->syncPermissions($this->administratorPermissions());
        Role::findByName('Curator', 'web')->syncPermissions($this->artworkManagerPermissions());
        Role::findByName('Artist', 'web')->syncPermissions($this->legacyArtistPermissions());
        Role::findByName('Photographer', 'web')->syncPermissions($this->legacyPhotographerPermissions());
        Role::findByName('Journalist', 'web')->syncPermissions($this->contentManagerPermissions());
        Role::findByName('Customer', 'web')->syncPermissions([]);

        $adminEmails = collect(config('chapung.admin_emails', []));

        if ($adminEmails->isNotEmpty()) {
            User::query()
                ->whereIn('email', $adminEmails)
                ->whereDoesntHave('roles')
                ->each(fn (User $user): mixed => $user->assignRole('Super Admin'));
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * @return array<int, string>
     */
    private function permissionNames(): array
    {
        $permissions = [];

        foreach (self::RESOURCES as $resource) {
            foreach (self::ACTIONS as $action) {
                $permissions[] = $action.' '.$resource;
            }
        }

        return $permissions;
    }

    /**
     * @return array<int, string>
     */
    private function administratorPermissions(): array
    {
        return collect($this->permissionNames())
            ->reject(fn (string $permission): bool => str_ends_with($permission, ' role') || str_ends_with($permission, ' permission'))
            ->values()
            ->all();
    }

    /**
     * @return array<int, string>
     */
    private function artworkManagerPermissions(): array
    {
        return $this->permissionsFor([
            'artist',
            'artwork',
            'artwork_review',
            'category',
            'certificate',
            'collection',
            'exhibition',
            'media_item',
            'photography',
            'tag',
        ]);
    }

    /**
     * @return array<int, string>
     */
    private function transactionManagerPermissions(): array
    {
        return $this->permissionsFor([
            'certificate',
            'customer',
            'order',
            'payment',
            'report',
            'shipment',
        ]);
    }

    /**
     * @return array<int, string>
     */
    private function contentManagerPermissions(): array
    {
        return $this->permissionsFor([
            'category',
            'exhibition',
            'homepage_section',
            'media_item',
            'post',
            'seo_meta',
            'tag',
        ]);
    }

    /**
     * @return array<int, string>
     */
    private function viewerPermissions(): array
    {
        return $this->permissionsFor([
            'activity_log',
            'artist',
            'artwork',
            'artwork_review',
            'category',
            'certificate',
            'collection',
            'customer',
            'exhibition',
            'homepage_section',
            'media_item',
            'order',
            'page_view',
            'payment',
            'photography',
            'post',
            'report',
            'seo_meta',
            'shipment',
            'site_setting',
            'tag',
        ], ['view_any', 'view']);
    }

    /**
     * @return array<int, string>
     */
    private function legacyArtistPermissions(): array
    {
        return $this->permissionsFor([
            'artwork',
            'artwork_review',
            'certificate',
            'collection',
            'media_item',
            'tag',
        ]);
    }

    /**
     * @return array<int, string>
     */
    private function legacyPhotographerPermissions(): array
    {
        return $this->permissionsFor([
            'collection',
            'media_item',
            'photography',
            'tag',
        ]);
    }

    /**
     * @param  array<int, string>  $resources
     * @param  array<int, string>  $actions
     * @return array<int, string>
     */
    private function permissionsFor(array $resources, array $actions = self::ACTIONS): array
    {
        $permissions = [];

        foreach ($resources as $resource) {
            foreach ($actions as $action) {
                $permissions[] = $action.' '.$resource;
            }
        }

        return $permissions;
    }
}
