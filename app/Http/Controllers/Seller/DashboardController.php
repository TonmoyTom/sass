<?php

declare(strict_types=1);

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Commission;
use App\Models\ModulePackage;
use App\Models\WithdrawRequest;
use Illuminate\Http\Request;
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

        // ── Module requests ──
        $moduleStats = $seller->moduleRequests()
            ->selectRaw("
                SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
                SUM(CASE WHEN status = 'pending'  THEN 1 ELSE 0 END) as pending
            ")
            ->first();

        // ── Commission: this month vs last month (1 query) ──
        $monthlyStats = Commission::where('seller_id', $seller->id)
            ->selectRaw('
                SUM(CASE WHEN created_at >= ? THEN amount ELSE 0 END) as this_month,
                SUM(CASE WHEN created_at >= ? AND created_at < ?   THEN amount ELSE 0 END) as last_month
            ', [
                $now->copy()->startOfMonth(),
                $now->copy()->subMonth()->startOfMonth(),
                $now->copy()->startOfMonth(),
            ])
            ->first();

        $thisMonth = (float) $monthlyStats->this_month;
        $lastMonth = (float) $monthlyStats->last_month;
        $growth = $lastMonth > 0
            ? round((($thisMonth - $lastMonth) / $lastMonth) * 100, 1)
            : ($thisMonth > 0 ? 100 : 0);

        // ── Commission status breakdown (1 query) ──
        $breakdown = Commission::where('seller_id', $seller->id)
            ->selectRaw("
                SUM(CASE WHEN status = 'pending'  THEN amount ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'approved' THEN amount ELSE 0 END) as approved,
                SUM(CASE WHEN status = 'paid'     THEN amount ELSE 0 END) as paid
            ")
            ->first();

        $statusBreakdown = [
            'pending' => (float) $breakdown->pending,
            'approved' => (float) $breakdown->approved,
            'paid' => (float) $breakdown->paid,
        ];

        // ── Daily earnings chart (30d) ──
        $daily = Commission::where('seller_id', $seller->id)
            ->whereBetween('created_at', [$start30, $end])
            ->selectRaw('DATE(created_at) as date, SUM(amount) as amount')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $earningsChart = [];
        for ($d = $start30->copy(); $d <= $end; $d->addDay()) {
            $key = $d->format('Y-m-d');
            $earningsChart[] = [
                'date' => $d->format('d M'),
                'amount' => (float) ($daily[$key]->amount ?? 0),
            ];
        }

        // ── Earning by module (top 5) ──
        $byModule = Commission::where('commissions.seller_id', $seller->id)
            ->join('sales', 'commissions.sale_id', '=', 'sales.id')
            ->selectRaw('sales.module_id, SUM(commissions.amount) as amount, COUNT(*) as count')
            ->groupBy('sales.module_id')
            ->orderByDesc('amount')
            ->limit(5)
            ->get()
            ->map(function ($c) {
                $module = ModulePackage::find($c->module_id);

                return [
                    'module' => $module?->name ?? '—',
                    'amount' => (float) $c->amount,
                    'count' => (int) $c->count,
                ];
            });

        // ── Recent commissions ──
        $recentCommissions = Commission::where('seller_id', $seller->id)
            ->with(['sale.module', 'sale.tenant'])
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn ($c) => [
                'module_name' => $c->sale?->module?->name ?? '—',
                'tenant_name' => $c->sale?->tenant?->name ?? '—',
                'amount' => $c->amount,
                'status' => $c->status,
                'created_at' => $c->created_at?->format('d M Y'),
            ]);

        // ── Withdraw requests ──
        $withdrawRequests = WithdrawRequest::where('seller_id', $seller->id)
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn ($w) => [
                'id' => $w->id,
                'amount' => $w->amount,
                'paid_amount' => $w->paid_amount,
                'method' => $w->method,
                'status' => $w->status,
                'created_at' => $w->created_at?->format('d M Y, h:i A'),
            ]);

        $pendingWithdraw = WithdrawRequest::where('seller_id', $seller->id)
            ->where('status', 'pending')
            ->exists();

        $withdrawStats = WithdrawRequest::where('seller_id', $seller->id)
            ->selectRaw("
        SUM(CASE WHEN status = 'pending'  THEN amount ELSE 0 END)      as pending_amount,
        SUM(CASE WHEN status = 'approved' THEN paid_amount ELSE 0 END) as paid_amount,
        SUM(CASE WHEN status = 'approved' THEN (amount - paid_amount) ELSE 0 END) as refunded_amount,
        SUM(amount)                                                     as total_requested,
        COUNT(CASE WHEN status = 'pending' THEN 1 END)                 as pending_count
    ")
            ->first();

        return Inertia::render('Seller/Dashboard', [
            'seller' => [
                'name' => $seller->user?->name,
                'referral_code' => $seller->referral_code,
                'commission_rate' => $seller->commission_rate,
                'status' => $seller->status,
            ],
            'stats' => [
                'available_balance' => $wallet?->available_balance ?? 0,
                'pending_balance' => $wallet?->pending_balance ?? 0,
                'total_earned' => $wallet?->total_earned ?? 0,
                'total_withdrawn' => $wallet?->total_withdrawn ?? 0,  // ← add
                'total_sales' => $seller->total_sales,
                'approved_modules' => (int) $moduleStats->approved,
                'pending_requests' => (int) $moduleStats->pending,
                'this_month' => $thisMonth,
                'growth' => $growth,
                'pending_withdraw' => (float) $withdrawStats->pending_amount,
                'paid_out' => (float) $withdrawStats->paid_amount,
                'refunded' => (float) $withdrawStats->refunded_amount,
                'total_requested' => (float) $withdrawStats->total_requested,
                'pending_withdraw_count' => (int) $withdrawStats->pending_count,          // ← add
            ],
            'earningsChart' => $earningsChart,
            'statusBreakdown' => $statusBreakdown,
            'byModule' => $byModule,
            'recentCommissions' => $recentCommissions,
            'withdrawRequests' => $withdrawRequests,
            'hasPendingWithdraw' => $pendingWithdraw,
        ]);
    }
}
