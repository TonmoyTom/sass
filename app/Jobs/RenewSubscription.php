<?php

namespace App\Jobs;

use App\Models\Commission;
use App\Models\Sale;
use App\Models\Seller;
use App\Models\TenantModule;
use App\Models\WalletTransaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class RenewSubscription implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public int $tenantModuleId
    ) {}

    public function handle(): void
    {
        $tm = TenantModule::with(['tenant', 'module', 'tier'])
            ->find($this->tenantModuleId);

        // race guard — majhe onno kichu hoye gele skip
        if (! $tm || $tm->status !== 'active' || $tm->access_type !== 'subscription') {
            return;
        }

        if ($tm->expires_at === null || $tm->expires_at->isFuture()) {
            return; // already renewed / not due
        }

        DB::transaction(function () use ($tm) {
            $tenant = $tm->tenant;

            // renewal price — tier er current price, cycle onujayi
            $price = $tm->billing_cycle === 'yearly'
                ? (float) ($tm->tier?->yearly_price ?? $tm->price_paid)
                : (float) ($tm->tier?->monthly_price ?? $tm->price_paid);

            $newExpiry = $tm->billing_cycle === 'yearly'
                ? $tm->expires_at->copy()->addYear()
                : $tm->expires_at->copy()->addMonth();

            $seller = $tenant->referred_by
                ? Seller::where('id', $tenant->referred_by)->where('status', 'active')->first()
                : null;

            $commissionAmount = 0;
            if ($seller) {
                $commissionAmount = round($price * ($seller->commission_rate / 100), 2);
            }

            // 1. renewal sale
            $sale = Sale::create([
                'tenant_id' => $tenant->id,
                'seller_id' => $seller?->id,
                'module_id' => $tm->module_id,
                'module_tier_id' => $tm->module_tier_id,
                'sale_type' => 'renewal',
                'amount' => $price,
                'commission_amount' => $commissionAmount,
                'admin_amount' => $price - $commissionAmount,
                'status' => 'completed',
                'sold_at' => now(),
            ]);

            // 2. recurring commission
            if ($seller && $commissionAmount > 0) {
                Commission::create([
                    'seller_id' => $seller->id,
                    'sale_id' => $sale->id,
                    'amount' => $commissionAmount,
                    'rate' => $seller->commission_rate,
                    'commission_type' => 'recurring',
                    'status' => 'pending',
                    'hold_until' => now()->addDays(30),
                ]);

                // wallet credit
                $wallet = Seller::find($seller->id)?->wallet;

                if ($wallet) {
                    $wallet = $wallet->newQuery()->whereKey($wallet->id)->lockForUpdate()->first();
                    $before = (float) $wallet->pending_balance;

                    $wallet->increment('pending_balance', $commissionAmount);
                    $wallet->increment('total_earned', $commissionAmount);

                    WalletTransaction::create([
                        'wallet_id' => $wallet->id,
                        'type' => 'credit',
                        'amount' => $commissionAmount,
                        'balance_before' => $before,
                        'balance_after' => $before + $commissionAmount,
                        'reference_type' => 'commission',
                        'reference_id' => $sale->id,
                        'description' => 'Recurring commission — '.$tenant->name.' renewal ('.$tm->module?->name.')',
                    ]);
                }

                $seller->increment('total_earned', $commissionAmount);
            }

            // 3. subscription extend
            $tm->update([
                'expires_at' => $newExpiry,
                'price_paid' => $price,
            ]);
        });
    }
}
