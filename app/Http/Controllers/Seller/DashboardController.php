<?php

declare(strict_types=1);

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Commission;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Seller Dashboard Controller
 *
 * Dashboard for sellers/affiliates.
 * Shows: earnings, modules, commissions, wallet balance.
 */
class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $seller = auth()->user()->sellerProfile;

        abort_unless($seller, 403, 'Seller profile not found');

        $wallet = $seller->wallet;

        // approved module count (sell korte pare)
        $approvedModules = $seller->moduleRequests()->where('status', 'approved')->count();
        $pendingRequests = $seller->moduleRequests()->where('status', 'pending')->count();

        // recent commissions
        $recentCommissions = Commission::where('seller_id', $seller->id)
            ->with(['sale.module', 'sale.tenant'])
            ->latest()
            ->limit(5)
            ->get()
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
            ],
            'recent_commissions' => $recentCommissions,
        ]);
    }
}
