<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Commission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;

class CommissionController extends Controller
{
    public function index(Request $request): Response
    {
        $seller = auth()->user()->sellerProfile;

        abort_unless($seller, 403, 'Seller profile not found');

        // summary stats — cache kore rakhলাম, Commission tag e (commission create/update hole auto-invalidate)
        $summary = Cache::store('redis')
            ->tags(['table:commissions'])
            ->remember("seller_commission_summary:{$seller->id}", 180, function () use ($seller) {
                return [
                    'total_earned' => (float) Commission::where('seller_id', $seller->id)
                        ->whereIn('status', ['approved', 'paid'])
                        ->sum('amount'),
                    'pending' => (float) Commission::where('seller_id', $seller->id)
                        ->where('status', 'pending')
                        ->sum('amount'),
                    'available' => (float) Commission::where('seller_id', $seller->id)
                        ->where('status', 'approved')
                        ->sum('amount'),
                    'paid' => (float) Commission::where('seller_id', $seller->id)
                        ->where('status', 'paid')
                        ->sum('amount'),
                ];
            });

        // commission list — filterable + cached
        $commissions = Commission::query()
            ->where('seller_id', $seller->id)
            ->with(['sale.module', 'sale.tenant'])
            ->filterAndCache(
                $request,
                searchable: ['sale.module.name', 'sale.tenant.name'],
                filterable: ['status', 'commission_type'],
                sortable: ['amount', 'created_at', 'hold_until'],
                ttlSeconds: 180,
                perPage: 15,
                transform: fn ($c) => [
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
                ]
            );

        return Inertia::render('Seller/Commissions/Index', [
            'commissions' => $commissions,
            'summary' => $summary,
            'filters' => [
                'search' => $request->input('search', ''),
                'status' => $request->input('status', ''),
                'sort_by' => $request->input('sort_by', 'created_at'),
                'sort_dir' => $request->input('sort_dir', 'desc'),
            ],
        ]);
    }
}
