<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use PragmaRX\Google2FA\Google2FA;

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
                    'email' => match ($user->status) {
                        UserStatus::SUSPENDED => 'Your account is suspended. Please contact support.',
                        UserStatus::PENDING => 'Your account is pending approval.',
                        UserStatus::BANNED => 'Your account has been banned.',
                        default => 'Your account is not active.',
                    },
                ]);
        }

        // ── 2FA check — password thik thakle, 2FA enabled hole full-login na kore challenge-e pathao ──
        if ($user->hasTwoFactorEnabled()) {
            $intendedUrl = redirect()->intended($user->getDashboardUrl())->getTargetUrl();

            Auth::logout();
            // session flush na kore shudhu logout, 2fa_user_id + intended url store koro
            $request->session()->put('2fa_user_id', $user->id);
            $request->session()->put('2fa_intended_url', $intendedUrl);

            return redirect()->route('two-factor.challenge');
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

    public function showChallenge(): Response
    {
        if (! session()->has('2fa_user_id')) {
            return redirect()->route('login');
        }

        return Inertia::render('Auth/TwoFactor/Challenge');
    }

    public function verifyChallenge(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'string'],
        ]);

        $userId = session('2fa_user_id');

        if (! $userId) {
            return redirect()->route('login');
        }

        $user = User::find($userId);

        // ── extra safety: status abar check koro, session-e atke thaka user active na-o thakte pare ──
        if (! $user || $user->status !== UserStatus::ACTIVE) {
            $request->session()->forget(['2fa_user_id', '2fa_intended_url']);

            return redirect()->route('login')
                ->withErrors(['email' => 'Your account is not active.']);
        }

        $google2fa = new Google2FA;

        $code = $request->input('code');
        $valid = $google2fa->verifyKey(decrypt($user->two_factor_secret), $code);

        if (! $valid && strlen($code) > 6) {
            $recoveryCodes = json_decode(decrypt($user->two_factor_recovery_codes), true);
            if (in_array($code, $recoveryCodes, true)) {
                $valid = true;
                $user->replaceRecoveryCode($code);
            }
        }

        if (! $valid) {
            return back()->withErrors(['code' => 'Invalid authentication code.']);
        }

        $intendedUrl = $request->session()->pull('2fa_intended_url');
        $request->session()->forget('2fa_user_id');

        Auth::login($user);
        $request->session()->regenerate();

        $user->recordLogin($request->ip());

        return $intendedUrl
            ? redirect()->to($intendedUrl)
            : redirect()->intended($user->getDashboardUrl());
    }
}
