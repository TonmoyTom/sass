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
use Illuminate\Support\Str;

class ProcessSubscriptionRenewal implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 60;

    public function __construct(
        public int $tenantModuleId,
        public bool $skipCommission = false,
        public bool $isFree = false,
        public ?string $freeNote = null,
        public ?int $renewedBy = null,
    ) {}

    public function handle(): void
    {
        $tm = TenantModule::with(['tenant', 'module', 'tier'])->find($this->tenantModuleId);

        if (! $tm || $tm->status !== 'active' || $tm->access_type !== 'subscription') {
            return;
        }

        if (! $this->isFree && ($tm->expires_at === null || $tm->expires_at->isFuture())) {
            return;
        }

        $tenant = $tm->tenant;
        if (! $tenant) {
            return;
        }

        $period = now()->format('Y-m');

        // free renewal-eo seller resolve koro (referral track korar jonno), commission amount 0 hobe
        $seller = $tenant->referred_by
            ? Seller::with('user', 'wallet')
                ->where('id', $tenant->referred_by)
                ->where('status', 'active')
                ->first()
            : null;

        $price = $this->isFree
            ? 0.0
            : ($tm->billing_cycle === 'yearly'
                ? (float) ($tm->tier?->yearly_price ?? $tm->price_paid)
                : (float) ($tm->tier?->monthly_price ?? $tm->price_paid));

        $newExpiry = $tm->billing_cycle === 'yearly'
            ? $tm->expires_at->copy()->addYear()
            : $tm->expires_at->copy()->addMonth();

        // free hole commission amount always 0
        $commissionAmount = ($this->isFree || ! $seller)
            ? 0
            : round($price * ($seller->commission_rate / 100), 2);

        $result = DB::transaction(function () use (
            $tm, $tenant, $seller, $price, $newExpiry, $commissionAmount, $period,
        ) {
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
                'payment_method' => $this->isFree ? 'free' : null,
                'transaction_id' => $this->isFree ? 'FREE-'.strtoupper(Str::random(8)) : null,
                'sold_at' => now(),
                'free_renewed_by' => $this->isFree ? $this->renewedBy : null,   // ← auth()->id() na
                'free_renewal_note' => $this->isFree ? $this->freeNote : null,
                'is_free_renewal' => $this->isFree,
            ]);

            // seller thakle Commission record SOBSOMOY create koro — free hole amount=0
            if ($seller) {
                $dupe = Commission::where('sale_id', $sale->id)
                    ->where('commission_type', 'recurring')
                    ->where('period', $period)
                    ->exists();

                if (! $dupe) {
                    Commission::create([
                        'seller_id' => $seller->id,
                        'sale_id' => $sale->id,
                        'amount' => $commissionAmount,          // free hole 0
                        'rate' => $seller->commission_rate,
                        'commission_type' => 'recurring',
                        'period' => $period,
                        'status' => $this->isFree ? 'free' : 'pending',   // free status alada
                        'hold_until' => $this->isFree ? null : now()->addDays(30),
                    ]);

                    // wallet e taka jabe SHUDHU jodi actual commission amount thake
                    if ($commissionAmount > 0) {
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
            }

            $tm->update([
                'expires_at' => $newExpiry,
                'price_paid' => $price,
                'is_free_renewal' => $this->isFree,
                'free_renewed_by' => $this->isFree ? auth()->id() : null,
                'free_renewal_note' => $this->isFree ? $this->freeNote : null,
            ]);

            return ['sale' => $sale, 'commissioned' => $seller && $commissionAmount > 0];
        });
        if (! $this->skipCommission && $result['commissioned'] && $seller?->user_id) {
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

        if ($this->isFree && $seller?->user_id) {
            $moduleName = $tm->module?->name ?? 'referral';

            NotificationSent::dispatch(
                "{$tenant->name}'s \"{$moduleName}\" subscription was renewed for free by our team this cycle — no commission applies.",
                $seller->user_id,
                'info',
                '/seller/commissions'
            );
        }

        // tenant owner notification
        $ownerId = $tenant->owner_id ?? $tenant->user_id;

        if ($ownerId) {
            $centralOwner = User::find($ownerId);

            if ($centralOwner) {
                $message = $this->isFree
                    ? "Your \"{$tm->module?->name}\" subscription has been renewed for free until {$newExpiry->format('d M Y')} by our team."
                    : "Your \"{$tm->module?->name}\" subscription has been renewed until {$newExpiry->format('d M Y')}. ৳".number_format($price, 2).' charged.';

                $wasAlreadyInitialized = tenancy()->initialized;

                try {
                    if (! $wasAlreadyInitialized) {
                        tenancy()->initialize($tenant);
                    }

                    $tenantUser = TenantUser::where('email', $centralOwner->email)->first();

                    if ($tenantUser) {
                        NotificationSent::dispatch(
                            $message,
                            $tenantUser->id,
                            $this->isFree ? 'success' : 'warning',
                            '/my-modules/history',
                            null,
                            $tenant->id,
                            'admin'
                        );
                    }
                } finally {
                    if (! $wasAlreadyInitialized) {
                        tenancy()->end();
                    }
                }
            }
        }
    }
}
