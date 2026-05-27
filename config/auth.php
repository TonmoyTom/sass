<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Authentication Defaults
    |--------------------------------------------------------------------------
    |
    | Default guard is "web" — this handles all central users (super_admin,
    | seller, tenant_owner). User type checking is done via middleware.
    |
    */

    'defaults' => [
        'guard' => env('AUTH_GUARD', 'web'),
        'passwords' => env('AUTH_PASSWORD_BROKER', 'users'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Guards
    |--------------------------------------------------------------------------
    |
    | We use a SINGLE 'web' guard for all central users.
    | User type-based access is enforced by middleware (EnsureSuperAdmin, etc.)
    |
    | This is simpler than multiple guards and uses one users table.
    | Tenant users will use a different guard later (when tenants are activated).
    |
    */

    'guards' => [
        // Main guard for all central users (admin, seller, tenant_owner)
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],

        // For future API access (mobile app)
        'sanctum' => [
            'driver' => 'sanctum',
            'provider' => 'users',
        ],

        // For tenant users (will be configured per-tenant via Stancl/Tenancy)
        // 'tenant' => [
        //     'driver' => 'session',
        //     'provider' => 'tenant_users',
        // ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Providers
    |--------------------------------------------------------------------------
    */

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => env('AUTH_MODEL', App\Models\User::class),
        ],

        // Tenant users will be added here when tenant auth is set up
        // 'tenant_users' => [...],
    ],

    /*
    |--------------------------------------------------------------------------
    | Resetting Passwords
    |--------------------------------------------------------------------------
    */

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => env('AUTH_PASSWORD_RESET_TOKEN_TABLE', 'password_reset_tokens'),
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Password Confirmation Timeout
    |--------------------------------------------------------------------------
    */

    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),
];
