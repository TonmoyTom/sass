<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckTenantActive
{
    public function handle(Request $request, Closure $next)
    {
        $tenant = tenant(); // current tenant (subdomain context-e available)

        if ($tenant && in_array($tenant->status, ['suspended', 'expired'])) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            abort(403, 'This workspace is currently ' . $tenant->status . '. Please contact support.');
        }

        return $next($request);
    }
}
