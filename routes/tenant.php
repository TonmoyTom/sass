<?php

declare(strict_types=1);

use App\Http\Controllers\Tenant\Domain\DashboardController;
use App\Http\Controllers\Tenant\Domain\ProfileController;
use App\Http\Controllers\Tenant\Domain\RoleController;
use App\Http\Controllers\Tenant\Domain\SettingController;
use App\Http\Controllers\Tenant\Domain\SiteSettingController;
use App\Http\Controllers\Tenant\Domain\TenantSitemapController;
use App\Http\Controllers\Tenant\Domain\UserController;
use App\Models\CompanySetting;
use App\Models\SiteSetting;
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

    Route::get('/', function () {
        $setting = SiteSetting::where('page_key', 'home')->with('seo')->first();

        return Inertia::render('Public/Domain/Home', [
            'seo' => $setting?->frontSeoArray() ?? [
                'title' => tenant('name'),
                'description' => 'Welcome to '.(tenant('name') ?? config('app.name')),
                'canonical' => url('/'),
                'robots' => 'index,follow',
                'og_type' => 'website',
                'twitter_card' => 'summary_large_image',
            ],
        ]);
    })->name('tenant.home');

    Route::get('/sitemap.xml', [TenantSitemapController::class, 'index'])->name('tenant.sitemap');
    Route::get('/robots.txt', [TenantSitemapController::class, 'robots'])->name('tenant.robots');

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

        Route::get('/settings/seo', [SiteSettingController::class, 'index'])->name('tenant.site-settings.index');
        Route::get('/settings/seo/create', [SiteSettingController::class, 'create'])->name('tenant.site-settings.create');
        Route::post('/settings/seo', [SiteSettingController::class, 'store'])->name('tenant.site-settings.store');
        Route::get('/settings/seo/{setting}/edit', [SiteSettingController::class, 'edit'])->name('tenant.site-settings.edit');
        Route::put('/settings/seo/{setting}', [SiteSettingController::class, 'updateSeo'])->name('tenant.site-settings.seo.update');
        Route::delete('/settings/seo/{setting}', [SiteSettingController::class, 'destroy'])->name('tenant.site-settings.destroy');
    });
});
