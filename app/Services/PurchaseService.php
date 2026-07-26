<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Commission;
use App\Models\Referral;
use App\Models\Sale;
use App\Models\Seller;
use App\Models\Tenant;
use App\Models\TenantModule;
use App\Models\WalletTransaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PurchaseService
{
    public function __construct(
        protected ProrationService $proration
    ) {}

    /**
     * Cart checkout — sob purchase logic ek transaction-e.
     *
     * @param  ?string  $referralCode  seller code (commission-er jonno)
     * @return array created sales + commission info
     */
    public function checkout(
        Tenant $tenant,
        Cart $cart,
        ?string $referralCode = null,
        ?string $paymentMethod = null,
        ?string $transactionId = null,
    ): array {
        $cart->load(['items.module', 'items.tier']);

        if ($cart->items->isEmpty()) {
            throw new \RuntimeException('Cart is empty');
        }

        // referral resolve: notun code thakle sheta, na thakle tenant-er existing referrer
        // (first-touch attribution — recurring commission original seller-i pabe)
        $seller = null;

        if ($tenant->referred_by) {
            $seller = Seller::where('id', $tenant->referred_by)
                ->where('status', 'active')
                ->first();
        } elseif ($referralCode) {
            $seller = Seller::where('referral_code', $referralCode)
                ->where('status', 'active')
                ->first();
        }

        return DB::transaction(function () use ($paymentMethod, $tenant, $cart, $seller, $transactionId) {
            $sales = [];
            $totalCommission = 0;
            $period = now()->format('Y-m');

            foreach ($cart->items as $item) {
                $fullPrice = (float) $item->price;
                $isOneTime = $item->billing_cycle === 'one_time';

                // already owned (upgrade/change) hole proration
                $existing = TenantModule::where('tenant_id', $tenant->id)
                    ->where('module_id', $item->module_id)
                    ->first();

                $amount = $fullPrice;
                $expiresAt = $this->cycleExpiry($item->billing_cycle);

                if ($existing && $existing->status === 'active') {
                    $p = $this->proration->calculate($existing, $fullPrice, $item->billing_cycle);
                    $amount = $p['charge'];             // baki value baad — kom charge
                    $expiresAt = $p['new_expires_at'];  // ekhon theke notun cycle
                }

                // commission — SELLER-er rate, prorated amount-er upor
                $commissionAmount = $seller
                    ? round($amount * ($seller->commission_rate / 100), 2)
                    : 0;
                $adminAmount = $amount - $commissionAmount;

                // 1. sale create
                $sale = Sale::create([
                    'tenant_id' => $tenant->id,
                    'seller_id' => $seller?->id,
                    'module_id' => $item->module_id,
                    'module_tier_id' => $item->module_tier_id,
                    'sale_type' => $isOneTime ? 'addon' : 'module',
                    'amount' => $amount,
                    'commission_amount' => $commissionAmount,
                    'admin_amount' => $adminAmount,
                    'status' => 'completed',
                    'sold_at' => now(),
                    'payment_method' => $paymentMethod,      
                    'transaction_id' => $transactionId,
                ]);

                // 2. commission create (seller thakle)
                if ($seller && $commissionAmount > 0) {
                    Commission::create([
                        'seller_id' => $seller->id,
                        'sale_id' => $sale->id,
                        'amount' => $commissionAmount,
                        'rate' => $seller->commission_rate,
                        'commission_type' => $isOneTime ? 'one_time' : 'recurring',
                        'period' => $isOneTime ? null : $period,   // recurring cron duplicate guard
                        'status' => 'pending',
                        'hold_until' => now()->addDays(30),
                    ]);

                    $totalCommission += $commissionAmount;
                }

                // 3. tenant_modules — enable / update (upgrade hole update)
                TenantModule::updateOrCreate(
                    ['tenant_id' => $tenant->id, 'module_id' => $item->module_id],
                    [
                        'module_tier_id' => $item->module_tier_id,
                        'status' => 'active',
                        'access_type' => $isOneTime ? 'lifetime' : 'subscription',
                        'limits' => $item->tier?->limits ?? [],
                        'activated_at' => now(),
                        'purchased_at' => now(),
                        'expires_at' => $expiresAt,
                        'price_paid' => $amount,        // actual paid (prorated)
                        'billing_cycle' => $item->billing_cycle,
                    ]
                );

                $sales[] = $sale;
            }

            // 4. seller wallet + stats update (commission thakle)
            if ($seller && $totalCommission > 0) {
                $wallet = $seller->wallet()->lockForUpdate()->first();

                if ($wallet) {
                    $before = (float) $wallet->pending_balance;

                    $wallet->increment('pending_balance', $totalCommission);
                    $wallet->increment('total_earned', $totalCommission);

                    WalletTransaction::create([
                        'wallet_id' => $wallet->id,
                        'type' => 'credit',
                        'amount' => $totalCommission,
                        'balance_before' => $before,
                        'balance_after' => $before + $totalCommission,
                        'reference_type' => 'commission',
                        'reference_id' => $sales[0]->id ?? null,
                        'description' => 'Commission from '.$tenant->name.' purchase',
                    ]);
                }

                $seller->increment('total_sales', count($sales));
                $seller->increment('total_earned', $totalCommission);

                // referral conversion mark — SHUDHU jodi age set na thake (first-touch)
                if (! $tenant->referred_by) {
                    $tenant->update(['referred_by' => $seller->id]);
                }

                Referral::updateOrCreate(
                    ['seller_id' => $seller->id, 'tenant_id' => $tenant->id],
                    [
                        'referral_code' => $seller->referral_code,
                        'converted_at' => now(),
                    ]
                );
            }

            // 5. cart clear
            $cart->items()->delete();

            return [
                'sales' => $sales,
                'seller' => $seller,
                'total_commission' => $totalCommission,
                'invoice_numbers' => collect($sales)->pluck('invoice_number')->all(),
            ];
        });
    }

    /**
     * Billing cycle theke expiry date.
     */
    protected function cycleExpiry(string $cycle): ?Carbon
    {
        return match ($cycle) {
            'yearly' => now()->addYear(),
            'monthly' => now()->addMonth(),
            default => null,
        };
    }
}
