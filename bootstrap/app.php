<?php

use App\Http\Middleware\CheckTenantActive;
use App\Http\Middleware\EnsureModuleActive;
use App\Http\Middleware\EnsureSeller;
use App\Http\Middleware\EnsureSuperAdmin;
use App\Http\Middleware\EnsureTenantOwner;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            // Route::group([], base_path('routes/tenant.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);
        $middleware->alias([
            'module.active' => EnsureModuleActive::class,
            'super_admin' => EnsureSuperAdmin::class,
            'seller' => EnsureSeller::class,
            'tenant_owner' => EnsureTenantOwner::class,
            'tenant.active' => CheckTenantActive::class,
        ]);

        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
