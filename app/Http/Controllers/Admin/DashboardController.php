<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Commission;
use App\Models\Sale;
use App\Models\Seller;
use App\Models\SellerModuleRequest;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $now = now();
        $thisMonthStart = $now->copy()->startOfMonth();
        $lastMonthStart = $now->copy()->subMonth()->startOfMonth();
        $lastMonthEnd = $now->copy()->startOfMonth()->subSecond();
        $start30 = $now->copy()->subDays(29)->startOfDay();
        $end = $now->copy()->endOfDay();

        // growth helper
        $growth = fn ($current, $previous) => $previous > 0
            ? round((($current - $previous) / $previous) * 100, 1)
            : ($current > 0 ? 100 : 0);

        // this vs last month
        $thisRevenue = (float) Sale::where('status', 'completed')->where('sold_at', '>=', $thisMonthStart)->sum('amount');
        $lastRevenue = (float) Sale::where('status', 'completed')->whereBetween('sold_at', [$lastMonthStart, $lastMonthEnd])->sum('amount');
        $thisOrders = Sale::where('status', 'completed')->where('sold_at', '>=', $thisMonthStart)->count();
        $lastOrders = Sale::where('status', 'completed')->whereBetween('sold_at', [$lastMonthStart, $lastMonthEnd])->count();
        $thisTenants = Tenant::where('created_at', '>=', $thisMonthStart)->count();
        $lastTenants = Tenant::whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])->count();
        $thisSellers = Seller::where('created_at', '>=', $thisMonthStart)->count();
        $lastSellers = Seller::whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])->count();

        $stats = [
            'revenue' => ['value' => (float) Sale::where('status', 'completed')->sum('amount'), 'growth' => $growth($thisRevenue, $lastRevenue)],
            'orders' => ['value' => Sale::where('status', 'completed')->count(), 'growth' => $growth($thisOrders, $lastOrders)],
            'tenants' => ['value' => Tenant::count(), 'growth' => $growth($thisTenants, $lastTenants)],
            'sellers' => ['value' => Seller::where('status', 'active')->count(), 'growth' => $growth($thisSellers, $lastSellers)],
        ];

        // commission cards
        $commissionStats = [
            'total_paid' => (float) Commission::whereIn('status', ['approved', 'paid'])->sum('amount'),
            'pending' => (float) Commission::where('status', 'pending')->sum('amount'),
            'available' => (float) Commission::where('status', 'approved')->sum('amount'),
        ];

        // alerts
        $alerts = [
            'pending_sellers' => Seller::where('status', 'pending')->count(),
            'pending_commission' => Commission::where('status', 'pending')->count(),
            'pending_requests' => SellerModuleRequest::where('status', 'pending')->count(),
        ];

        // daily revenue + orders (30d)
        $daily = Sale::where('status', 'completed')->whereBetween('sold_at', [$start30, $end])
            ->select(DB::raw('DATE(sold_at) as date'), DB::raw('SUM(amount) as revenue'), DB::raw('COUNT(*) as orders'))
            ->groupBy('date')->orderBy('date')->get()->keyBy('date');

        $revenueChart = [];
        for ($d = $start30->copy(); $d <= $end; $d->addDay()) {
            $key = $d->format('Y-m-d');
            $revenueChart[] = [
                'date' => $d->format('d M'),
                'revenue' => (float) ($daily[$key]->revenue ?? 0),
                'orders' => (int) ($daily[$key]->orders ?? 0),
            ];
        }

        // monthly comparison (last 6 months) — bar
        $monthly = [];
        for ($i = 5; $i >= 0; $i--) {
            $mStart = $now->copy()->subMonths($i)->startOfMonth();
            $mEnd = $now->copy()->subMonths($i)->endOfMonth();
            $monthly[] = [
                'month' => $mStart->format('M'),
                'revenue' => (float) Sale::where('status', 'completed')->whereBetween('sold_at', [$mStart, $mEnd])->sum('amount'),
                'orders' => Sale::where('status', 'completed')->whereBetween('sold_at', [$mStart, $mEnd])->count(),
            ];
        }

        // revenue by module — donut
        $byModule = Sale::where('status', 'completed')
            ->select('module_id', DB::raw('SUM(amount) as revenue'))
            ->groupBy('module_id')->orderByDesc('revenue')->with('module')->get()
            ->map(fn ($s) => ['module' => $s->module?->name ?? 'Unknown', 'revenue' => (float) $s->revenue]);

        // tenant status — bar
        $tenantStatus = [
            'active' => Tenant::where('status', 'active')->count(),
            'pending' => Tenant::where('status', 'pending')->count(),
            'suspended' => Tenant::where('status', 'suspended')->count(),
        ];

        // top sellers
        $topSellers = Sale::where('status', 'completed')->whereNotNull('seller_id')
            ->select('seller_id', DB::raw('SUM(amount) as revenue'), DB::raw('COUNT(*) as orders'))
            ->groupBy('seller_id')->orderByDesc('revenue')->limit(5)->with('seller.user')->get()
            ->map(fn ($s) => ['name' => $s->seller?->user?->name ?? '—', 'revenue' => (float) $s->revenue, 'orders' => (int) $s->orders]);

        // top modules
        $topModules = Sale::where('status', 'completed')
            ->select('module_id', DB::raw('SUM(amount) as revenue'), DB::raw('COUNT(*) as orders'))
            ->groupBy('module_id')->orderByDesc('revenue')->limit(5)->with('module')->get()
            ->map(fn ($s) => ['name' => $s->module?->name ?? '—', 'revenue' => (float) $s->revenue, 'orders' => (int) $s->orders]);

        // recent orders
        $recentOrders = Sale::with(['module', 'tenant'])->latest()->limit(6)->get()
            ->map(fn ($s) => ['id' => $s->id, 'tenant' => $s->tenant?->name ?? '—', 'module' => $s->module?->name ?? '—', 'amount' => (float) $s->amount, 'status' => $s->status, 'date' => $s->created_at?->format('d M')]);

        // recent tenants
        $recentTenants = Tenant::with('owner')->latest()->limit(6)->get()
            ->map(fn ($t) => ['name' => $t->name, 'email' => $t->owner?->email, 'status' => $t->status, 'date' => $t->created_at?->format('d M')]);

        return Inertia::render('Admin/Dashboard', [
            'stats' => $stats,
            'commissionStats' => $commissionStats,
            'alerts' => $alerts,
            'revenueChart' => $revenueChart,
            'monthly' => $monthly,
            'byModule' => $byModule,
            'tenantStatus' => $tenantStatus,
            'topSellers' => $topSellers,
            'topModules' => $topModules,
            'recentOrders' => $recentOrders,
            'recentTenants' => $recentTenants,
        ]);
    }
}
