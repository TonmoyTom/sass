<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Models\WithdrawRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
        $requests = WithdrawRequest::with(['seller.user'])
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(20)
            ->through(fn ($w) => [
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
            ]);

        $stats = WithdrawRequest::selectRaw("
                SUM(CASE WHEN status = 'pending'  THEN amount ELSE 0 END)                           as pending_amount,
                SUM(CASE WHEN status = 'approved' THEN paid_amount ELSE 0 END)                      as approved_amount,
                SUM(CASE WHEN status = 'approved' THEN (amount - paid_amount) ELSE 0 END)           as refunded_amount,
                COUNT(CASE WHEN status = 'pending' THEN 1 END)                                      as pending_count
            ")->first();

        return Inertia::render('Admin/WithdrawRequests/Index', [
            'requests' => $requests,
            'filters' => $request->only('status'),
            'stats' => [
                'pending_amount' => (float) $stats->pending_amount,
                'approved_amount' => (float) $stats->approved_amount,
                'refunded_amount' => (float) $stats->refunded_amount,
                'pending_count' => (int) $stats->pending_count,
            ],
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
                'bkash_number' => $withdraw->seller?->bkash_number,
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
            $wallet = Wallet::find($withdraw->wallet_id);

            // pending balance theke paid amount ber koro
            $wallet->decrement('pending_balance', $withdraw->amount);
            $wallet->increment('total_withdrawn', $paidAmount);

            // remaining refund → available balance
            if ($remaining > 0) {
                $wallet->increment('available_balance', $remaining);
            }

            WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'type' => 'approved',
                'amount' => -$paidAmount,
                'balance_before' => $wallet->pending_balance,
                'balance_after' => $wallet->pending_balance - $withdraw->amount,
                'description' => 'Withdraw approved ৳'.$paidAmount.' via '.$withdraw->method.($request->note ? ' — '.$request->note : ''),
                'reference_type' => 'withdraw_request',
            ]);

            $withdraw->update([
                'status' => 'approved',
                'paid_amount' => $paidAmount,
                'note' => $request->note ?? $withdraw->note,
                'processed_at' => now(),
            ]);
        });

        return back()->with('success', 'Payment of ৳'.number_format($paidAmount, 2).' approved successfully.');
    }

    public function reject(WithdrawRequest $withdraw, Request $request): RedirectResponse
    {
        abort_unless($withdraw->status === 'pending', 422, 'Already processed.');

        $request->validate([
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($withdraw, $request) {
            $wallet = Wallet::find($withdraw->wallet_id);

            // refund: pending → available
            $wallet->decrement('pending_balance', $withdraw->amount);
            $wallet->increment('available_balance', $withdraw->amount);

            WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'type' => 'credit',
                'amount' => $withdraw->amount,
                'balance_before' => $wallet->available_balance,
                'balance_after' => $wallet->available_balance + $withdraw->amount,
                'description' => 'Withdraw rejected — refunded. '.($request->reason ?? ''),
                'reference_type' => 'withdraw_request',
            ]);

            $withdraw->update([
                'status' => 'rejected',
                'note' => $request->reason ?? $withdraw->note,
                'processed_at' => now(),
            ]);
        });

        return back()->with('success', 'Withdraw request rejected and amount refunded.');
    }
}
