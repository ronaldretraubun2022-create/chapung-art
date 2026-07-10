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
        Role::findByName('Administrator', 'web')->syncPermissions($this->administratorPermissions());
        Role::findByName('Curator', 'web')->syncPermissions($this->curatorPermissions());
        Role::findByName('Artist', 'web')->syncPermissions($this->artistPermissions());
        Role::findByName('Photographer', 'web')->syncPermissions($this->photographerPermissions());
        Role::findByName('Journalist', 'web')->syncPermissions($this->journalistPermissions());
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
    private function curatorPermissions(): array
    {
        return $this->permissionsFor([
            'artist',
            'artwork',
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
    private function artistPermissions(): array
    {
        return $this->permissionsFor([
            'artwork',
            'certificate',
            'collection',
            'media_item',
            'tag',
        ]);
    }

    /**
     * @return array<int, string>
     */
    private function photographerPermissions(): array
    {
        return $this->permissionsFor([
            'collection',
            'media_item',
            'photography',
            'tag',
        ]);
    }

    /**
     * @return array<int, string>
     */
    private function journalistPermissions(): array
    {
        return $this->permissionsFor([
            'category',
            'media_item',
            'post',
            'tag',
        ]);
    }

    /**
     * @param  array<int, string>  $resources
     * @return array<int, string>
     */
    private function permissionsFor(array $resources): array
    {
        $permissions = [];

        foreach ($resources as $resource) {
            foreach (self::ACTIONS as $action) {
                $permissions[] = $action.' '.$resource;
            }
        }

        return $permissions;
    }
}
