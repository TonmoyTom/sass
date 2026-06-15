<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Commission;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CommissionController extends Controller
{
    public function index(Request $request): Response
    {
        $seller = auth()->user()->sellerProfile;

        abort_unless($seller, 403, 'Seller profile not found');

        // summary stats
        $totalEarned = Commission::where('seller_id', $seller->id)
            ->whereIn('status', ['approved', 'paid'])
            ->sum('amount');

        $pending = Commission::where('seller_id', $seller->id)
            ->where('status', 'pending')
            ->sum('amount');

        $available = Commission::where('seller_id', $seller->id)
            ->where('status', 'approved')
            ->sum('amount');

        $paid = Commission::where('seller_id', $seller->id)
            ->where('status', 'paid')
            ->sum('amount');

        // commission list (sale + module info shoho)
        $commissions = Commission::where('seller_id', $seller->id)
            ->with(['sale.module', 'sale.tenant'])
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->latest()
            ->paginate(15)
            ->through(fn ($c) => [
                'id' => $c->id,
                'amount' => $c->amount,
                'rate' => $c->rate,
                'type' => $c->commission_type,
                'status' => $c->status,
                'module_name' => $c->sale?->module?->name ?? '—',
                'tenant_name' => $c->sale?->tenant?->name ?? '—',
                'sale_amount' => $c->sale?->amount,
                'hold_until' => $c->hold_until?->format('d M Y'),
                'created_at' => $c->created_at?->format('d M Y'),
            ]);

        return Inertia::render('Seller/Commissions/Index', [
            'commissions' => $commissions,
            'summary' => [
                'total_earned' => $totalEarned,
                'pending' => $pending,
                'available' => $available,
                'paid' => $paid,
            ],
            'filters' => ['status' => $request->status],
        ]);
    }
}
