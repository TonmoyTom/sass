<?php

namespace App\Http\Controllers\Admin;

use App\Events\NotificationSent;
use App\Http\Controllers\Controller;
use App\Models\Commission;
use App\Models\WalletTransaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class CommissionController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:commissions.view')->only(['index']);
        $this->middleware('can:commissions.approve')->only(['approve', 'approveDue']);
        $this->middleware('can:commissions.reject')->only(['reject']);
    }

    public function index(Request $request): Response
    {
        $commissions = Commission::query()
            ->with(['seller.user', 'sale.module', 'sale.tenant.owner'])
            ->filterAndCache(
                $request,
                searchable: ['seller.user.name', 'seller.user.email', 'sale.module.name'],
                filterable: ['status', 'commission_type'],
                sortable: ['amount', 'created_at', 'hold_until'],
                ttlSeconds: 180,
                perPage: 20,
                transform: fn ($c) => [
                    'id' => $c->id,
                    'amount' => $c->amount,
                    'rate' => $c->rate,
                    'type' => $c->commission_type,
                    'status' => $c->status,
                    'seller_name' => $c->seller?->user?->name ?? '—',
                    'seller_email' => $c->seller?->user?->email ?? '—',
                    'module_name' => $c->sale?->module?->name ?? '—',
                    'tenant_name' => $c->sale?->tenant?->name ?? '—',
                    'tenant_email' => $c->sale?->tenant?->owner?->email ?? '—',
                    'sale_amount' => $c->sale?->amount,
                    'hold_until' => $c->hold_until?->format('d M Y'),
                    'is_held' => $c->hold_until && $c->hold_until->isFuture(),
                    'created_at' => $c->created_at?->format('d M Y'),
                ]
            );

        // stats — cache alada (aggregate query, table-cache tag e cache holeo invalidation e thik e clear hobe)
        $stats = Cache::store('redis')
            ->tags(['table:'.(new Commission)->getTable()])
            ->remember('commission_stats', 180, fn () => [
                'counts' => [
                    'all' => Commission::count(),
                    'pending' => Commission::where('status', 'pending')->count(),
                    'approved' => Commission::where('status', 'approved')->count(),
                    'paid' => Commission::where('status', 'paid')->count(),
                ],
                'totals' => [
                    'pending' => (float) Commission::where('status', 'pending')->sum('amount'),
                    'approved' => (float) Commission::where('status', 'approved')->sum('amount'),
                ],
            ]);

        return Inertia::render('Admin/Commissions/Index', [
            'commissions' => $commissions,
            'filters' => [
                'search' => $request->input('search', ''),
                'status' => $request->input('status', ''),
                'commission_type' => $request->input('commission_type', ''),
                'sort_by' => $request->input('sort_by', 'created_at'),
                'sort_dir' => $request->input('sort_dir', 'desc'),
            ],
            'counts' => $stats['counts'],
            'totals' => $stats['totals'],
        ]);
    }

    public function approve(Commission $commission): RedirectResponse
    {
        if ($commission->status !== 'pending') {
            return back()->with('error', 'Ei commission already processed.');
        }

        DB::transaction(function () use ($commission) {
            $commission->update([
                'status' => 'approved',
                'approved_at' => now(),
            ]);

            $wallet = $commission->seller?->wallet;
            if ($wallet) {
                $before = $wallet->available_balance;

                $wallet->decrement('pending_balance', $commission->amount);
                $wallet->increment('available_balance', $commission->amount);

                WalletTransaction::create([
                    'wallet_id' => $wallet->id,
                    'type' => 'release',
                    'amount' => $commission->amount,
                    'balance_before' => $before,
                    'balance_after' => $before + $commission->amount,
                    'reference_type' => 'commission',
                    'reference_id' => $commission->id,
                    'description' => 'Commission approved & released',
                ]);
            }
        });

        // seller ke notify
        $sellerUserId = $commission->seller?->user_id;
        if ($sellerUserId) {
            NotificationSent::dispatch(
                'Your commission of ৳'.number_format($commission->amount, 2).' has been approved and is now available in your wallet.',
                $sellerUserId,
                'success',
                '/seller/wallet'
            );
        }

        return back()->with('status', 'Commission approved. Seller wallet-e available holo.');
    }

    public function reject(Request $request, Commission $commission): RedirectResponse
    {
        if ($commission->status !== 'pending') {
            return back()->with('error', 'Ei commission already processed.');
        }

        $data = $request->validate([
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($commission, $data) {
            $commission->update([
                'status' => 'cancelled',
                'notes' => $data['note'] ?? 'Cancelled by admin',
            ]);

            $wallet = $commission->seller?->wallet;
            if ($wallet) {
                $before = $wallet->pending_balance;

                $wallet->decrement('pending_balance', $commission->amount);
                $wallet->decrement('total_earned', $commission->amount);

                WalletTransaction::create([
                    'wallet_id' => $wallet->id,
                    'type' => 'debit',
                    'amount' => $commission->amount,
                    'balance_before' => $before,
                    'balance_after' => $before - $commission->amount,
                    'reference_type' => 'commission',
                    'reference_id' => $commission->id,
                    'description' => 'Commission cancelled',
                ]);
            }
        });

        // seller ke notify
        $sellerUserId = $commission->seller?->user_id;
        if ($sellerUserId) {
            $reason = $data['note'] ? " Reason: {$data['note']}" : '';

            NotificationSent::dispatch(
                'Your commission of ৳'.number_format($commission->amount, 2)." has been cancelled.{$reason}",
                $sellerUserId,
                'error',
                '/seller/wallet'
            );
        }

        return back()->with('status', 'Commission cancelled.');
    }

    /**
     * Bulk approve — hold period sesh hoyeche emn sob pending approve.
     */
    public function approveDue(): RedirectResponse
    {
        $due = Commission::with('seller')
            ->where('status', 'pending')
            ->where(function ($q) {
                $q->whereNull('hold_until')->orWhere('hold_until', '<=', now());
            })
            ->get();

        $count = 0;
        $notifications = []; // batch e pathabo transaction er bahire

        DB::transaction(function () use ($due, &$count, &$notifications) {
            foreach ($due as $commission) {
                $commission->update(['status' => 'approved', 'approved_at' => now()]);

                $wallet = $commission->seller?->wallet;
                if ($wallet) {
                    $before = $wallet->available_balance;
                    $wallet->decrement('pending_balance', $commission->amount);
                    $wallet->increment('available_balance', $commission->amount);

                    WalletTransaction::create([
                        'wallet_id' => $wallet->id,
                        'type' => 'release',
                        'amount' => $commission->amount,
                        'balance_before' => $before,
                        'balance_after' => $before + $commission->amount,
                        'reference_type' => 'commission',
                        'reference_id' => $commission->id,
                        'description' => 'Commission auto-approved (hold period over)',
                    ]);
                }

                if ($commission->seller?->user_id) {
                    $notifications[] = [
                        'user_id' => $commission->seller->user_id,
                        'amount' => $commission->amount,
                    ];
                }

                $count++;
            }
        });

        // sob notify — transaction er bahire
        foreach ($notifications as $n) {
            NotificationSent::dispatch(
                'Your commission of ৳'.number_format($n['amount'], 2).' has been approved and is now available in your wallet.',
                $n['user_id'],
                'success',
                '/seller/wallet'
            );
        }

        return back()->with('status', "{$count} commission approved.");
    }
}
