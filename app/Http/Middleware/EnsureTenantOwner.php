<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * EnsureTenantOwner Middleware
 *
 * Restricts route access to tenant_owner users only.
 *
 * Usage:
 * Route::middleware(['auth', 'tenant_owner'])->group(...);
 */
class EnsureTenantOwner
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        if (!$user->isTenantOwner()) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Forbidden'], 403);
            }
            abort(403, 'Tenant owner access only.');
        }

        if (!$user->isActive()) {
            auth()->logout();
            return redirect()->route('login')
                ->withErrors(['email' => 'Your account is not active.']);
        }

        return $next($request);
    }
}
