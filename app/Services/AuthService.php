<?php

namespace App\Services;

use App\Models\TenantUser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

class AuthService
{
    /**
     * Login — common (sob module).
     */
    public function login(array $credentials, bool $remember = false): TenantUser
    {
        if (! Auth::attempt($credentials, $remember)) {
            throw ValidationException::withMessages([
                'email' => 'Email ba password thik na.',
            ]);
        }

        return auth()->user();
    }

    /**
     * Register — common, role parameter diye.
     */
    public function register(array $data, string $role): TenantUser
    {
        $user = TenantUser::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'password' => Hash::make($data['password']),
        ]);

        if (Role::where('name', $role)->exists()) {
            $user->assignRole($role);
        }

        Auth::login($user);

        return $user;
    }

    public function logout(): void
    {
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();
    }

    public function redirectFor(TenantUser $user): string
    {
        return match (true) {
            $user->hasRole('customer') => '/shop',
            $user->hasRole('patient') => '/patient/dashboard',
            default => '/dashboard',
        };
    }
}
