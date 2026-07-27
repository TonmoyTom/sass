<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessSubscriptionRenewal;
use App\Models\TenantModule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;

class TenantModuleUsageController extends Controller
{
    public function index(Request $request): Response
    {
        $usage = TenantModule::query()
            ->with(['tenant.owner', 'module', 'tier'])
            ->filterAndCache(
                $request,
                searchable: ['tenant.name', 'module.name'],
                filterable: ['status', 'access_type', 'billing_cycle'],
                sortable: ['purchased_at', 'expires_at', 'price_paid'],
                ttlSeconds: 180,
                perPage: 25,
                transform: fn ($tm) => [
                    'id' => $tm->id,
                    'tenant_name' => $tm->tenant?->name ?? '—',
                    'tenant_owner_email' => $tm->tenant?->owner?->email,
                    'module_name' => $tm->module?->name ?? '—',
                    'tier_name' => $tm->tier?->name,
                    'status' => $tm->status,
                    'access_type' => $tm->access_type,
                    'billing_cycle' => $tm->billing_cycle,
                    'price_paid' => $tm->price_paid,
                    'purchased_at' => $tm->purchased_at?->format('d M Y'),
                    'expires_at' => $tm->expires_at?->format('d M Y'),
                    'is_expired' => $tm->expires_at && $tm->expires_at->isPast(),
                    'is_expiring_soon' => $tm->expires_at && $tm->expires_at->isBetween(now(), now()->addDays(7)),   // ← add
                ]
            );

        // summary stats
        $stats = Cache::store('redis')
            ->tags(['table:'.(new TenantModule)->getTable()])
            ->remember('tenant_module_usage_stats', 180, fn () => [
                'total_active' => TenantModule::where('status', 'active')->count(),
                'total_expired' => TenantModule::whereNotNull('expires_at')
                    ->where('expires_at', '<', now())
                    ->count(),
                'unique_tenants' => TenantModule::distinct('tenant_id')->count('tenant_id'),
            ]);

        return Inertia::render('Admin/TenantModuleUsage/Index', [
            'usage' => $usage,
            'stats' => $stats,
            'filters' => [
                'search' => $request->input('search', ''),
                'status' => $request->input('status', ''),
                'access_type' => $request->input('access_type', ''),
                'sort_by' => $request->input('sort_by', 'purchased_at'),
                'sort_dir' => $request->input('sort_dir', 'desc'),
            ],
        ]);
    }

    public function freeRenew(Request $request, TenantModule $tenantModule): RedirectResponse
    {
        $data = $request->validate([
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        if ($tenantModule->access_type === 'lifetime') {
            return back()->with('error', 'This module has lifetime access, no renewal needed.');
        }

        if ($tenantModule->status !== 'active') {
            return back()->with('error', 'This module is not currently active.');
        }

        ProcessSubscriptionRenewal::dispatchSync(
            $tenantModule->id,
            skipCommission: true,
            isFree: true,
            freeNote: $data['note'] ?? null,
            renewedBy : auth()->id()
        );

        return back()->with('status', 'Module renewed for free successfully.');
    }

    public function renew(Request $request, TenantModule $tenantModule): RedirectResponse
    {
        $data = $request->validate([
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        if ($tenantModule->access_type === 'lifetime') {
            return back()->with('error', 'This module has lifetime access, no renewal needed.');
        }

        if ($tenantModule->status !== 'active') {
            return back()->with('error', 'This module is not currently active.');
        }

        ProcessSubscriptionRenewal::dispatchSync(
            $tenantModule->id,
        );

        return back()->with('status', 'Module renewed  successfully.');
    }
}
