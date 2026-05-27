<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

       $this->call([
            // Order matters!

            // 1. Roles & Permissions FIRST (needed before creating users)
            RolesAndPermissionsSeeder::class,

            // 2. Default super admin
            SuperAdminSeeder::class,

            // 3. Subscription plans (uncomment when created)
            // SubscriptionPlansSeeder::class,

            // 4. Settings (uncomment when created)
            // SettingsSeeder::class,
        ]);
    }
}
