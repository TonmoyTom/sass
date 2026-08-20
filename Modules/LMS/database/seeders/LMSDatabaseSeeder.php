<?php

namespace Modules\LMS\Database\Seeders;

use Illuminate\Database\Seeder;

class LMSDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command?->info('LMSDatabaseSeeder START');

        $this->call(LmsPermissionSeeder::class);

        $this->command?->info('LmsPermissionSeeder DONE');
    }
}
