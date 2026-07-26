<?php

namespace App\Http\Controllers\Admin;

use App\Events\NotificationSent;
use App\Http\Controllers\Controller;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Models\WithdrawRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class WithdrawRequestController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:withdraw.view')->only(['index', 'show']);
        $this->middleware('can:withdraw.approve')->only(['approve']);
        $this->middleware('can:withdraw.reject')->only(['reject']);
    }

    public function index(Request $request): Response
    {
        $requests = WithdrawRequest::query()
            ->with(['seller.user'])
            ->filterAndCache(
                $request,
                searchable: ['method'],
                filterable: ['status', 'method'],
                sortable: ['amount', 'status', 'created_at'],
                ttlSeconds: 120,
                perPage: 20,
                transform: fn ($w) => [
                    'id' => $w->id,
                    'amount' => $w->amount,
                    'paid_amount' => $w->paid_amount ?? 0,
                    'method' => $w->method,
                    'status' => $w->status,
                    'note' => $w->note,
                    'seller_name' => $w->seller?->user?->name ?? '—',
                    'seller_email' => $w->seller?->user?->email ?? '—',
                    'bkash_number' => $w->seller?->bkash_number,
                    'bank_name' => $w->seller?->bank_name,
                    'bank_account' => $w->seller?->bank_account,
                    'created_at' => $w->created_at?->format('d M Y, h:i A'),
                    'processed_at' => $w->processed_at?->format('d M Y, h:i A'),
                ]
            );
        $stats = Cache::store('redis')
            ->tags(['table:withdraw_requests'])
            ->remember('withdraw_stats', 120, function () {
                $s = WithdrawRequest::selectRaw("
                    SUM(CASE WHEN status = 'pending'  THEN amount ELSE 0 END)                     as pending_amount,
                    SUM(CASE WHEN status = 'approved' THEN paid_amount ELSE 0 END)                as approved_amount,
                    SUM(CASE WHEN status = 'approved' THEN (amount - paid_amount) ELSE 0 END)     as refunded_amount,
                    COUNT(CASE WHEN status = 'pending' THEN 1 END)                                as pending_count
                ")->first();

                return [
                    'pending_amount' => (float) $s->pending_amount,
                    'approved_amount' => (float) $s->approved_amount,
                    'refunded_amount' => (float) $s->refunded_amount,
                    'pending_count' => (int) $s->pending_count,
                ];
            });

        return Inertia::render('Admin/WithdrawRequests/Index', [
            'requests' => $requests,
            'filters' => [
                'search' => $request->input('search', ''),
                'status' => $request->input('status', ''),
                'method' => $request->input('method', ''),
                'sort_by' => $request->input('sort_by', 'created_at'),
                'sort_dir' => $request->input('sort_dir', 'desc'),
            ],
            'stats' => $stats,
        ]);
    }

    public function show(WithdrawRequest $withdraw): Response
    {
        $withdraw->load('seller.user');

        return Inertia::render('Admin/WithdrawRequests/Show', [
            'request' => [
                'id' => $withdraw->id,
                'amount' => $withdraw->amount,
                'paid_amount' => $withdraw->paid_amount ?? 0,  // ← ?? 0 sure koro
                'method' => $withdraw->method,
                'status' => $withdraw->status,
                'note' => $withdraw->note,
                'seller_name' => $withdraw->seller?->user?->name ?? '—',
                'seller_email' => $withdraw->seller?->user?->email ?? '—',
                'bkash_number' => $withdraw->account_number,
                'bank_name' => $withdraw->seller?->bank_name,
                'bank_account' => $withdraw->seller?->bank_account,
                'created_at' => $withdraw->created_at?->format('d M Y, h:i A'),
                'processed_at' => $withdraw->processed_at?->format('d M Y, h:i A'),
            ],
        ]);
    }

    public function approve(WithdrawRequest $withdraw, Request $request): RedirectResponse
    {
        abort_unless($withdraw->status === 'pending', 422, 'Already processed.');

        $request->validate([
            'paid_amount' => ['required', 'numeric', 'min:1', 'max:'.$withdraw->amount],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $paidAmount = (float) $request->paid_amount;
        $remaining = $withdraw->amount - $paidAmount;

        DB::transaction(function () use ($withdraw, $request, $paidAmount, $remaining) {
            $wallet = Wallet::whereKey($withdraw->wallet_id)->lockForUpdate()->first();

            $pendingBefore = $wallet->pending_balance;

            $wallet->decrement('pending_balance', $withdraw->amount);
            $wallet->increment('total_withdrawn', $paidAmount);

            if ($remaining > 0) {
                $wallet->increment('available_balance', $remaining);
            }

            WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'type' => 'approved',
                'amount' => -$paidAmount,
                'balance_before' => $pendingBefore,
                'balance_after' => $pendingBefore - $withdraw->amount,
                'description' => 'Withdraw approved ৳'.$paidAmount.' via '.$withdraw->method.($request->note ? ' — '.$request->note : ''),
                'reference_type' => 'withdraw_request',
                'reference_id' => $withdraw->id,
            ]);

            $withdraw->update([
                'status' => 'approved',
                'paid_amount' => $paidAmount,
                'note' => $request->note ?? $withdraw->note,
                'processed_at' => now(),
                'approved_by' => auth()->id(),
            ]);
        });

        // seller ke notify
        $sellerUserId = $withdraw->seller?->user_id;

        if ($sellerUserId) {
            $formattedPaid = number_format($paidAmount, 2);

            $message = $remaining > 0
                ? "Your withdraw request has been approved. ৳{$formattedPaid} paid via {$withdraw->method}, remaining ৳".number_format($remaining, 2).' returned to your available balance.'
                : "Your withdraw request of ৳{$formattedPaid} has been approved and paid via {$withdraw->method}.";

            NotificationSent::dispatch(
                $message,
                $sellerUserId,
                'success',
                '/seller/wallet'
            );
        }

        return back()->with('success', 'Payment of ৳'.number_format($paidAmount, 2).' approved successfully.');
    }

    public function reject(WithdrawRequest $withdraw, Request $request): RedirectResponse
    {
        abort_unless($withdraw->status === 'pending', 422, 'Already processed.');

        $request->validate([
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($withdraw, $request) {
            $wallet = Wallet::whereKey($withdraw->wallet_id)->lockForUpdate()->first();

            $availableBefore = $wallet->available_balance;

            $wallet->decrement('pending_balance', $withdraw->amount);
            $wallet->increment('available_balance', $withdraw->amount);

            WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'type' => 'credit',
                'amount' => $withdraw->amount,
                'balance_before' => $availableBefore,
                'balance_after' => $availableBefore + $withdraw->amount,
                'description' => 'Withdraw rejected — refunded. '.($request->reason ?? ''),
                'reference_type' => 'withdraw_request',
                'reference_id' => $withdraw->id,
            ]);

            $withdraw->update([
                'status' => 'rejected',
                'note' => $request->reason ?? $withdraw->note,
                'processed_at' => now(),
                'approved_by' => auth()->id(),
            ]);
        });

        // seller ke notify
        $sellerUserId = $withdraw->seller?->user_id;

        if ($sellerUserId) {
            $formattedAmount = number_format($withdraw->amount, 2);
            $reason = $request->reason ? " Reason: {$request->reason}" : '';

            NotificationSent::dispatch(
                "Your withdraw request of ৳{$formattedAmount} has been rejected. The amount has been returned to your available balance.{$reason}",
                $sellerUserId,
                'error',
                '/seller/wallet'
            );
        }

        return back()->with('success', 'Withdraw request rejected and amount refunded.');
    }
}
