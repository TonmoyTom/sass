<?php

declare(strict_types=1);

namespace App\Actions\Modules;

use App\Models\Tenant;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Nwidart\Modules\Facades\Module;

/**
 * InstallModuleAction
 *
 * Installs a module for a specific tenant.
 * Runs migrations on tenant DB, registers module activation, seeds data.
 *
 * Usage:
 * app(InstallModuleAction::class)->execute(
 *     tenant: $tenant,
 *     moduleAlias: 'school',
 *     billingCycle: 'monthly',
 *     pricePaid: 1500.00
 * );
 */
class InstallModuleAction
{
    /**
     * Execute the action.
     *
     * @param  Tenant  $tenant
     * @param  string  $moduleAlias  Module alias (e.g., 'school', 'certificate_generator')
     * @param  string  $billingCycle  'monthly', 'yearly', or 'one_time'
     * @param  float  $pricePaid
     * @return void
     */
    public function execute(
        Tenant $tenant,
        string $moduleAlias,
        string $billingCycle,
        float $pricePaid = 0
    ): void {
        // 1. Verify module exists in registry
        $module = $this->findModule($moduleAlias);

        // 2. Check dependencies
        $this->checkDependencies($tenant, $module);

        // 3. Verify not already installed
        $this->checkNotInstalled($tenant, $module);

        DB::transaction(function () use ($tenant, $module, $billingCycle, $pricePaid) {
            // 4. Run migrations on tenant DB
            $this->runMigrationsOnTenant($tenant, $module);

            // 5. Run seeders if available
            $this->runSeeders($tenant, $module);

            // 6. Create tenant_modules record
            $this->registerActivation($tenant, $module, $billingCycle, $pricePaid);

            Log::info('Module installed for tenant', [
                'tenant_id' => $tenant->id,
                'module' => $module->alias,
                'billing_cycle' => $billingCycle,
            ]);
        });
    }

    /**
     * Find the module record.
     */
    protected function findModule(string $alias)
    {
        $moduleClass = '\App\Models\Module';

        if (!class_exists($moduleClass)) {
            throw new \Exception('Module model not found. Create app/Models/Module.php');
        }

        $module = $moduleClass::where('alias', $alias)->first();

        if (!$module) {
            throw new \Exception("Module '{$alias}' not found in registry");
        }

        return $module;
    }

    /**
     * Check if all dependencies are installed.
     */
    protected function checkDependencies(Tenant $tenant, $module): void
    {
        $dependencies = $module->dependencies ?? [];

        if (empty($dependencies)) {
            return;
        }

        foreach ($dependencies as $depAlias) {
            $hasDep = DB::table('tenant_modules')
                ->where('tenant_id', $tenant->id)
                ->whereIn('status', ['active', 'purchased'])
                ->whereExists(function ($query) use ($depAlias) {
                    $query->select(DB::raw(1))
                        ->from('modules')
                        ->whereColumn('modules.id', 'tenant_modules.module_id')
                        ->where('modules.alias', $depAlias);
                })
                ->exists();

            if (!$hasDep) {
                throw new \Exception(
                    "Module '{$module->alias}' requires '{$depAlias}' to be installed first"
                );
            }
        }
    }

    /**
     * Check that module is not already installed.
     */
    protected function checkNotInstalled(Tenant $tenant, $module): void
    {
        $exists = DB::table('tenant_modules')
            ->where('tenant_id', $tenant->id)
            ->where('module_id', $module->id)
            ->whereIn('status', ['active', 'purchased', 'trial'])
            ->exists();

        if ($exists) {
            throw new \Exception("Module '{$module->alias}' already installed for this tenant");
        }
    }

    /**
     * Run module migrations on tenant DB.
     */
    protected function runMigrationsOnTenant(Tenant $tenant, $module): void
    {
        $moduleName = $this->aliasToModuleName($module->alias);
        $moduleInstance = Module::find($moduleName);

        if (!$moduleInstance) {
            throw new \Exception("Module folder '{$moduleName}' not found");
        }

        $migrationPath = "Modules/{$moduleName}/Database/Migrations";

        // Run migrations within tenant context
        $tenant->run(function () use ($migrationPath) {
            Artisan::call('migrate', [
                '--path' => $migrationPath,
                '--force' => true,
            ]);
        });
    }

    /**
     * Run module seeders on tenant DB.
     */
    protected function runSeeders(Tenant $tenant, $module): void
    {
        $moduleName = $this->aliasToModuleName($module->alias);
        $seederClass = "Modules\\{$moduleName}\\Database\\Seeders\\{$moduleName}DatabaseSeeder";

        if (!class_exists($seederClass)) {
            return; // No seeder, skip
        }

        $tenant->run(function () use ($seederClass) {
            Artisan::call('db:seed', [
                '--class' => $seederClass,
                '--force' => true,
            ]);
        });
    }

    /**
     * Create tenant_modules record.
     */
    protected function registerActivation(
        Tenant $tenant,
        $module,
        string $billingCycle,
        float $pricePaid
    ): void {
        $isOneTime = $billingCycle === 'one_time';

        DB::table('tenant_modules')->insert([
            'tenant_id' => $tenant->id,
            'module_id' => $module->id,
            'status' => $isOneTime ? 'purchased' : 'active',
            'access_type' => $isOneTime ? 'lifetime' : 'subscription',
            'activated_at' => now(),
            'purchased_at' => $isOneTime ? now() : null,
            'expires_at' => $isOneTime ? null : $this->calculateExpiry($billingCycle),
            'price_paid' => $pricePaid,
            'billing_cycle' => $billingCycle,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Calculate expiry date based on billing cycle.
     */
    protected function calculateExpiry(string $billingCycle): ?\DateTime
    {
        return match ($billingCycle) {
            'monthly' => now()->addMonth(),
            'yearly' => now()->addYear(),
            default => null,
        };
    }

    /**
     * Convert module alias to PascalCase folder name.
     * e.g., 'certificate_generator' → 'CertificateGenerator'
     */
    protected function aliasToModuleName(string $alias): string
    {
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $alias)));
    }
}
