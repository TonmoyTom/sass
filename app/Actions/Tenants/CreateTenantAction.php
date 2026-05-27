<?php

declare(strict_types=1);

namespace App\Actions\Tenants;

use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * CreateTenantAction
 *
 * Single-purpose action to create a new tenant (school).
 * Handles: tenant creation, database creation, migrations, default data, owner user.
 *
 * Usage:
 * $tenant = app(CreateTenantAction::class)->execute([
 *     'name' => 'ABC High School',
 *     'subdomain' => 'abchigh',
 *     'email' => 'admin@abchigh.com',
 *     'password' => 'secret123',
 *     'owner_name' => 'John Doe',
 *     'phone' => '01700000000',
 * ]);
 */
class CreateTenantAction
{
    /**
     * Execute the action.
     *
     * @param  array  $data  Tenant data
     * @return Tenant The created tenant
     */
    public function execute(array $data): Tenant
    {
        return DB::transaction(function () use ($data) {
            // 1. Validate subdomain availability
            $subdomain = $this->normalizeSubdomain($data['subdomain']);
            $this->validateSubdomain($subdomain);

            // 2. Create central user (owner)
            $owner = $this->createOwner($data);

            // 3. Create the tenant record
            $tenant = $this->createTenant($subdomain, $owner, $data);

            // 4. Create domain
            $this->createDomain($tenant, $subdomain);

            // 5. Tenancy events will trigger:
            //    - Database creation (CreateDatabase job)
            //    - Migrations (MigrateDatabase job)
            //    - Optional: Seeding

            Log::info('Tenant created successfully', [
                'tenant_id' => $tenant->id,
                'subdomain' => $subdomain,
                'owner_id' => $owner->id,
            ]);

            return $tenant->fresh();
        });
    }

    /**
     * Normalize subdomain (lowercase, alphanumeric).
     */
    protected function normalizeSubdomain(string $subdomain): string
    {
        return Str::lower(Str::slug($subdomain));
    }

    /**
     * Validate subdomain is allowed.
     */
    protected function validateSubdomain(string $subdomain): void
    {
        $reserved = ['www', 'admin', 'api', 'mail', 'app', 'blog', 'help', 'support'];

        if (in_array($subdomain, $reserved)) {
            throw new \Exception("Subdomain '{$subdomain}' is reserved");
        }

        if (strlen($subdomain) < 3 || strlen($subdomain) > 50) {
            throw new \Exception('Subdomain must be 3-50 characters');
        }

        // Check uniqueness
        if (Tenant::where('id', $subdomain)->exists()) {
            throw new \Exception("Subdomain '{$subdomain}' already taken");
        }
    }

    /**
     * Create the owner user in central DB.
     */
    protected function createOwner(array $data)
    {
        $userClass = config('auth.providers.users.model', \App\Models\User::class);

        return $userClass::create([
            'name' => $data['owner_name'] ?? $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'email_verified_at' => now(),
            // Add other fields as needed (phone, user_type, etc.)
        ]);
    }

    /**
     * Create the tenant record.
     */
    protected function createTenant(string $subdomain, $owner, array $data): Tenant
    {
        return Tenant::create([
            'id' => $subdomain, // Stancl uses ID as identifier
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'owner_id' => $owner->id,
            'status' => 'trial',
            'trial_ends_at' => now()->addDays(14),
            'data' => [
                'address' => $data['address'] ?? null,
                'logo' => null,
                'timezone' => $data['timezone'] ?? 'Asia/Dhaka',
                'currency' => 'BDT',
                'language' => 'en',
            ],
        ]);
    }

    /**
     * Create domain for the tenant.
     */
    protected function createDomain(Tenant $tenant, string $subdomain): void
    {
        $appDomain = config('app.url');
        $appHost = parse_url($appDomain, PHP_URL_HOST) ?? 'localhost';

        // Strip port if present
        $appHost = explode(':', $appHost)[0];

        $tenant->domains()->create([
            'domain' => "{$subdomain}.{$appHost}",
        ]);
    }
}
