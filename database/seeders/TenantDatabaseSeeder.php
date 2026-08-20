<?php

namespace Database\Seeders;

use App\Models\CompanySetting;
use App\Models\SiteSetting;
use Illuminate\Database\Seeder;
use Modules\LMS\Database\Seeders\LMSDatabaseSeeder;

class TenantDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->command?->info('TenantDatabaseSeeder START');
        $this->call(TenantRolePermissionSeeder::class);
        $this->call(LMSDatabaseSeeder::class);
        $this->command?->info('TenantDatabaseSeeder DONE');
    }
}
