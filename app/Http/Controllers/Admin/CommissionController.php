<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Commission;
use App\Models\WalletTransaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class CommissionController extends Controller
{
    public function index(Request $request): Response
    {
        $commissions = Commission::query()
            ->with(['seller.user', 'sale.module', 'sale.tenant.owner'])
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->latest()
            ->paginate(20)
            ->through(fn ($c) => [
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
            ]);

        return Inertia::render('Admin/Commissions/Index', [
            'commissions' => $commissions,
            'filters' => ['status' => $request->status],
            'counts' => [
                'all' => Commission::count(),
                'pending' => Commission::where('status', 'pending')->count(),
                'approved' => Commission::where('status', 'approved')->count(),
                'paid' => Commission::where('status', 'paid')->count(),
            ],
            'totals' => [
                'pending' => Commission::where('status', 'pending')->sum('amount'),
                'approved' => Commission::where('status', 'approved')->sum('amount'),
            ],
        ]);
    }

    public function approve(Commission $commission): RedirectResponse
    {
        if ($commission->status !== 'pending') {
            return back()->with('error', 'Ei commission already processed.');
        }

        DB::transaction(function () use ($commission) {
            // commission approve
            $commission->update([
                'status' => 'approved',
                'approved_at' => now(),
            ]);

            // wallet — pending theke available-e move
            $wallet = $commission->seller?->wallet;
            if ($wallet) {
                $before = $wallet->available_balance;

                $wallet->decrement('pending_balance', $commission->amount);
                $wallet->increment('available_balance', $commission->amount);

                // transaction log
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

            // wallet pending theke baad
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

        return back()->with('status', 'Commission cancelled.');
    }

    /**
     * Bulk approve — hold period sesh hoyeche emn sob pending approve.
     */
    public function approveDue(): RedirectResponse
    {
        $due = Commission::where('status', 'pending')
            ->where(function ($q) {
                $q->whereNull('hold_until')->orWhere('hold_until', '<=', now());
            })
            ->get();

        $count = 0;
        DB::transaction(function () use ($due, &$count) {
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
                $count++;
            }
        });

        return back()->with('status', "{$count} commission approved.");
    }
}
