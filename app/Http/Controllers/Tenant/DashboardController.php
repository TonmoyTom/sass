<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\TenantModule;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $user = auth()->user();
        $tenant = $user->ownedTenants()->with('domains')->firstOrFail();
        $modules = TenantModule::where('tenant_id', $tenant->id)
            ->whereIn('status', ['active', 'purchased', 'trial'])
            ->with(['module', 'tier'])
            ->get()
            ->map(fn ($tm) => [
                'id' => $tm->id,
                'name' => $tm->module?->name,
                'tier_name' => $tm->tier?->name,
                'icon' => $tm->module?->icon,
                'status' => $tm->status,
                'billing_cycle' => $tm->billing_cycle,
                'expires_at' => $tm->expires_at?->format('d M Y'),
                'is_expired' => $tm->expires_at && $tm->expires_at->isPast(),
            ]);

        // subdomain URL
        $domain = $tenant->domains->first()?->domain;

        return Inertia::render('Tenant/Dashboard', [
            'tenant' => [
                'name' => $tenant->name,
                'subdomain' => $domain,
            ],
            'modules' => $modules,
            'stats' => [
                'total_modules' => $modules->count(),
                'active_modules' => $modules->where('is_expired', false)->count(),
            ],
        ]);
    }
}
