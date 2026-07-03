<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CompanySetting;
use App\Services\FileStorageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SettingController extends Controller
{
    public function index(): Response
    {
        $settings = CompanySetting::current();
        $user = auth()->user()->load('info');

        return Inertia::render('Admin/Settings/Index', [
            'settings' => $settings,
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $user->avatar,
            ],
        ]);
    }

    public function update(Request $request): RedirectResponse
    {

        $data = $request->validate([
            // identity
            'company_name' => ['required', 'string', 'max:255'],
            'legal_name' => ['nullable', 'string', 'max:255'],
            'tagline' => ['nullable', 'string', 'max:255'],

            // contact
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'alt_phone' => ['nullable', 'string', 'max:20'],
            'website' => ['nullable', 'string', 'max:255'],

            // // address
            'address_line1' => ['nullable', 'string', 'max:255'],
            'address_line2' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'country' => ['nullable', 'string', 'max:100'],

            // // legal
            'registration_no' => ['nullable', 'string', 'max:100'],
            'tax_id' => ['nullable', 'string', 'max:100'],
            'vat_no' => ['nullable', 'string', 'max:100'],

            // // localization
            'currency' => ['nullable', 'string', 'max:10'],
            'currency_symbol' => ['nullable', 'string', 'max:10'],
            'timezone' => ['nullable', 'string', 'max:50'],
            'locale' => ['nullable', 'string', 'max:10'],
            'date_format' => ['nullable', 'string', 'max:20'],

            // // social
            'facebook' => ['nullable', 'string', 'max:255'],
            'instagram' => ['nullable', 'string', 'max:255'],
            'whatsapp' => ['nullable', 'string', 'max:255'],
        ]);

        $settings = CompanySetting::current();

        $data['setup_completed'] = true;

        $settings->update($data);

        return back()->with('status', 'Settings updated successfully');
    }

    public function updateLogo(Request $request, FileStorageService $storage): RedirectResponse
    {
        $request->validate([
            'logo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:2048'],
        ]);

        $settings = CompanySetting::current();

        if ($settings->logo) {
            $storage->deleteFile($settings->logo);
        }

        $path = $storage->uploadImage(
            $request->file('logo'),
            'company',
            ['width' => 400, 'height' => 400, 'quality' => 90]
        );

        $settings->update(['logo' => $path]);

        return back()->with('status', 'Logo updated');
    }

    public function updateFavicon(Request $request, FileStorageService $storage)
    {
        $request->validate([
            'favicon' => 'required|image|mimes:png,ico,jpg,jpeg|max:512', // favicon chhoto rakho, max 512KB
        ]);
        $setting = CompanySetting::current();
        $path = $storage->uploadImage(
            $request->file('favicon'),
            'company',
            ['width' => 400, 'height' => 400, 'quality' => 90]
        );

        $setting->update(['favicon' => $path]);

        return back()->with('success', 'Favicon updated successfully');
    }
}
