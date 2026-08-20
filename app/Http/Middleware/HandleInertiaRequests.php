<?php

namespace App\Http\Middleware;

use App\Models\CompanySetting;
use App\Models\Tenant;
use App\Models\User;
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
        $impersonatorId = session('impersonator_id');
        $user = $request->user();

        return [
            ...parent::share($request),
            'centralDomain' => config('app.central_domain'),
            'impersonating' => (bool) $impersonatorId,
            'impersonator' => $impersonatorId
                ? User::find($impersonatorId)?->only(['id', 'name'])
                : null,
            'auth' => [
                'permissions' => $user ? $user->getAllPermissions()->pluck('name') : [],
                'user' => fn () => $user?->only('id', 'name', 'email', 'user_type'),
            ],

            // ── central side: owner's tenant ──
            'tenant' => fn () => $this->ownedTenant($request),

            // ── tenant side: subdomain context ──
            'workspace' => fn () => $this->workspace(),

            // ── central platform's own branding (logo/favicon/name) ──
            'company' => fn () => $this->company(),
        ];
    }

    protected function ownedTenant(Request $request): ?array
    {
        $user = $request->user();
        if (! $user || ! method_exists($user, 'ownedTenants')) {
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
     
            // Only tenant-wide, role-independent data is cached here — company
            // branding and the tenant's purchased module list rarely change and
            // are the same for every user. Anything permission-sensitive (who
            // can see which module) is computed fresh below, every request, so
            // a role/permission change takes effect immediately instead of
            // waiting out a stale cache and never being served to the wrong user.
            $tenantWide = Cache::remember(
                "share:workspace:{$tenantId}",
                now()->addMinutes(15),
                function () use ($tenantId) {
                    $t = Tenant::on('mysql')->find($tenantId);
                    $settings = CompanySetting::select('company_name', 'logo')
                        ->first();
     
                    return [
                        'tenant' => [
                            'id' => $tenantId,
                        ],
                        'company_name' => $settings->company_name ?? 'Workspace',
                        'logo_url' => $settings->logo_url,
                        'purchased_modules' => $t?->enabledModules() ?? [],
                    ];
                }
            );
     
            $user = auth('tenant')->user();
            $isSuperAdmin = $user?->roles?->contains('id', 1) ?? false;
     
            if ($isSuperAdmin) {
                // Super Admin always sees everything the tenant owns.
                $accessibleModules = $tenantWide['purchased_modules'];
            } elseif ($user) {
                $grantedModules = $user->roles->load('moduleGrants')
                    ->flatMap(fn ($role) => $role->grantedModuleAliases())
                    ->unique()
                    ->all();
     
                $accessibleModules = array_values(array_intersect($tenantWide['purchased_modules'], $grantedModules));
            } else {
                $accessibleModules = [];
            }
     
            return [
                'tenant' => $tenantWide['tenant'],
                'company_name' => $tenantWide['company_name'],
                'logo_url' => $tenantWide['logo_url'],
                'enabled_modules' => $accessibleModules,
                'user' => $user
            ];
        }

    /**
     * Central platform's own company branding — shudhu tenant() null hole (central context)
     */
    protected function company(): ?array
    {
        if (tenant()) {
            return null;
        }

        return Cache::remember(
            'share:company:central',
            now()->addMinutes(15),
            function () {
                $settings = CompanySetting::select('company_name', 'logo', 'favicon')->first();

                if (! $settings) {
                    return [
                        'name' => config('app.name'),
                        'logo_url' => null,
                        'favicon_url' => null,
                    ];
                }

                return [
                    'name' => $settings->company_name ?? config('app.name'),
                    'logo_url' => $settings->logo_url,
                    'favicon_url' => $settings->favicon_url,
                ];
            }
        );
    }
}
