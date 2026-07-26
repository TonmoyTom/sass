<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserType;
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
        $page = $user->getTwoFactorPage();

        return Inertia::render($page, [
            'enabled' => $user->hasTwoFactorEnabled(),
            'qr_code_url' => $user->two_factor_secret && ! $user->hasTwoFactorEnabled()
                ? $user->twoFactorQrCodeUrl()
                : null,
        ]);
    }

    // Step 1: secret generate koro, QR dekhao (confirm na hoya porjonto enable hoy na)
    public function enable(OwnerProfileSync $sync): RedirectResponse
    {
        $user = auth()->user();
        $google2fa = new Google2FA;

        $user->forceFill([
            'two_factor_secret' => encrypt($google2fa->generateSecretKey()),
            'two_factor_confirmed_at' => null,
        ])->save();

        if ($user->user_type === UserType::TENANT_OWNER) {
            $sync->syncTwoFactorToTenant($user);
        }

        return back();
    }

    // Step 2: user QR scan kore ekta code dey, verify koro
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

        if ($user->user_type === UserType::TENANT_OWNER) {
            $sync->syncTwoFactorToTenant($user);
        }

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

        if ($user->user_type === UserType::TENANT_OWNER) {
            $sync->syncTwoFactorToTenant($user);
        }

        return back()->with('status', 'Two-factor authentication disabled.');
    }
}
