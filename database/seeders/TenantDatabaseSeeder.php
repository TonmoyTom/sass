<?php

namespace Database\Seeders;

use App\Models\CompanySetting;
use App\Models\SiteSetting;
use Illuminate\Database\Seeder;

class TenantDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(TenantRolePermissionSeeder::class);
    }
}
