<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class ImpersonateController extends Controller
{

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!$request->user()?->isSuperAdmin()) {
                abort(403, 'Only super admin can impersonate.');
            }
            return $next($request);
        });
    }
    /**
     * Start impersonating a user. Stores the original admin's ID in session
     * so we can return back to it later.
     */
    public function start(User $user): RedirectResponse
    {
        // Safety: can't impersonate yourself, and can't impersonate another super-admin
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot impersonate yourself.');
        }

        if ($user->hasRole('super-admin')) {
            return back()->with('error', 'You cannot impersonate a super admin.');
        }

        // Store original admin id (don't overwrite if already impersonating, to
        // prevent nested impersonation chains)
        if (!session()->has('impersonator_id')) {
            session(['impersonator_id' => auth()->id()]);
        }

        Auth::login($user);

        return redirect()
            ->route('dashboard')
            ->with('status', "You are now logged in as {$user->name}.");
    }

    /**
     * Stop impersonating and return to the original admin account.
     */
    public function stop(): RedirectResponse
    {
        $adminId = session('impersonator_id');

        if (!$adminId) {
            return redirect()->route('dashboard');
        }

        session()->forget('impersonator_id');

        $admin = User::find($adminId);

        if (!$admin) {
            Auth::logout();
            return redirect()->route('login');
        }

        Auth::login($admin);

        return redirect()
            ->route('admin.dashboard')
            ->with('status', 'You have returned to your admin account.');
    }
}
