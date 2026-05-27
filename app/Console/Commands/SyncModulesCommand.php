<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * SyncModulesCommand
 *
 * Scans Modules/ folder and syncs all module.json files
 * to the central modules table.
 *
 * Usage:
 * php artisan platform:modules:sync
 * php artisan platform:modules:sync --force  (overwrites pricing too)
 */
class SyncModulesCommand extends Command
{
    protected $signature = 'platform:modules:sync {--force : Overwrite pricing and rates from module.json}';

    protected $description = 'Sync modules from folder to central database';

    public function handle(): int
    {
        $this->info('🔄 Syncing modules from folder to database...');

        $modulesPath = base_path('Modules');

        if (!File::isDirectory($modulesPath)) {
            $this->warn('Modules directory does not exist: ' . $modulesPath);
            return self::SUCCESS;
        }

        $directories = File::directories($modulesPath);
        $synced = 0;
        $created = 0;
        $updated = 0;

        foreach ($directories as $dir) {
            $moduleJson = $dir . DIRECTORY_SEPARATOR . 'module.json';

            if (!File::exists($moduleJson)) {
                $this->warn('No module.json in: ' . basename($dir));
                continue;
            }

            $manifest = json_decode(File::get($moduleJson), true);

            if (!$manifest || !isset($manifest['alias'])) {
                $this->error('Invalid module.json in: ' . basename($dir));
                continue;
            }

            $result = $this->syncModule($manifest);

            if ($result === 'created') $created++;
            if ($result === 'updated') $updated++;
            $synced++;
        }

        $this->info("✅ Synced {$synced} modules ({$created} new, {$updated} updated)");

        // Mark missing modules as inactive
        $this->markMissingAsInactive($directories);

        return self::SUCCESS;
    }

    /**
     * Sync a single module manifest to DB.
     */
    protected function syncModule(array $manifest): string
    {
        $alias = $manifest['alias'];
        $force = $this->option('force');

        $existing = DB::table('modules')->where('alias', $alias)->first();

        $data = [
            'name' => $manifest['name'] ?? Str::studly($alias),
            'alias' => $alias,
            'description' => $manifest['description'] ?? null,
            'icon' => $manifest['icon'] ?? 'box',
            'version' => $manifest['version'] ?? '1.0.0',
            'pricing_type' => $manifest['pricing_type'] ?? 'subscription',
            'module_category' => $manifest['module_category'] ?? 'core',
            'is_active' => true,
            'is_core' => $manifest['is_core'] ?? false,
            'features' => isset($manifest['features']) ? json_encode($manifest['features']) : null,
            'dependencies' => isset($manifest['dependencies']) ? json_encode($manifest['dependencies']) : null,
            'updated_at' => now(),
        ];

        // Pricing fields (only set on create or with --force)
        if (!$existing || $force) {
            $pricing = $manifest['pricing'] ?? [];
            $data['monthly_price'] = $pricing['monthly'] ?? 0;
            $data['yearly_price'] = $pricing['yearly'] ?? 0;
            $data['one_time_price'] = $pricing['one_time'] ?? null;
            $data['commission_rate'] = $manifest['commission']['rate'] ?? 70.00;
        }

        if ($existing) {
            DB::table('modules')->where('id', $existing->id)->update($data);
            $this->line("  ↻ Updated: <fg=cyan>{$alias}</>");
            return 'updated';
        }

        $data['created_at'] = now();
        DB::table('modules')->insert($data);
        $this->line("  + Created: <fg=green>{$alias}</>");
        return 'created';
    }

    /**
     * Mark modules as inactive if their folder is missing.
     */
    protected function markMissingAsInactive(array $directories): void
    {
        $existingAliases = collect($directories)
            ->map(function ($dir) {
                $json = $dir . DIRECTORY_SEPARATOR . 'module.json';
                if (!File::exists($json)) return null;
                $manifest = json_decode(File::get($json), true);
                return $manifest['alias'] ?? null;
            })
            ->filter()
            ->values()
            ->all();

        if (empty($existingAliases)) {
            return;
        }

        $marked = DB::table('modules')
            ->whereNotIn('alias', $existingAliases)
            ->where('is_active', true)
            ->update(['is_active' => false, 'updated_at' => now()]);

        if ($marked > 0) {
            $this->warn("⚠️  Marked {$marked} missing modules as inactive");
        }
    }
}
