<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

/**
 * EnsureModuleActive
 *
 * Middleware that ensures a tenant has access to a specific module.
 * Used in module routes to gate access.
 *
 * Usage in routes (e.g., Modules/School/Routes/web.php):
 * Route::middleware(['tenant', 'module.active:school'])
 *     ->group(function () {
 *         Route::get('/students', [StudentController::class, 'index']);
 *     });
 *
 * Register in bootstrap/app.php:
 * $middleware->alias([
 *     'module.active' => \App\Http\Middleware\EnsureModuleActive::class,
 * ]);
 */
class EnsureModuleActive
{
    public function handle(Request $request, Closure $next, string $moduleAlias): Response
    {
        // Get current tenant
        $tenant = function_exists('tenant') ? tenant() : null;

        if (!$tenant) {
            abort(403, 'Tenant context required for this route');
        }

        // Check if module is active for this tenant
        // We query the central DB (modules + tenant_modules tables)
        $isActive = $this->checkModuleAccess($tenant->id, $moduleAlias);

        if (!$isActive) {
            // For Inertia/AJAX requests, return JSON
            if ($request->wantsJson() || $request->header('X-Inertia')) {
                return response()->json([
                    'message' => "Module '{$moduleAlias}' is not active. Please subscribe or purchase.",
                ], 403);
            }

            // Redirect to marketplace with error
            return redirect()
                ->route('tenant.modules.marketplace')
                ->with('error', "Please activate the {$moduleAlias} module to continue.");
        }

        return $next($request);
    }

    /**
     * Check if tenant has active access to the module.
     * Handles both subscription (with expiry) and lifetime (one-time) access.
     */
    protected function checkModuleAccess(string $tenantId, string $moduleAlias): bool
    {
        // Use central DB connection to query
        $centralConnection = config('tenancy.database.central_connection', 'mysql');

        $tenantModule = DB::connection($centralConnection)
            ->table('tenant_modules')
            ->join('modules', 'modules.id', '=', 'tenant_modules.module_id')
            ->where('tenant_modules.tenant_id', $tenantId)
            ->where('modules.alias', $moduleAlias)
            ->select('tenant_modules.*', 'modules.alias as module_alias')
            ->first();

        if (!$tenantModule) {
            return false;
        }

        // Lifetime access (one-time purchase)
        if ($tenantModule->access_type === 'lifetime') {
            return $tenantModule->status === 'purchased';
        }

        // Subscription access — check status and expiry
        if (!in_array($tenantModule->status, ['active', 'trial'])) {
            return false;
        }

        // Check expiry
        if ($tenantModule->expires_at) {
            return now()->lt($tenantModule->expires_at);
        }

        return true;
    }
}
