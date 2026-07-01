<?php

namespace App\Http\Controllers\Tenant\Domain;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Services\FileStorageService;
use App\Services\OwnerProfileSync;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function edit(): Response
    {
        $user = auth()->user()->load('info');

        return Inertia::render('Tenant/Domain/Profile/Edit', [
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'avatar' => $user->avatar_url,
                'info' => [
                    'first_name' => $user->info?->first_name,
                    'last_name' => $user->info?->last_name,
                    'bio' => $user->info?->bio,
                    'country' => $user->info?->country,
                    'city' => $user->info?->city,
                    'postal_code' => $user->info?->postal_code,
                    'facebook' => $user->info?->facebook,
                    'twitter' => $user->info?->twitter,
                    'linkedin' => $user->info?->lnkedin,   // tenant clean column
                    'instagram' => $user->info?->instagram,
                ],
            ],
        ]);
    }

    public function update(Request $request, OwnerProfileSync $sync): RedirectResponse
    {
        $user = auth()->user();
        $originalEmail = $user->email;

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:20'],
            'first_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'country' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'facebook' => ['nullable', 'string', 'max:255'],
            'twitter' => ['nullable', 'string', 'max:255'],
            'linkedin' => ['nullable', 'string', 'max:255'],
            'instagram' => ['nullable', 'string', 'max:255'],
        ]);

        // tenant user update
        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? $user->phone,
        ]);

        $user->info()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'first_name' => $data['first_name'] ?? null,
                'last_name' => $data['last_name'] ?? null,
                'bio' => $data['bio'] ?? null,
                'country' => $data['country'] ?? null,
                'city' => $data['city'] ?? null,
                'postal_code' => $data['postal_code'] ?? null,
                'facebook' => $data['facebook'] ?? null,
                'twitter' => $data['twitter'] ?? null,
                'lnkedin' => $data['linkedin'] ?? null,   // tenant clean column
                'instagram' => $data['instagram'] ?? null,
            ]
        );

        // owner hole — central-e sync
        $tenant = Tenant::on('mysql')->find(tenant('id'));
        if ($tenant && $tenant->owner && $tenant->owner->email === $originalEmail) {
            $sync->syncToCentral($tenant, $user, $data, $originalEmail);
        }

        return back()->with('status', 'Profile updated');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'current_password' => ['required'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $user = auth()->user();

        if (! Hash::check($data['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'Current password thik na.']);
        }

        $user->update(['password' => Hash::make($data['password'])]);

        return back()->with('status', 'Password updated');
    }

    public function updateAvatar(Request $request, FileStorageService $storage): RedirectResponse
    {
        $request->validate(['avatar' => ['required', 'image', 'max:2048']]);

        $user = auth()->user();

        $path = $storage->uploadImage(
            $request->file('logo'),
            'site_setting',
            ['width' => 400, 'height' => 400, 'quality' => 90]
        );
        $user->update(['avatar' => $path]);

        return back()->with('status', 'Avatar updated');
    }
}
