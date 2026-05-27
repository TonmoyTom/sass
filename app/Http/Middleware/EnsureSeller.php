<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * EnsureSeller Middleware
 *
 * Restricts route access to seller users only.
 *
 * Usage:
 * Route::middleware(['auth', 'seller'])->group(...);
 */
class EnsureSeller
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        if (!$user->isSeller()) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Forbidden'], 403);
            }
            abort(403, 'Seller access only.');
        }

        if (!$user->isActive()) {
            auth()->logout();
            return redirect()->route('login')
                ->withErrors(['email' => 'Your seller account is not active.']);
        }

        return $next($request);
    }
}
