<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\CompanySetting;
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
        $tenant = $user->ownedTenants()->with(['domains', 'owner.info'])->firstOrFail();

        // ── Purchased modules ──
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
            ])->values();

        $logoUrl = null;
        $logoPath = null;

        $tenant->run(function () use (&$logoPath) {
            $company = CompanySetting::first();
            $logoPath = $company?->logo;
        });

        $now = now();
        $start = $now->copy()->subMonths(11)->startOfMonth();

        $baseQuery = fn () => Sale::where('tenant_id', $tenant->id);

        $rows = $baseQuery()
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

        $revenue = (float) $baseQuery()->where('status', 'completed')->sum('amount');
        $totalOrders = $baseQuery()->count();
        $completed = $baseQuery()->where('status', 'completed')->count();
        $pending = $baseQuery()->where('status', 'pending')->count();

        $thisMonth = $chart[11] ?? 0;
        $lastMonth = $chart[10] ?? 0;
        $changePct = $lastMonth > 0
            ? round((($thisMonth - $lastMonth) / $lastMonth) * 100, 1)
            : ($thisMonth > 0 ? 100 : 0);

        $sales = [
            'revenue' => $revenue,
            'change_pct' => $changePct,
            'total_orders' => $totalOrders,
            'avg_order_value' => $completed ? round($revenue / $completed) : 0,
            'pending' => $pending,
            'chart' => $chart,
        ];

        // ── Recent orders (ei tenant er) ──
        $recentOrders = $baseQuery()
            ->with(['module', 'tier'])
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

        // ── Owner + tenant meta ──
        $owner = $tenant->owner;

        return Inertia::render('Tenant/Dashboard', [
            'tenant' => [
                'name' => $tenant->name,
                'status' => $tenant->status,
                'logo' => $assets->companyLogo($tenant) ?? null,
                'domain' => $tenant->domains->first()?->domain,
                'plan' => optional($modules->first())['tier_name'] ?? 'Free',
                'region' => $tenant->region ?? 'Asia · Dhaka',
                'renews_at' => $tenant->trial_ends_at?->format('d M Y'),
                'created_at' => $tenant->created_at?->format('d M Y'),
            ],
            'owner' => [
                'name' => $owner?->name,
                'email' => $owner?->email,
                'phone' => $owner?->phone,
                'avatar' => $assets->ownerAvatar($tenant) ?? null,
                'role' => 'Owner',
            ],
            'sales' => $sales,
            'recentOrders' => $recentOrders,
            'modules' => $modules,
            'stats' => [
                'total_modules' => $modules->count(),
                'active_modules' => $modules->where('is_expired', false)->count(),
            ],
        ]);
    }
}
