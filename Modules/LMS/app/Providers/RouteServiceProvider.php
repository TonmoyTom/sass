<?php

namespace Modules\LMS\Providers;

use App\Http\Middleware\SetTenantAuthGuard;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

class RouteServiceProvider extends ServiceProvider
{
    protected string $name = 'LMS';

    public function map(): void
    {
        $this->mapApiRoutes();
        $this->mapWebRoutes();
        $this->mapTenantRoutes();   // ← notun
    }

    protected function mapWebRoutes(): void
    {
        Route::middleware('web')
            ->group(module_path($this->name, '/routes/web.php'));
    }

    protected function mapApiRoutes(): void
    {
        Route::middleware('api')
            ->prefix('api')
            ->group(module_path($this->name, '/routes/api.php'));
    }

    // ── notun method — tenant subdomain-e attach koro ──
    protected function mapTenantRoutes(): void
    {
        Route::domain('{tenant}.myapp.test')   // tomar central domain onujayi
            ->middleware([
                'web',
                'tenant.active',
                InitializeTenancyByDomain::class,
                PreventAccessFromCentralDomains::class,
                SetTenantAuthGuard::class,
                'auth:tenant',
            ])
            ->group(module_path($this->name, '/routes/tenant.php'));
    }
}
