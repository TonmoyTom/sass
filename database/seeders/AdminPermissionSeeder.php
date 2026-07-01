<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class AdminPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Permission cache clear koro SHURUTE
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // Dashboard
            'dashboard.view',

            // Tenants
            'tenants.view',
            'tenants.create',
            'tenants.edit',
            'tenants.delete',
            'tenants.suspend',
            'tenants.reactivate',
            'tenants.impersonate',

            // Sellers
            'sellers.view',
            'sellers.create',
            'sellers.edit',
            'sellers.delete',
            'sellers.approve',
            'sellers.reject',
            'sellers.suspend',

            // Module requests
            'module-requests.view',
            'module-requests.create',
            'module-requests.approve',
            'module-requests.reject',
            'module-requests.update-status',
            'module-requests.update-note',

            // Modules
            'modules.view',
            'modules.create',
            'modules.edit',
            'modules.delete',

            // Commissions
            'commissions.view',
            'commissions.approve',
            'commissions.reject',
            'commissions.approve-due',

            // Orders & Invoices
            'orders.view',
            'orders.invoice',

            // Withdraw requests
            'withdraw.view',
            'withdraw.approve',
            'withdraw.reject',

            // Reports
            'reports.view',
            'reports.revenue',
            'reports.tenants',
            'reports.sellers',

            // Roles & Permissions
            'roles.view',
            'roles.create',
            'roles.edit',
            'roles.delete',

            // Settings
            'settings.view',
            'settings.create',
            'settings.edit',
            'settings.delete',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        // Permission create korার POR abar cache clear koro (safety)
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Core roles
        $superAdmin = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
        $superAdmin->syncPermissions($permissions);

        // Staff: operational access, no roles/settings management
        $staff = Role::firstOrCreate(['name' => 'staff', 'guard_name' => 'web']);
        $staff->syncPermissions(array_filter(
            $permissions,
            fn ($p) => !str_starts_with($p, 'roles.') && !str_starts_with($p, 'settings.')
        ));
    }
}
