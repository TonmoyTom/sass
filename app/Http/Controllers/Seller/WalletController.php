<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\WalletTransaction;
use App\Models\WithdrawRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class WalletController extends Controller
{
    public function index(): Response
    {
        $seller = auth()->user()->sellerProfile;
        abort_unless($seller, 403, 'Seller profile not found');

        $wallet = $seller->wallet;

        $transactions = WalletTransaction::where('wallet_id', $wallet?->id)
            ->latest()
            ->paginate(20)
            ->through(fn ($t) => [
                'id' => $t->id,
                'type' => $t->type,
                'amount' => $t->amount,
                'balance_after' => $t->balance_after,
                'description' => $t->description,
                'reference_type' => $t->reference_type,
                'created_at' => $t->created_at?->format('d M Y, h:i A'),
            ]);

        return Inertia::render('Seller/Wallet/Index', [
            'wallet' => [
                'available_balance' => $wallet?->available_balance ?? 0,
                'pending_balance' => $wallet?->pending_balance ?? 0,
                'total_earned' => $wallet?->total_earned ?? 0,
                'total_withdrawn' => $wallet?->total_withdrawn ?? 0,
                'currency' => $wallet?->currency ?? 'BDT',
            ],
            'transactions' => $transactions,
            'payout' => [
                'bkash_number' => $seller->bkash_number,
                'bank_name' => $seller->bank_name,
                'bank_account' => $seller->bank_account,
            ],
        ]);
    }

    public function withdraw(Request $request): RedirectResponse
    {
        $seller = auth()->user()->sellerProfile;
        abort_unless($seller, 403, 'Seller profile not found');

        $wallet = $seller->wallet;
        abort_unless($wallet, 403, 'Wallet not found');

        $request->validate([
            'amount' => ['required', 'numeric', 'min:10'],
            'method' => ['required', 'in:bkash,bank'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $amount = (float) $request->amount;

        if ($wallet->available_balance < $amount) {
            return back()->withErrors(['amount' => 'Insufficient balance.']);
        }

        // pending withdraw request already ache?
        $hasPending = WithdrawRequest::where('seller_id', $seller->id)
            ->where('status', 'pending')
            ->exists();

        if ($hasPending) {
            return back()->withErrors(['amount' => 'You already have a pending withdraw request.']);
        }

        \DB::transaction(function () use ($wallet, $seller, $request, $amount) {
            // balance hold (available → pending)
            $wallet->decrement('available_balance', $amount);
            $wallet->increment('pending_balance', $amount);

            // withdraw request create
            WithdrawRequest::create([
                'seller_id' => $seller->id,
                'wallet_id' => $wallet->id,
                'amount' => $amount,
                'method' => $request->method,
                'note' => $request->note,
                'status' => 'pending',
            ]);

            // transaction log
            WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'type' => 'withdraw_request',
                'amount' => -$amount,
                'balance_before' => $wallet->available_balance, // ← add
                'balance_after' => $wallet->available_balance - $amount,                'description' => 'Withdraw request via '.$request->method,
                'reference_type' => 'withdraw_request',
            ]);
        });

        return to_route('seller.wallet.index')->with('success', 'Withdraw request submitted successfully.');
    }

    public function withdrawPage(): Response
    {
        $seller = auth()->user()->sellerProfile;
        abort_unless($seller, 403);

        $wallet = $seller->wallet;

        $pendingRequest = WithdrawRequest::where('seller_id', $seller->id)
            ->where('status', 'pending')
            ->latest()
            ->first();

        return Inertia::render('Seller/Wallet/Withdraw', [
            'wallet' => [
                'available_balance' => $wallet?->available_balance ?? 0,
                'currency' => $wallet?->currency ?? 'BDT',
            ],
            'payout' => [
                'bkash_number' => $seller->bkash_number,
                'bank_name' => $seller->bank_name,
                'bank_account' => $seller->bank_account,
            ],
            'pending_request' => $pendingRequest ? [
                'amount' => $pendingRequest->amount,
                'method' => $pendingRequest->method,
                'created_at' => $pendingRequest->created_at->format('d M Y, h:i A'),
            ] : null,
        ]);
    }

    public function updatePayout(Request $request): RedirectResponse
    {
        $seller = auth()->user()->sellerProfile;
        abort_unless($seller, 403);
        $request->validate([
            'bkash_number' => ['nullable', 'string', 'max:20'],
            'bank_name' => ['nullable', 'string', 'max:100'],
            'bank_account' => ['nullable', 'string', 'max:50'],
        ]);

        $seller->update($request->only('bkash_number', 'bank_name', 'bank_account'));

        return back()->with('success', 'Payout details updated.');
    }

    public function withdrawHistory(): Response
    {
        $seller = auth()->user()->sellerProfile;
        abort_unless($seller, 403);

        $requests = WithdrawRequest::where('seller_id', $seller->id)
            ->latest()
            ->paginate(20)
            ->through(fn ($w) => [
                'id' => $w->id,
                'amount' => $w->amount,
                'paid_amount' => $w->paid_amount,
                'method' => $w->method,
                'status' => $w->status,
                'note' => $w->note,
                'created_at' => $w->created_at?->format('d M Y, h:i A'),
                'processed_at' => $w->processed_at?->format('d M Y, h:i A'),
            ]);

        return Inertia::render('Seller/Wallet/WithdrawHistory', [
            'requests' => $requests,
        ]);
    }

    public function withdrawShow(WithdrawRequest $withdraw): Response
    {
        $seller = auth()->user()->sellerProfile;
        abort_unless($seller && $withdraw->seller_id === $seller->id, 403);

        return Inertia::render('Seller/Wallet/WithdrawShow', [
            'request' => [
                'id' => $withdraw->id,
                'amount' => $withdraw->amount,
                'paid_amount' => $withdraw->paid_amount,
                'method' => $withdraw->method,
                'status' => $withdraw->status,
                'note' => $withdraw->note,
                'created_at' => $withdraw->created_at?->format('d M Y, h:i A'),
                'processed_at' => $withdraw->processed_at?->format('d M Y, h:i A'),
            ],
            'payout' => [
                'bkash_number' => $seller->bkash_number,
                'bank_name' => $seller->bank_name,
                'bank_account' => $seller->bank_account,
            ],
        ]);
    }
}
