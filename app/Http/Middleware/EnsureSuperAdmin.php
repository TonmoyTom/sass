<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\UserType;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * EnsureSuperAdmin Middleware
 *
 * Restricts route access to super admin and staff users only.
 * Fine-grained permission checks (per-route) are handled separately
 * via Spatie's `can:` middleware inside individual controllers.
 *
 * Usage in routes:
 * Route::middleware(['auth', 'super_admin'])->group(function () {
 *     Route::get('/admin/dashboard', ...);
 * });
 */
class EnsureSuperAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        $isAllowed = $user->isSuperAdmin() || $user->hasRole('staff');

        if (!$isAllowed) {
            // Not authorized for admin area — redirect to their dashboard
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Forbidden'], 403);
            }
            abort(403, 'You are not authorized to access this area.');
        }

        if (!$user->isActive()) {
            auth()->logout();
            return redirect()->route('login')
                ->withErrors(['email' => 'Your account is not active.']);
        }

        return $next($request);
    }
}
