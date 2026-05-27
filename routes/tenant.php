<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

Route::middleware([
    'web',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
])->domain('{tenant}.localhost')->group(function () {

    Route::get('/', function () {
        dd("hello");
        // return Inertia::render('Tenant/Dashboard', [
        //     'tenant' => tenant(),
        // ]);
    })->name('tenant.home');
    // Tenant login (renamed to avoid conflict)
    Route::get('/tenant-login', function () {
        return Inertia::render('Tenant/Auth/Login');
    })->name('tenant.login');

    // Authenticated tenant routes
    Route::middleware(['auth'])->group(function () {

        Route::get('/', function () {
            return Inertia::render('Tenant/Dashboard', [
                'tenant' => tenant(),
            ]);
        })->name('tenant.home');

        Route::get('/modules', function () {
            return Inertia::render('Tenant/Modules/Index');
        })->name('tenant.modules');

        Route::get('/settings', function () {
            return Inertia::render('Tenant/Settings');
        })->name('tenant.settings');
    });
});
