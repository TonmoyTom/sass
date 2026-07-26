<?php

namespace App\Http\Controllers\Seller;

use App\Enums\UserType;
use App\Events\NotificationSent;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Models\WithdrawRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
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
        $user = auth()->user();
        $seller = $user->sellerProfile;
        abort_unless($seller, 403, 'Seller profile not found');

        $wallet = $seller->wallet;
        abort_unless($wallet, 403, 'Wallet not found');

        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:20'],
            'method' => ['required', 'in:bkash,nagad,rocket'],
            'account_name' => ['required', 'string', 'max:255'],
            'account_number' => ['required', 'regex:/^01[3-9]\d{8}$/'],
            'note' => ['nullable', 'string', 'max:255'],
        ], [
            'account_number.regex' => 'Valid BD mobile number din (01XXXXXXXXX).',
        ]);

        $amount = (float) $data['amount'];

        DB::transaction(function () use ($wallet, $seller, $data, $amount) {
            $wallet = Wallet::whereKey($wallet->id)->lockForUpdate()->first();

            if ($wallet->available_balance < $amount) {
                throw ValidationException::withMessages(['amount' => 'Insufficient balance.']);
            }

            $hasPending = WithdrawRequest::where('seller_id', $seller->id)
                ->where('status', 'pending')
                ->lockForUpdate()
                ->exists();

            if ($hasPending) {
                throw ValidationException::withMessages(['amount' => 'You already have a pending withdraw request.']);
            }

            $balanceBefore = $wallet->available_balance;

            $wallet->decrement('available_balance', $amount);
            $wallet->increment('pending_balance', $amount);

            $withdrawRequest = WithdrawRequest::create([
                'seller_id' => $seller->id,
                'wallet_id' => $wallet->id,
                'amount' => $amount,
                'method' => $data['method'],
                'account_name' => $data['account_name'],
                'account_number' => $data['account_number'],
                'note' => $data['note'] ?? null,
                'status' => 'pending',
            ]);

            WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'type' => 'withdraw_request',
                'amount' => -$amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceBefore - $amount,
                'description' => 'Withdraw request via '.$data['method'],
                'reference_type' => 'withdraw_request',
                'reference_id' => $withdrawRequest->id,
            ]);
        });

        $formattedAmount = number_format($amount, 2);

        // admin(s) ke notify
        User::where('user_type', UserType::SUPER_ADMIN)
            ->pluck('id')
            ->each(fn ($adminId) => NotificationSent::dispatch(
                "New withdraw request: {$user->name} has requested ৳{$formattedAmount} via {$data['method']}.",
                $adminId,
                'info',
                '/admin/withdraw-requests'
            ));

        // seller confirmation
        NotificationSent::dispatch(
            "Your withdraw request of ৳{$formattedAmount} via {$data['method']} has been submitted. You'll be notified once it's processed.",
            $user->id,
            'success',
            '/seller/wallet'
        );

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

        // latest request (je kono status) — prefill er jonno
        $lastRequest = WithdrawRequest::where('seller_id', $seller->id)
            ->latest()
            ->first();

        return Inertia::render('Seller/Wallet/Withdraw', [
            'wallet' => [
                'available_balance' => $wallet->available_balance,
            ],
            'pending_request' => $pendingRequest ? [
                'amount' => $pendingRequest->amount,
                'method' => $pendingRequest->method,
                'created_at' => $pendingRequest->created_at->diffForHumans(),
            ] : null,
            'last_request' => $lastRequest ? [
                'method' => $lastRequest->method,
                'account_name' => $lastRequest->account_name,
                'account_number' => $lastRequest->account_number,
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
                'approved_by' => $withdraw->approvedBy?->name,
                'account_number' => $withdraw->account_number,
                'account_name' => $withdraw->account_name,
            ],
            'payout' => [
                'bank_account' => $seller->bank_account,
            ],
        ]);
    }
}
