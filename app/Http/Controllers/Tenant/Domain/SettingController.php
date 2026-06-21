<?php

namespace App\Http\Controllers\Tenant\Domain;

use App\Http\Controllers\Controller;
use App\Models\CompanySetting;
use App\Models\Tenant;
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
        $tenant = Tenant::on('mysql')->find(tenant('id'));
        $user = auth()->user()->load('info');

        return Inertia::render('Tenant/Domain/Settings/Index', [
            'settings' => $settings,
            'tenant' => [
                'id' => $tenant?->id,
                'name' => $tenant?->name,
                'subdomain' => $tenant?->domains()->first()?->domain,
            ],
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

            // address
            'address_line1' => ['nullable', 'string', 'max:255'],
            'address_line2' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'country' => ['nullable', 'string', 'max:100'],

            // legal
            'registration_no' => ['nullable', 'string', 'max:100'],
            'tax_id' => ['nullable', 'string', 'max:100'],
            'vat_no' => ['nullable', 'string', 'max:100'],

            // localization
            'currency' => ['nullable', 'string', 'max:10'],
            'currency_symbol' => ['nullable', 'string', 'max:10'],
            'timezone' => ['nullable', 'string', 'max:50'],
            'locale' => ['nullable', 'string', 'max:10'],
            'date_format' => ['nullable', 'string', 'max:20'],

            // social
            'facebook' => ['nullable', 'string', 'max:255'],
            'instagram' => ['nullable', 'string', 'max:255'],
            'whatsapp' => ['nullable', 'string', 'max:20'],
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
}
