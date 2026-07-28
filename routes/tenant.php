<?php

declare(strict_types=1);

use App\Http\Controllers\SessionController;
use App\Http\Controllers\Tenant\Domain\AuthController;
use App\Http\Controllers\Tenant\Domain\DashboardController;
use App\Http\Controllers\Tenant\Domain\MyModulesController;
use App\Http\Controllers\Tenant\Domain\NotificationController;
use App\Http\Controllers\Tenant\Domain\ProfileController;
use App\Http\Controllers\Tenant\Domain\RoleController;
use App\Http\Controllers\Tenant\Domain\SettingController;
use App\Http\Controllers\Tenant\Domain\SiteSettingController;
use App\Http\Controllers\Tenant\Domain\TenantPaymentSettingController;
use App\Http\Controllers\Tenant\Domain\TenantSitemapController;
use App\Http\Controllers\Tenant\Domain\TwoFactorController;
use App\Http\Controllers\Tenant\Domain\UserController;
use App\Http\Middleware\SetTenantAuthGuard;
use App\Models\CompanySetting;
use App\Models\SiteSetting;
use App\Models\TenantLoginToken;
use App\Models\TenantUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

Route::domain('{tenant}.myapp.test')->group(function () {
    Route::middleware([
        'web',
        'tenant.active',
        InitializeTenancyByDomain::class,
        PreventAccessFromCentralDomains::class,
        SetTenantAuthGuard::class,
    ])->group(function () {

        Broadcast::routes(['middleware' => ['web', 'auth:tenant']]);
        Route::get('/whoami', function () {
            $u = auth('tenant')->user();
            dd([
                'default_guard' => config('auth.defaults.guard'),
                'auth_check' => auth()->check(),
                'auth_user' => auth()->user()?->only('id', 'email'),
                'tenant_guard_check' => auth('tenant')->check(),
                'tenant_guard_user' => auth('tenant')->user()?->only('id', 'email'),
                'web_guard_check' => auth('web')->check(),
                'session_id' => session()->getId(),
                'user_guard' => $u->guard_name ?? 'not set',
                'roles' => $u->roles->map(fn ($r) => [$r->name, $r->guard_name]),
                'perms' => $u->getAllPermissions()->map(fn ($p) => [$p->name, $p->guard_name]),
                'direct_check' => $u->can('users.view'),
                'all_roles_in_db' => Role::all(['name', 'guard_name'])->toArray(),
                'all_perms_in_db' => Permission::where('name', 'like', 'users.%')->get(['name', 'guard_name'])->toArray(),
                'db_connection' => DB::connection()->getDatabaseName(),
            ]);
        });
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

        Route::get('/login', [AuthController::class, 'create'])->name('tenant.login');
        Route::post('/login', [AuthController::class, 'store'])->name('tenant.login.store');
        Route::post('/logout', [AuthController::class, 'destroy'])->name('tenant.logout');

        Route::get('/two-factor-challenge', [AuthController::class, 'showTwoFactorChallenge'])->name('tenant.two-factor.challenge');
        Route::post('/two-factor-challenge', [AuthController::class, 'verifyTwoFactorChallenge'])->name('tenant.two-factor.verify');

        // authenticated tenant routes
        Route::middleware(['auth:tenant'])->group(function () {
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
            Route::post('/settings/favicon', [SettingController::class, 'updateFavicon'])->name('tenant.settings.fav');
            Route::resource('/roles', RoleController::class)->names('tenant.roles');
            Route::resource('users', UserController::class)->names('tenant.users');

            Route::get('/settings/seo', [SiteSettingController::class, 'index'])->name('tenant.site-settings.index');
            Route::get('/settings/seo/create', [SiteSettingController::class, 'create'])->name('tenant.site-settings.create');
            Route::post('/settings/seo', [SiteSettingController::class, 'store'])->name('tenant.site-settings.store');
            Route::get('/settings/seo/{setting}/edit', [SiteSettingController::class, 'edit'])->name('tenant.site-settings.edit');
            Route::put('/settings/seo/{setting}', [SiteSettingController::class, 'updateSeo'])->name('tenant.site-settings.seo.update');
            Route::delete('/settings/seo/{setting}', [SiteSettingController::class, 'destroy'])->name('tenant.site-settings.destroy');

            Route::get('/notifications', [NotificationController::class, 'index'])->name('tenant.notifications.index');
            Route::post('/notifications/{id}/read', [NotificationController::class, 'markRead'])->name('tenant.notifications.read');

            Route::get('/my-modules', [MyModulesController::class, 'index'])->name('tenant.my-modules.index');
            Route::get('/my-modules/history', [MyModulesController::class, 'purchaseHistory'])->name('tenant.my-modules.history');
            Route::get('/my-modules/{id}', [MyModulesController::class, 'show'])->name('tenant.my-modules.show');
            Route::get('/my-modules/{id}/invoice', [MyModulesController::class, 'invoice'])->name('tenant.my-modules.invoice');
            Route::post('/my-modules/{tenantModuleId}/referral', [MyModulesController::class, 'updateReferral'])->name('tenant.my-modules.update-referral');

            Route::post('/my-modules/{tenantModuleId}/renew', [MyModulesController::class, 'renew'])->name('tenant.my-modules.renew');

            Route::get('/settings/payment', [TenantPaymentSettingController::class, 'index'])->name('tenant.payment-settings.index');
            Route::patch('/settings/payment/{method}', [TenantPaymentSettingController::class, 'update'])->name('tenant.payment-settings.update');

            Route::post('/session/clear', [SessionController::class, 'clearSession'])->name('session.clear');
            Route::post('/session/clear-cookies', [SessionController::class, 'clearCookies'])->name('session.clear-cookies');
            Route::get('/settings/two-factor', [TwoFactorController::class, 'show'])->name('tenant.payment-settings.index');
            Route::post('/settings/two-factor/enable', [TwoFactorController::class, 'enable'])->name('two-factor.enable');
            Route::post('/settings/two-factor/confirm', [TwoFactorController::class, 'confirm'])->name('two-factor.confirm');
            Route::delete('/settings/two-factor', [TwoFactorController::class, 'disable'])->name('two-factor.disable');

        });
    });
});
