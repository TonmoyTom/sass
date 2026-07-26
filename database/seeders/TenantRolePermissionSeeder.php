<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class TenantRolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // Dashboard
            'dashboard.view',
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',

            // Roles & Permissions
            'roles.view',
            'roles.create',
            'roles.edit',
            'roles.delete',

            // Company Settings
            'settings.view',
            'settings.edit',

            // Site Settings (SEO)
            'site-settings.view',
            'site-settings.create',
            'site-settings.edit',
            'site-settings.delete',

            'my-modules.view',
            'my-modules.invoice',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'tenant',
            ]);
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Owner — full access (tenant creator/subscriber)
        $owner = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'tenant']);
        $owner->syncPermissions($permissions);
        $staff = Role::firstOrCreate(['name' => 'staff', 'guard_name' => 'tenant']);
        $staff->syncPermissions(array_filter(
            $permissions,
            fn ($p) => ! str_starts_with($p, 'roles.') && ! str_starts_with($p, 'settings.') && ! str_starts_with($p, 'site-settings.')
        ));
    }
}
