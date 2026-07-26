<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PaymentSettingController extends Controller
{
    protected array $methods = ['bkash', 'nagad', 'bank'];

    public function index(): Response
    {
        $settings = collect($this->methods)->map(function ($method) {
            $setting = PaymentSetting::where('method', $method)->first();

            return [
                'method' => $method,
                'is_active' => $setting?->is_active ?? false,
                'merchant_number' => $setting?->merchant_number,
                'api_key' => $setting?->api_key,
                'username' => $setting?->username,
                'bank_name' => $setting?->bank_name,
                'account_name' => $setting?->account_name,
                'account_number' => $setting?->account_number,
                'routing_number' => $setting?->routing_number,
                'branch' => $setting?->branch,
                'instructions' => $setting?->instructions,
                // sensitive field gulo mask kore pathao, full value na
                'has_api_secret' => (bool) $setting?->api_secret,
                'has_password' => (bool) $setting?->password,
            ];
        });

        return Inertia::render('Admin/PaymentSettings/Index', [
            'settings' => $settings,
        ]);
    }

    public function update(Request $request, string $method): RedirectResponse
    {
        if (! in_array($method, $this->methods, true)) {
            abort(404);
        }

        $rules = match ($method) {
            'bkash', 'nagad' => [
                'merchant_number' => ['nullable', 'string', 'max:20'],
                'api_key' => ['nullable', 'string', 'max:255'],
                'api_secret' => ['nullable', 'string', 'max:255'],
                'username' => ['nullable', 'string', 'max:255'],
                'password' => ['nullable', 'string', 'max:255'],
                'instructions' => ['nullable', 'string', 'max:1000'],
            ],
            'bank' => [
                'bank_name' => ['nullable', 'string', 'max:255'],
                'account_name' => ['nullable', 'string', 'max:255'],
                'account_number' => ['nullable', 'string', 'max:50'],
                'routing_number' => ['nullable', 'string', 'max:50'],
                'branch' => ['nullable', 'string', 'max:255'],
                'instructions' => ['nullable', 'string', 'max:1000'],
            ],
        };

        $active = false;
        if ($request?->merchant_number) {
            $active = true;
        } elseif ($request?->account_number) {
            $active = true;
        }

        $data = $request->validate($rules);
        $data['is_active'] = $active;
        foreach (['api_secret', 'password'] as $secretField) {
            if (array_key_exists($secretField, $data) && empty($data[$secretField])) {
                unset($data[$secretField]);
            }
        }

        PaymentSetting::updateOrCreate(
            ['method' => $method],
            $data
        );

        return back()->with('status', ucfirst($method).' settings updated.');
    }
}
