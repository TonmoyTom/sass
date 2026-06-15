<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\WalletTransaction;
use Inertia\Inertia;
use Inertia\Response;

class WalletController extends Controller
{
    public function index(): Response
    {
        $seller = auth()->user()->sellerProfile;

        abort_unless($seller, 403, 'Seller profile not found');

        $wallet = $seller->wallet;

        // transaction history
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
}
