<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByRequestData;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| API routes for future mobile app and third-party integrations.
| For MVP, these are not actively used — but the structure is ready.
|
| When you build the mobile app:
| 1. Modules will register their API routes here (auto-loaded)
| 2. Mobile app sends header: X-Tenant: <subdomain>
| 3. Auth via Sanctum tokens
|
| URL pattern: api.platform.com/v1/<resource>
|              or platform.com/api/v1/<resource>
|
*/

Route::prefix('v1')->group(function () {

    // Public API (no auth required)
    Route::prefix('public')->group(function () {
        Route::get('/health', fn() => ['status' => 'ok', 'timestamp' => now()]);
        Route::get('/version', fn() => ['version' => '1.0.0']);
    });

    // Auth endpoints (no middleware)
    Route::prefix('auth')->group(function () {
        // Will be implemented when app is built:
        // Route::post('/login', [AuthApiController::class, 'login']);
        // Route::post('/register', [AuthApiController::class, 'register']);
        // Route::post('/forgot-password', [AuthApiController::class, 'forgot']);
    });

    // Authenticated routes (Sanctum token required)
    Route::middleware('auth:sanctum')->group(function () {

        // Current user info
        Route::get('/me', function (Request $request) {
            return $request->user();
        });

        // Logout
        Route::post('/logout', function (Request $request) {
            $request->user()->currentAccessToken()->delete();
            return ['message' => 'Logged out'];
        });
    });

    // Tenant-scoped API routes
    // Tenant identified by: X-Tenant header OR ?tenant= query param
    Route::middleware([
        'auth:sanctum',
        InitializeTenancyByRequestData::class,
    ])->prefix('tenant')->group(function () {

        // Tenant info
        Route::get('/info', function () {
            return tenant();
        });

        // Modules will register their API routes here
        // Each module's RouteServiceProvider can load Routes/api.php
    });
});
