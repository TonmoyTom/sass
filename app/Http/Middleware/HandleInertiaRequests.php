<?php

namespace App\Http\Middleware;

use App\Models\CompanySetting;
use App\Models\Tenant;
use App\Services\TenantAssetService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        return [
            ...parent::share($request),

            'auth' => [
                // full model na pathiye dorkari field gulai pathao (payload chhoto + safe)
                'user' => fn () => $request->user()?->only('id', 'name', 'email'),
            ],

            // ── central side: owner's tenant ──
            'tenant' => fn () => $this->ownedTenant($request),

            // ── tenant side: subdomain context ──
            'workspace' => fn () => $this->workspace(),
        ];
    }

    protected function ownedTenant(Request $request): ?array
    {
        $user = $request->user();
        if (! $user) {
            return null;
        }

        return Cache::remember(
            "share:tenant:user:{$user->id}",
            now()->addMinutes(15),
            function () use ($user) {
                $owned = $user->ownedTenants()
                    ->with('domains:tenant_id,domain')
                    ->first();

                if (! $owned) {
                    return null;
                }

                return [
                    'name' => $owned->name,
                    'status' => $owned->status,
                    'domain' => $owned->domains->first()?->domain,
                    'logo' => app(TenantAssetService::class)->companyLogo($owned),
                ];
            }
        );
    }

    protected function workspace(): ?array
    {
        if (! tenant()) {
            return null;
        }
        $tenantId = tenant('id');

        return Cache::remember(
            "share:workspace:{$tenantId}",
            now()->addMinutes(15),
            function () use ($tenantId) {
                $t = Tenant::on('mysql')->find($tenantId);
                $settings = CompanySetting::select('company_name', 'logo_url')
                    ->first();
                return [
                    'company_name' => $settings->company_name ?? 'Workspace',
                    'logo_url' => $settings->logo_url,
                    'enabled_modules' => $t?->enabledModules() ?? [],
                ];
            }
        );
    }
}
