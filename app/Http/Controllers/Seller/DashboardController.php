<?php

declare(strict_types=1);

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Commission;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $seller = auth()->user()->sellerProfile;

        abort_unless($seller, 403, 'Seller profile not found');

        $wallet = $seller->wallet;
        $now = now();
        $start30 = $now->copy()->subDays(29)->startOfDay();
        $end = $now->copy()->endOfDay();

        $approvedModules = $seller->moduleRequests()->where('status', 'approved')->count();
        $pendingRequests = $seller->moduleRequests()->where('status', 'pending')->count();

        // this month vs last month earning (growth)
        $thisMonth = (float) Commission::where('seller_id', $seller->id)
            ->where('created_at', '>=', $now->copy()->startOfMonth())->sum('amount');
        $lastMonth = (float) Commission::where('seller_id', $seller->id)
            ->whereBetween('created_at', [$now->copy()->subMonth()->startOfMonth(), $now->copy()->startOfMonth()->subSecond()])
            ->sum('amount');

        $growth = $lastMonth > 0 ? round((($thisMonth - $lastMonth) / $lastMonth) * 100, 1) : ($thisMonth > 0 ? 100 : 0);

        // daily earnings (30d) — line chart
        $daily = Commission::where('seller_id', $seller->id)
            ->whereBetween('created_at', [$start30, $end])
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(amount) as amount'))
            ->groupBy('date')->orderBy('date')->get()->keyBy('date');

        $earningsChart = [];
        for ($d = $start30->copy(); $d <= $end; $d->addDay()) {
            $key = $d->format('Y-m-d');
            $earningsChart[] = [
                'date'   => $d->format('d M'),
                'amount' => (float) ($daily[$key]->amount ?? 0),
            ];
        }

        // commission status breakdown — donut
        $statusBreakdown = [
            'pending'   => (float) Commission::where('seller_id', $seller->id)->where('status', 'pending')->sum('amount'),
            'approved'  => (float) Commission::where('seller_id', $seller->id)->where('status', 'approved')->sum('amount'),
            'paid'      => (float) Commission::where('seller_id', $seller->id)->where('status', 'paid')->sum('amount'),
        ];

        // earning by module — top modules seller sold
        $byModule = Commission::where('commissions.seller_id', $seller->id)
            ->join('sales', 'commissions.sale_id', '=', 'sales.id')
            ->select('sales.module_id', DB::raw('SUM(commissions.amount) as amount'), DB::raw('COUNT(*) as count'))
            ->groupBy('sales.module_id')->orderByDesc('amount')->limit(5)
            ->with('sale.module')->get()
            ->map(function ($c) {
                $module = \App\Models\ModulePackage::find($c->module_id);
                return ['module' => $module?->name ?? '—', 'amount' => (float) $c->amount, 'count' => (int) $c->count];
            });

        // recent commissions
        $recentCommissions = Commission::where('seller_id', $seller->id)
            ->with(['sale.module', 'sale.tenant'])
            ->latest()->limit(5)->get()
            ->map(fn ($c) => [
                'module_name' => $c->sale?->module?->name ?? '—',
                'tenant_name' => $c->sale?->tenant?->name ?? '—',
                'amount'      => $c->amount,
                'status'      => $c->status,
                'created_at'  => $c->created_at?->format('d M Y'),
            ]);

        return Inertia::render('Seller/Dashboard', [
            'seller' => [
                'name'            => $seller->user?->name,
                'referral_code'   => $seller->referral_code,
                'commission_rate' => $seller->commission_rate,
                'status'          => $seller->status,
            ],
            'stats' => [
                'available_balance' => $wallet?->available_balance ?? 0,
                'pending_balance'   => $wallet?->pending_balance ?? 0,
                'total_earned'      => $wallet?->total_earned ?? 0,
                'total_sales'       => $seller->total_sales,
                'approved_modules'  => $approvedModules,
                'pending_requests'  => $pendingRequests,
                'this_month'        => $thisMonth,
                'growth'            => $growth,
            ],
            'earningsChart'   => $earningsChart,
            'statusBreakdown' => $statusBreakdown,
            'byModule'        => $byModule,
            'recent_commissions' => $recentCommissions,
        ]);
    }
}
