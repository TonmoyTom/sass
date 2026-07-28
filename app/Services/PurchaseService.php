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
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PurchaseService
{
    public function __construct(
        protected ProrationService $proration
    ) {}

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

        $seller = null;

        if ($referralCode) {
            $seller = Seller::where('referral_code', $referralCode)
                ->where('status', 'active')
                ->first();
        }

        $result = DB::transaction(function () use ($paymentMethod, $tenant, $cart, $seller, $transactionId) {
            $sales = [];
            $totalCommission = 0;
            $period = now()->format('Y-m');
            $purchasedModuleAliases = [];   // ← notun, permission-sync-er jonno track kori

            foreach ($cart->items as $item) {
                $fullPrice = (float) $item->price;
                $isOneTime = $item->billing_cycle === 'one_time';

                $existing = TenantModule::where('tenant_id', $tenant->id)
                    ->where('module_id', $item->module_id)
                    ->first();

                $amount = $fullPrice;
                $expiresAt = $this->cycleExpiry($item->billing_cycle);

                if ($existing && $existing->status === 'active') {
                    $p = $this->proration->calculate($existing, $fullPrice, $item->billing_cycle);
                    $amount = $p['charge'];
                    $expiresAt = $p['new_expires_at'];
                }

                $commissionAmount = $seller
                    ? round($amount * ($seller->commission_rate / 100), 2)
                    : 0;
                $adminAmount = $amount - $commissionAmount;

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

                if ($seller && $commissionAmount > 0) {
                    Commission::create([
                        'seller_id' => $seller->id,
                        'sale_id' => $sale->id,
                        'amount' => $commissionAmount,
                        'rate' => $seller->commission_rate,
                        'commission_type' => $isOneTime ? 'one_time' : 'recurring',
                        'period' => $isOneTime ? null : $period,
                        'status' => 'pending',
                        'hold_until' => now()->addDays(30),
                    ]);

                    $totalCommission += $commissionAmount;
                }

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
                        'price_paid' => $amount,
                        'billing_cycle' => $item->billing_cycle,
                        'referred_by' => $seller?->id,
                    ]
                );

                $sales[] = $sale;

                // module-er alias/slug track kori — permission sync-er jonno
                if ($item->module?->alias) {
                    $purchasedModuleAliases[] = $item->module->alias;
                }
            }

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

            $cart->items()->delete();

            return [
                'sales' => $sales,
                'seller' => $seller,
                'total_commission' => $totalCommission,
                'invoice_numbers' => collect($sales)->pluck('invoice_number')->all(),
                'purchased_module_aliases' => $purchasedModuleAliases,
            ];
        });

        $this->syncModulePermissions($tenant, $result['purchased_module_aliases']);

        return $result;
    }

    /**
     * Purchase kora module-er jonno permission create + tenant owner-er role-e assign koro.
     */
    protected function syncModulePermissions(Tenant $tenant, array $moduleAliases): void
    {
        if (empty($moduleAliases)) {
            return;
        }
        $permissionsByPrefix = [
            'ecommerce' => [
                'ecommerce.view',
            ],
            'pos' => [
                'pos.view',
            ],
            'lms' => [
                'lms.view',
            ],
        ];

        $allPermissionNames = [];

        foreach ($moduleAliases as $alias) {
            $prefix = $this->permissionPrefix($alias);

            if ($prefix && isset($permissionsByPrefix[$prefix])) {
                $allPermissionNames = array_merge($allPermissionNames, $permissionsByPrefix[$prefix]);
            }
        }

        if (empty($allPermissionNames)) {
            return;
        }

        $allPermissionNames = array_unique($allPermissionNames);
        $wasAlreadyInitialized = tenancy()->initialized;
        $previousTenant = $wasAlreadyInitialized ? tenant() : null;

        try {
            // shudhu jodi already ei tenant-e active na thaki, tobei initialize kori
            if (! $wasAlreadyInitialized || tenant('id') !== $tenant->id) {
                tenancy()->initialize($tenant);
            }

            $createdPermissions = [];

            foreach ($allPermissionNames as $permName) {
                $createdPermissions[] = Permission::firstOrCreate([
                    'name' => $permName,
                    'guard_name' => 'tenant',
                ]);
            }

            $ownerRole = Role::where('guard_name', 'tenant')
                ->whereIn('name', ['Super admin', 'Admin', 'Owner'])
                ->first();

            if ($ownerRole) {
                $ownerRole->givePermissionTo($createdPermissions);
            }

            app(PermissionRegistrar::class)->forgetCachedPermissions();

            Cache::forget("share:workspace:{$tenant->id}");
        } catch (\Throwable $e) {
            report($e); // permission sync fail hole o purchase flow break na hok
        } finally {
            // amra nijei initialize korle, amra e cleanup kori
            if (! $wasAlreadyInitialized) {
                tenancy()->end();
            } elseif ($previousTenant && tenant('id') !== $previousTenant->id) {
                tenancy()->initialize($previousTenant);
            }
        }
    }

    protected function permissionPrefix(string $moduleAlias): ?string
    {
        return match ($moduleAlias) {
            'eccomarce', 'e-commerce', 'ecommerce' => 'ecommerce',
            'pos' => 'pos',
            'learning-system-management' => 'lms',
            default => null,
        };
    }

    protected function cycleExpiry(string $cycle): ?Carbon
    {
        return match ($cycle) {
            'yearly' => now()->addYear(),
            'monthly' => now()->addMonth(),
            default => null,
        };
    }
}
