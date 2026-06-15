<?php

namespace App\Http\Controllers;

use App\Enums\UserType;
use App\Http\Requests\ProfileUpdateRequest;
use App\Http\Requests\UpdateAddressRequest;
use App\Services\FileStorageService;
use App\Services\OwnerProfileSync;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('Profile/Edit', [
            'user' => $request->user()->load('info'),
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => session('status'),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request, OwnerProfileSync $sync): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validated();
        $originalEmail = $user->email;

        $user->fill([
            'name' => trim(($data['first_name'] ?? '').' '.($data['last_name'] ?? '')),
            'email' => $data['email'] ?? $user->email,
            'phone' => $data['phone'] ?? $user->phone,
        ]);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        $user->info()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'first_name' => $data['first_name'] ?? '',
                'last_name' => $data['last_name'] ?? '',
                'bio' => $data['bio'] ?? null,
                'facebook' => $data['facebook'] ?? null,
                'twitter' => $data['twitter'] ?? null,
                'lnkedin' => $data['linkedin'] ?? null,
                'instagram' => $data['instagram'] ?? null,
            ]
        );

        if ($user->user_type === UserType::TENANT_OWNER) {
            $sync->syncToTenant($user, $data, $originalEmail);
        }

        return Redirect::route('profile.edit');
    }

    public function updateAddress(UpdateAddressRequest $request, OwnerProfileSync $sync): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validated();

        $user->info()->updateOrCreate(['user_id' => $user->id], $data);

        if ($user->user_type === UserType::TENANT_OWNER) {
            $sync->syncToTenant($user, $data, $user->email);
        }

        return Redirect::route('profile.edit');
    }

    public function updateAvatar(Request $request, FileStorageService $storage, OwnerProfileSync $sync): RedirectResponse
    {
        $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $user = $request->user();

        $storage->deleteFile($user->avatar);
        $path = $storage->uploadImage(
            $request->file('avatar'),
            'avatars',
            ['width' => 400, 'height' => 400, 'quality' => 85]
        );

        $user->update(['avatar' => $path]);

        if ($user->user_type === UserType::TENANT_OWNER) {
            $sync->syncToTenant($user, ['avatar' => $path], $user->email);
        }

        return back();
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
