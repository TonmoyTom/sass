<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\TenantModule;
use App\Services\TenantAssetService;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(TenantAssetService $assets): Response
    {
        $user = auth()->user();
        $tenant = $user->ownedTenants()->with(['domains', 'owner'])->firstOrFail();

        $tenantId = $tenant->id;
        $now = now();
        $start = $now->copy()->subMonths(11)->startOfMonth();

        // ── Parallel-friendly: sob query ekসাথে define ──
        $modules = TenantModule::where('tenant_id', $tenantId)
            ->whereIn('status', ['active', 'purchased', 'trial'])
            ->with(['module:id,name,icon', 'tier:id,name'])
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
            ])->values();

        // ── 4 query → 1 query ──
        $stats = Sale::where('tenant_id', $tenantId)
            ->selectRaw("
            COALESCE(SUM(amount), 0)                                              as revenue,
            COUNT(*)                                                               as total_orders,
            SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END)                as completed,
            SUM(CASE WHEN status = 'pending'   THEN 1 ELSE 0 END)                as pending,
            SUM(CASE WHEN status = 'completed' THEN amount ELSE 0 END)           as completed_amount
        ")
            ->first();

        $revenue = (float) $stats->revenue;
        $totalOrders = (int) $stats->total_orders;
        $completed = (int) $stats->completed;
        $pending = (int) $stats->pending;

        // ── Chart: শুধু completed + date range ──
        $rows = Sale::where('tenant_id', $tenantId)
            ->where('status', 'completed')
            ->where('sold_at', '>=', $start)
            ->selectRaw("DATE_FORMAT(sold_at, '%Y-%m') as ym, SUM(amount) as t")
            ->groupBy('ym')
            ->pluck('t', 'ym');

        $chart = [];
        for ($i = 0; $i < 12; $i++) {
            $key = $start->copy()->addMonths($i)->format('Y-m');
            $chart[] = (float) ($rows[$key] ?? 0);
        }

        $thisMonth = $chart[11] ?? 0;
        $lastMonth = $chart[10] ?? 0;
        $changePct = $lastMonth > 0
            ? round((($thisMonth - $lastMonth) / $lastMonth) * 100, 1)
            : ($thisMonth > 0 ? 100 : 0);

        // ── Recent orders ──
        $recentOrders = Sale::where('tenant_id', $tenantId)
            ->with(['module:id,name', 'tier:id,name'])
            ->latest('sold_at')
            ->take(5)
            ->get()
            ->map(fn ($s) => [
                'id' => $s->id,
                'customer' => $s->module?->name ?? '—',
                'date' => $s->sold_at?->format('d M Y'),
                'item_count' => 1,
                'amount' => $s->amount,
                'status' => $s->status,
            ])->all();

        $owner = $tenant->owner;

        return Inertia::render('Tenant/Dashboard', [
            'tenant' => [
                'name' => $tenant->name,
                'status' => $tenant->status,
                'logo' => $assets->companyLogo($tenant),
                'domain' => $tenant->domains->first()?->domain,
                'plan' => $modules->first()['tier_name'] ?? 'Free',
                'region' => $tenant->region ?? 'Asia · Dhaka',
                'renews_at' => $tenant->trial_ends_at?->format('d M Y'),
                'created_at' => $tenant->created_at?->format('d M Y'),
            ],
            'owner' => [
                'name' => $owner?->name,
                'email' => $owner?->email,
                'phone' => $owner?->phone,
                'avatar' => $assets->ownerAvatar($tenant),
                'role' => 'Owner',
            ],
            'sales' => [
                'revenue' => $revenue,
                'change_pct' => $changePct,
                'total_orders' => $totalOrders,
                'avg_order_value' => $completed ? round($revenue / $completed) : 0,
                'pending' => $pending,
                'chart' => $chart,
            ],
            'recentOrders' => $recentOrders,
            'modules' => $modules,
            'stats' => [
                'total_modules' => $modules->count(),
                'active_modules' => $modules->where('is_expired', false)->count(),
            ],
        ]);
    }
}
