<?php

namespace App\Http\Controllers\Tenant\Domain;

use App\Http\Controllers\Controller;
use App\Models\TenantUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use PragmaRX\Google2FA\Google2FA;

class AuthController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Public/Domain/Auth/Login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::guard('tenant')->attempt($credentials)) {
            return back()->withErrors(['email' => 'Invalid credentials.']);
        }

        $user = Auth::guard('tenant')->user();
        // ── 2FA check, tenant guard-eo same pattern ──
        if ($user->hasTwoFactorEnabled()) {
            Auth::guard('tenant')->logout();

            $request->session()->put('tenant_2fa_user_id', $user->id);

            return redirect('/two-factor-challenge');
        }

        $request->session()->regenerate();

        return redirect()->intended('/dashboard');
    }

    public function showTwoFactorChallenge(): Response
    {
      
        if (! session()->has('tenant_2fa_user_id')) {
            return redirect()->route('login');
        }

        return Inertia::render('Auth/Tenant/TwoFactorChallenge');
    }

    public function verifyTwoFactorChallenge(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'string'],
        ]);

        $userId = session('tenant_2fa_user_id');

        if (! $userId) {
            return redirect()->route('login');
        }

        $user = TenantUser::find($userId);
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

        $request->session()->forget('tenant_2fa_user_id');
        Auth::guard('tenant')->login($user);
        $request->session()->regenerate();

        return redirect()->intended('/dashboard');
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('tenant')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
