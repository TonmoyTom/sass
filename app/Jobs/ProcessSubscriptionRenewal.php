<?php

namespace App\Jobs;

use App\Enums\UserType;
use App\Events\NotificationSent;
use App\Models\Commission;
use App\Models\Sale;
use App\Models\Seller;
use App\Models\TenantModule;
use App\Models\TenantUser;
use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class ProcessSubscriptionRenewal implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 60;

    public function __construct(
        public int $tenantModuleId,
     public bool $skipCommission = false 
    ) {}

    public function handle(): void
    {
        $tm = TenantModule::with(['tenant', 'module', 'tier'])->find($this->tenantModuleId);

        if (! $tm || $tm->status !== 'active' || $tm->access_type !== 'subscription') {
            return;
        }
        if ($tm->expires_at === null || $tm->expires_at->isFuture()) {
            return; // already renewed
        }

        $tenant = $tm->tenant;
        if (! $tenant) {
            return;
        }

        $period = now()->format('Y-m');

        // seller — tenant e referred_by mark kora
        $seller = $tenant->referred_by
            ? Seller::with('user', 'wallet')
                ->where('id', $tenant->referred_by)
                ->where('status', 'active')
                ->first()
            : null;

        // renewal price — tier er current price, cycle onujayi (fallback price_paid)
        $price = $tm->billing_cycle === 'yearly'
            ? (float) ($tm->tier?->yearly_price ?? $tm->price_paid)
            : (float) ($tm->tier?->monthly_price ?? $tm->price_paid);

        $newExpiry = $tm->billing_cycle === 'yearly'
            ? $tm->expires_at->copy()->addYear()
            : $tm->expires_at->copy()->addMonth();

        $commissionAmount = $seller
            ? round($price * ($seller->commission_rate / 100), 2)
            : 0;

        $result = DB::transaction(function () use (
            $tm, $tenant, $seller, $price, $newExpiry, $commissionAmount, $period
        ) {
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

            // 2. recurring commission (seller thakle)
            if ($seller && $commissionAmount > 0) {
                $dupe = Commission::where('sale_id', $sale->id)
                    ->where('commission_type', 'recurring')
                    ->where('period', $period)
                    ->exists();

                if (! $dupe) {
                    Commission::create([
                        'seller_id' => $seller->id,
                        'sale_id' => $sale->id,
                        'amount' => $commissionAmount,
                        'rate' => $seller->commission_rate,
                        'commission_type' => 'recurring',
                        'period' => $period,
                        'status' => 'pending',
                        'hold_until' => now()->addDays(30),
                    ]);
                    $wallet = $seller->wallet;
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
                            'description' => 'Recurring commission — '.$tenant->name.' ('.$tm->module?->name.') '.$period,
                        ]);
                    }

                    $seller->increment('total_sales', 1);
                    $seller->increment('total_earned', $commissionAmount);
                }
            }
            $tm->update([
                'expires_at' => $newExpiry,
                'price_paid' => $price,
            ]);

            return ['sale' => $sale, 'commissioned' => $seller && $commissionAmount > 0];
        });
        if (!$this->skipCommission && $result['commissioned'] && $seller?->user_id) {
            $formatted = number_format($commissionAmount, 2);
            $moduleName = $tm->module?->name ?? 'referral';
            User::where('user_type', UserType::SUPER_ADMIN)
                ->pluck('id')
                ->each(fn ($adminId) => NotificationSent::dispatch(
                    "New recurring commission: ৳{$formatted} for {$seller->user->name} from {$tenant->name} ({$moduleName}).",
                    $adminId,
                    'info',
                    '/admin/commissions'
                ));
            NotificationSent::dispatch(
                "You earned ৳{$formatted} recurring commission from {$tenant->name} ({$moduleName}) for ".now()->format('F Y').'.',
                $seller->user_id,
                'success',
                '/seller/wallet'
            );
        }

        $ownerId = $tenant->owner_id ?? $tenant->user_id;

        if ($ownerId) {
            $centralOwner = User::find($ownerId);
            if ($centralOwner) {
                $message = "Your \"{$tm->module?->name}\" subscription has been renewed until {$newExpiry->format('d M Y')}. ৳".number_format($price, 2).' charged.';

                $wasAlreadyInitialized = tenancy()->initialized;

                if (! $wasAlreadyInitialized) {
                    tenancy()->initialize($tenant);
                }

                $tenantUser = TenantUser::where('email', $centralOwner->email)->first();

                if ($tenantUser) {
                    NotificationSent::dispatch(
                        $message,
                        $tenantUser->id,
                        'warning',
                        '/my-modules/history',
                        null,
                        $tenant->id,
                        'admin'
                    );
                }
                if (! $wasAlreadyInitialized) {
                    tenancy()->end();
                }
            }
        }
    }
}
