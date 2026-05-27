<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * RolesAndPermissionsSeeder
 *
 * Seeds the platform's roles and permissions.
 * Run this once after migrations.
 *
 * Run: php artisan db:seed --class=RolesAndPermissionsSeeder
 */
class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        DB::transaction(function () {
            $this->createPermissions();
            $this->createRoles();
            $this->assignPermissionsToRoles();
        });

        $this->command->info('✅ Roles and permissions seeded successfully.');
    }

    /**
     * Define all permissions.
     */
    protected function createPermissions(): void
    {
        $permissions = [
            // Tenant management (Super Admin)
            'tenants.view',
            'tenants.create',
            'tenants.edit',
            'tenants.suspend',
            'tenants.delete',
            'tenants.impersonate',

            // Seller management (Super Admin)
            'sellers.view',
            'sellers.approve',
            'sellers.suspend',
            'sellers.delete',
            'sellers.view_earnings',

            // Module management (Super Admin)
            'modules.view',
            'modules.edit_pricing',
            'modules.toggle_status',
            'modules.sync',

            // Subscription plans (Super Admin)
            'plans.view',
            'plans.create',
            'plans.edit',
            'plans.delete',

            // Payment management (Super Admin)
            'payments.view',
            'payments.refund',
            'invoices.view',
            'invoices.download',

            // Withdrawal management (Super Admin)
            'withdrawals.view',
            'withdrawals.approve',
            'withdrawals.reject',
            'withdrawals.process',

            // Reports & Analytics (Super Admin)
            'reports.view',
            'reports.export',

            // Settings (Super Admin)
            'settings.view',
            'settings.edit',
            'settings.payment_gateways',
            'settings.commission_rates',

            // Seller permissions
            'seller.dashboard',
            'seller.referrals.view',
            'seller.commissions.view',
            'seller.wallet.view',
            'seller.withdrawals.request',
            'seller.profile.edit',

            // Tenant owner permissions
            'tenant_owner.dashboard',
            'tenant_owner.subscription.view',
            'tenant_owner.subscription.upgrade',
            'tenant_owner.modules.view',
            'tenant_owner.modules.install',
            'tenant_owner.modules.uninstall',
            'tenant_owner.users.manage',
            'tenant_owner.settings.edit',
            'tenant_owner.billing.view',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        $this->command->info("✓ Created " . count($permissions) . " permissions");
    }

    /**
     * Define all roles.
     */
    protected function createRoles(): void
    {
        $roles = [
            'super_admin' => 'Full platform access',
            'admin' => 'Limited admin access',
            'seller' => 'Seller/Affiliate user',
            'tenant_owner' => 'School owner (tenant)',
        ];

        foreach ($roles as $name => $description) {
            Role::firstOrCreate([
                'name' => $name,
                'guard_name' => 'web',
            ]);
        }

        $this->command->info("✓ Created " . count($roles) . " roles");
    }

    /**
     * Assign permissions to each role.
     */
    protected function assignPermissionsToRoles(): void
    {
        // ─── SUPER ADMIN: All permissions ───
        $superAdmin = Role::findByName('super_admin');
        $superAdmin->syncPermissions(Permission::all());

        // ─── ADMIN: Limited permissions ───
        $admin = Role::findByName('admin');
        $admin->syncPermissions([
            'tenants.view',
            'tenants.edit',
            'sellers.view',
            'modules.view',
            'plans.view',
            'payments.view',
            'invoices.view',
            'withdrawals.view',
            'reports.view',
        ]);

        // ─── SELLER: Seller permissions ───
        $seller = Role::findByName('seller');
        $seller->syncPermissions([
            'seller.dashboard',
            'seller.referrals.view',
            'seller.commissions.view',
            'seller.wallet.view',
            'seller.withdrawals.request',
            'seller.profile.edit',
        ]);

        // ─── TENANT OWNER: Tenant owner permissions ───
        $tenantOwner = Role::findByName('tenant_owner');
        $tenantOwner->syncPermissions([
            'tenant_owner.dashboard',
            'tenant_owner.subscription.view',
            'tenant_owner.subscription.upgrade',
            'tenant_owner.modules.view',
            'tenant_owner.modules.install',
            'tenant_owner.modules.uninstall',
            'tenant_owner.users.manage',
            'tenant_owner.settings.edit',
            'tenant_owner.billing.view',
        ]);

        $this->command->info("✓ Assigned permissions to roles");
    }
}
