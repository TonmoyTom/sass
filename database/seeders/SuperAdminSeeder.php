<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\UserStatus;
use App\Enums\UserType;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * SuperAdminSeeder
 *
 * Creates the default super admin account.
 *
 * Default credentials (CHANGE AFTER FIRST LOGIN):
 *   Email:    admin@edusaas.com
 *   Password: password (or set via .env)
 *
 * Run: php artisan db:seed --class=SuperAdminSeeder
 */
class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('SUPER_ADMIN_EMAIL', 'admin@edusaas.com');
        $password = env('SUPER_ADMIN_PASSWORD', 'password');
        $name = env('SUPER_ADMIN_NAME', 'Super Admin');

        // Create or update super admin
        $admin = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make($password),
                'email_verified_at' => now(),
                'user_type' => UserType::SUPER_ADMIN,
                'status' => UserStatus::ACTIVE,
            ]
        );

        // Assign super admin role
        if (!$admin->hasRole('super_admin')) {
            $admin->assignRole('super_admin');
        }

        $this->command->info('');
        $this->command->info('╔════════════════════════════════════════════╗');
        $this->command->info('║   ✅ Super Admin Created Successfully     ║');
        $this->command->info('╠════════════════════════════════════════════╣');
        $this->command->info('║   Email:    ' . str_pad($email, 28) . '║');
        $this->command->info('║   Password: ' . str_pad($password, 28) . '║');
        $this->command->info('║                                            ║');
        $this->command->info('║   ⚠️  CHANGE PASSWORD AFTER FIRST LOGIN!   ║');
        $this->command->info('╚════════════════════════════════════════════╝');
        $this->command->info('');
    }
}
