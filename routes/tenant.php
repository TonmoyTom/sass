<?php

declare(strict_types=1);

use App\Http\Controllers\Tenant\Domain\DashboardController;
use App\Http\Controllers\Tenant\Domain\ProfileController;
use App\Http\Controllers\Tenant\Domain\RoleController;
use App\Http\Controllers\Tenant\Domain\SettingController;
use App\Http\Controllers\Tenant\Domain\UserController;
use App\Models\CompanySetting;
use App\Models\TenantLoginToken;
use App\Models\TenantUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

Route::middleware([
    'web',
    'tenant.active',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
])->domain('{tenant}.localhost')->group(function () {
    Route::get('/auto-login', function (Request $request) {
        $token = $request->query('token');

        $loginToken = TenantLoginToken::on('mysql')
            ->where('token', $token)
            ->where('tenant_id', tenant('id'))
            ->first();

        if (! $loginToken || ! $loginToken->isValid()) {
            abort(403, 'Invalid or expired login link');
        }

        $loginToken->update(['used' => true]);

        $tenantUser = TenantUser::where('email', $loginToken->email)->first();

        if (! $tenantUser) {
            abort(403, 'User not found in this workspace');
        }
        $settings = CompanySetting::current();
        $default = $settings->setup_completed ? '/dashboard' : '/settings';
        $redirect = $default;

        if (! str_starts_with($redirect, '/') || str_starts_with($redirect, '//')) {
            $redirect = $default;
        }

        return redirect($redirect);
    })->name('tenant.auto-login');

    // tenant login page (public)
    Route::get('/tenant-login', function () {
        return Inertia::render('Tenant/Auth/Login');
    })->name('tenant.login');

    // authenticated tenant routes
    Route::middleware(['auth'])->group(function () {
        Route::get('/', function () {
            return 'Logged in as: '.auth()->user()->name.' (tenant: '.tenant('id').')';
        })->name('tenant.home');
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('tenant.dashboard');
        Route::get('/profile', [ProfileController::class, 'edit'])->name('tenant.profile');
        Route::post('/profile', [ProfileController::class, 'update'])->name('tenant.profile.update');
        Route::post('/profile/avatar', [ProfileController::class, 'updateAvatar'])->name('tenant.profile.avatar');
        Route::post('/profile/password', [ProfileController::class, 'updatePassword'])->name('tenant.profile.password');
        Route::get('/settings', [SettingController::class, 'index'])->name('tenant.settings');
        Route::post('/settings', [SettingController::class, 'update'])->name('tenant.settings.update');
        Route::post('/settings/logo', [SettingController::class, 'updateLogo'])->name('tenant.settings.logo');
        Route::resource('/roles', RoleController::class)->names('tenant.roles');
        Route::resource('users', UserController::class)->names('tenant.users');
    });
});
