<?php

namespace App\Http\Controllers\Tenant\Domain;

use App\Http\Controllers\Controller;
use App\Services\OwnerProfileSync;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorController extends Controller
{
    public function show(): Response
    {
        $user = auth()->user();

        return Inertia::render('Auth/WorkSpace/Show', [
            'enabled' => $user->hasTwoFactorEnabled(),
            'qr_code_url' => $user->two_factor_secret && ! $user->hasTwoFactorEnabled()
                ? $user->twoFactorQrCodeUrl()
                : null,
        ]);
    }

    public function enable(OwnerProfileSync $sync): RedirectResponse
    {
        $user = auth()->user();
        $google2fa = new Google2FA;

        $user->forceFill([
            'two_factor_secret' => encrypt($google2fa->generateSecretKey()),
            'two_factor_confirmed_at' => null,
        ])->save();
        $sync->syncTwoFactorToCentral($user);

        return back();
    }

    public function confirm(Request $request, OwnerProfileSync $sync): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'string'],
        ]);

        $user = auth()->user();
        $google2fa = new Google2FA;

        $valid = $google2fa->verifyKey(
            decrypt($user->two_factor_secret),
            $request->input('code')
        );

        if (! $valid) {
            return back()->withErrors(['code' => 'Invalid authentication code.']);
        }

        $user->forceFill(['two_factor_confirmed_at' => now()])->save();
        $codes = $user->generateRecoveryCodes();

        // ── tenant subdomain-e sync koro ──
        $sync->syncTwoFactorToCentral($user);

        return back()->with('recovery_codes', $codes);
    }

    public function disable(Request $request, OwnerProfileSync $sync): RedirectResponse
    {
        $request->validate([
            'password' => ['required'],
        ]);

        $user = auth()->user();

        if (! Hash::check($request->input('password'), $user->password)) {
            return back()->withErrors(['password' => 'Incorrect password.']);
        }

        $user->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();
        $sync->syncTwoFactorToCentral($user);

        return back()->with('status', 'Two-factor authentication disabled.');
    }
}
