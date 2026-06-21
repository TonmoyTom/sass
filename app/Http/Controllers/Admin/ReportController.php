<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Commission;
use App\Models\Sale;
use App\Models\Seller;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ReportController extends Controller
{
    public function index(): Response
    {
        $start = now()->subDays(29)->startOfDay();  // last 30 days
        $end = now()->endOfDay();

        // overall stats
        $stats = [
            'total_revenue' => Sale::where('status', 'completed')->sum('amount'),
            'month_revenue' => Sale::where('status', 'completed')->whereBetween('sold_at', [$start, $end])->sum('amount'),
            'total_orders' => Sale::where('status', 'completed')->count(),
            'month_orders' => Sale::where('status', 'completed')->whereBetween('sold_at', [$start, $end])->count(),
            'total_tenants' => Tenant::count(),
            'total_sellers' => Seller::where('status', 'active')->count(),
        ];

        // daily revenue (last 30 days) — chart
        $daily = Sale::where('status', 'completed')
            ->whereBetween('sold_at', [$start, $end])
            ->select(
                DB::raw('DATE(sold_at) as date'),
                DB::raw('SUM(amount) as revenue'),
                DB::raw('COUNT(*) as orders')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        // sob din fill (data nai emn din-e 0)
        $chart = [];
        for ($d = $start->copy(); $d <= $end; $d->addDay()) {
            $key = $d->format('Y-m-d');
            $chart[] = [
                'date' => $d->format('d M'),
                'revenue' => (float) ($daily[$key]->revenue ?? 0),
                'orders' => (int) ($daily[$key]->orders ?? 0),
            ];
        }

        // top sellers (revenue-bhittik, last 30 days)
        $topSellers = Sale::where('sales.status', 'completed')
            ->whereBetween('sold_at', [$start, $end])
            ->whereNotNull('seller_id')
            ->select('seller_id', DB::raw('SUM(amount) as revenue'), DB::raw('COUNT(*) as orders'))
            ->groupBy('seller_id')
            ->orderByDesc('revenue')
            ->limit(5)
            ->with('seller.user')
            ->get()
            ->map(fn ($s) => [
                'name' => $s->seller?->user?->name ?? '—',
                'revenue' => (float) $s->revenue,
                'orders' => (int) $s->orders,
            ]);

        // top modules
        $topModules = Sale::where('sales.status', 'completed')
            ->whereBetween('sold_at', [$start, $end])
            ->select('module_id', DB::raw('SUM(amount) as revenue'), DB::raw('COUNT(*) as orders'))
            ->groupBy('module_id')
            ->orderByDesc('revenue')
            ->limit(5)
            ->with('module')
            ->get()
            ->map(fn ($s) => [
                'name' => $s->module?->name ?? '—',
                'revenue' => (float) $s->revenue,
                'orders' => (int) $s->orders,
            ]);

        return Inertia::render('Admin/Reports/Index', [
            'stats' => $stats,
            'chart' => $chart,
            'topSellers' => $topSellers,
            'topModules' => $topModules,
            'range' => [
                'start' => $start->format('d M Y'),
                'end' => $end->format('d M Y'),
            ],
        ]);
    }

    /**
     * Revenue report — sales trend + breakdown.
     */
    public function revenue(): Response
    {
        $start = now()->subDays(29)->startOfDay();
        $end = now()->endOfDay();

        $base = Sale::where('status', 'completed')->whereBetween('sold_at', [$start, $end]);

        // summary
        $summary = [
            'total_revenue' => (clone $base)->sum('amount'),
            'total_orders' => (clone $base)->count(),
            'commission_paid' => (clone $base)->sum('commission_amount'),
            'platform_earning' => (clone $base)->sum('admin_amount'),
            'avg_order' => round((clone $base)->avg('amount') ?? 0, 2),
        ];

        // daily trend
        $daily = (clone $base)
            ->select(DB::raw('DATE(sold_at) as date'), DB::raw('SUM(amount) as revenue'), DB::raw('COUNT(*) as orders'))
            ->groupBy('date')->orderBy('date')->get()->keyBy('date');

        $chart = [];
        for ($d = $start->copy(); $d <= $end; $d->addDay()) {
            $key = $d->format('Y-m-d');
            $chart[] = [
                'date' => $d->format('d M'),
                'revenue' => (float) ($daily[$key]->revenue ?? 0),
                'orders' => (int) ($daily[$key]->orders ?? 0),
            ];
        }

        // sale type breakdown (subscription/module/addon)
        $byType = (clone $base)
            ->select('sale_type', DB::raw('SUM(amount) as revenue'), DB::raw('COUNT(*) as orders'))
            ->groupBy('sale_type')->get()
            ->map(fn ($r) => [
                'type' => $r->sale_type,
                'revenue' => (float) $r->revenue,
                'orders' => (int) $r->orders,
            ]);

        // module breakdown
        $byModule = (clone $base)
            ->select('module_id', DB::raw('SUM(amount) as revenue'), DB::raw('COUNT(*) as orders'))
            ->groupBy('module_id')->orderByDesc('revenue')->with('module')->get()
            ->map(fn ($r) => [
                'module' => $r->module?->name ?? '—',
                'revenue' => (float) $r->revenue,
                'orders' => (int) $r->orders,
            ]);

        return Inertia::render('Admin/Reports/Revenue', [
            'summary' => $summary,
            'chart' => $chart,
            'byType' => $byType,
            'byModule' => $byModule,
            'range' => ['start' => $start->format('d M Y'), 'end' => $end->format('d M Y')],
        ]);
    }

    /**
     * Tenant report — tenant-wise revenue + activity.
     */
    public function tenants(): Response
    {
        $start = now()->subDays(29)->startOfDay();
        $end = now()->endOfDay();

        // summary
        $summary = [
            'total_tenants' => Tenant::count(),
            'active_tenants' => Tenant::where('status', 'active')->count(),
            'new_this_month' => Tenant::whereBetween('created_at', [$start, $end])->count(),
            'paying_tenants' => Sale::where('status', 'completed')->distinct('tenant_id')->count('tenant_id'),
        ];

        // top tenants (revenue-bhittik, all-time)
        $topTenants = Sale::where('status', 'completed')
            ->select('tenant_id', DB::raw('SUM(amount) as revenue'), DB::raw('COUNT(*) as orders'))
            ->groupBy('tenant_id')->orderByDesc('revenue')->limit(10)
            ->with('tenant.owner')->get()
            ->map(fn ($s) => [
                'tenant' => $s->tenant?->name ?? '—',
                'email' => $s->tenant?->owner?->email,
                'revenue' => (float) $s->revenue,
                'orders' => (int) $s->orders,
            ]);

        // recent tenants
        $recent = Tenant::with('owner')->latest()->limit(10)->get()
            ->map(fn ($t) => [
                'name' => $t->name,
                'email' => $t->owner?->email,
                'status' => $t->status,
                'created_at' => $t->created_at?->format('d M Y'),
            ]);

        return Inertia::render('Admin/Reports/Tenants', [
            'summary' => $summary,
            'topTenants' => $topTenants,
            'recent' => $recent,
            'range' => ['start' => $start->format('d M Y'), 'end' => $end->format('d M Y')],
        ]);
    }

    /**
     * Seller report — seller performance + commission.
     */
    public function sellers(): Response
    {
        $start = now()->subDays(29)->startOfDay();
        $end = now()->endOfDay();

        // summary
        $summary = [
            'total_sellers' => Seller::count(),
            'active_sellers' => Seller::where('status', 'active')->count(),
            'pending_sellers' => Seller::where('status', 'pending')->count(),
            'total_commission' => Commission::whereIn('status', ['approved', 'paid'])->sum('amount'),
        ];

        // top sellers (revenue-bhittik, all-time)
        $topSellers = Sale::where('status', 'completed')
            ->whereNotNull('seller_id')
            ->select('seller_id', DB::raw('SUM(amount) as revenue'), DB::raw('SUM(commission_amount) as commission'), DB::raw('COUNT(*) as orders'))
            ->groupBy('seller_id')->orderByDesc('revenue')->limit(10)
            ->with('seller.user')->get()
            ->map(fn ($s) => [
                'name' => $s->seller?->user?->name ?? '—',
                'email' => $s->seller?->user?->email,
                'revenue' => (float) $s->revenue,
                'commission' => (float) $s->commission,
                'orders' => (int) $s->orders,
            ]);

        return Inertia::render('Admin/Reports/Sellers', [
            'summary' => $summary,
            'topSellers' => $topSellers,
            'range' => ['start' => $start->format('d M Y'), 'end' => $end->format('d M Y')],
         ]);
    }
}
