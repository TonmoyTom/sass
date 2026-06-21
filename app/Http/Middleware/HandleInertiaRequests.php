<?php

namespace App\Http\Middleware;

use App\Models\CompanySetting;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
            ],
            'workspace' => function () {
                if (! tenant()) {
                    return null;
                }

                $t = Tenant::on('mysql')->find(tenant('id'));
                $settings = CompanySetting::current();

                return [
                    'company_name' => $settings->company_name ?? 'Workspace',
                    'logo_url' => $settings->logo_url,
                    'enabled_modules' => $t?->enabledModules() ?? [],
                ];
            },
        ];
    }
}
