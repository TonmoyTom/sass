<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

/**
 * AuthenticatedSessionController
 *
 * Handles login/logout for the central system.
 * Replaces Breeze's default controller with multi-user-type smart redirects.
 */
class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): Response
    {

        return Inertia::render('Auth/Login', [
            'canResetPassword' => true,
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {

    
        $request->authenticate();
        $request->session()->regenerate();

        $user = $request->user();

        // Check if user is active
        if ($user->status !== UserStatus::ACTIVE) {
            Auth::logout();
            $request->session()->invalidate();

            return redirect()->route('login')
                ->withErrors([
                    'email' => match($user->status) {
                        UserStatus::SUSPENDED => 'Your account is suspended. Please contact support.',
                        UserStatus::PENDING => 'Your account is pending approval.',
                        UserStatus::BANNED => 'Your account has been banned.',
                        default => 'Your account is not active.',
                    }
                ]);
        }

        // Record login info
        $user->recordLogin($request->ip());

        // Smart redirect based on user type
        return redirect()->intended($user->getDashboardUrl());
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
