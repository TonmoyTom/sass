<?php

namespace App\Services;

use App\Models\Referral;
use App\Models\Seller;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;

class ReferralService
{
    /**
     * Referral click track — keu ?ref=CODE diye ashle.
     * Ekta referral row create kore (converted_at null).
     */
    public function trackClick(string $code, array $meta = []): ?Referral
    {
        $seller = Seller::where('referral_code', $code)
            ->where('status', 'active')
            ->first();

        if (! $seller) {
            return null;
        }

        return Referral::create([
            'seller_id'     => $seller->id,
            'referral_code' => $code,
            'ip_address'    => $meta['ip'] ?? null,
            'user_agent'    => $meta['user_agent'] ?? null,
            'landing_url'   => $meta['landing_url'] ?? null,
        ]);
    }

    /**
     * Purchase-er somoy referral convert + seller commission credit.
     *
     * @param  string  $code        seller-er referral code
     * @param  Tenant  $tenant      je tenant purchase korlo
     * @param  float   $saleAmount  purchase-er amount (commission ei amount-er upor)
     * @return array|null  ['seller' => Seller, 'commission' => float] ba null
     */
    public function convert(string $code, Tenant $tenant, float $saleAmount): ?array
    {
        $seller = Seller::where('referral_code', $code)
            ->where('status', 'active')
            ->first();

        if (! $seller) {
            return null;
        }

        return DB::transaction(function () use ($seller, $tenant, $saleAmount) {
            $commission = round($saleAmount * ($seller->commission_rate / 100), 2);
            $referral = Referral::where('seller_id', $seller->id)
                ->where('referral_code', $seller->referral_code)
                ->whereNull('converted_at')
                ->latest()
                ->first();

            if ($referral) {
                $referral->update([
                    'tenant_id'    => $tenant->id,
                    'converted_at' => now(),
                ]);
            } else {
                Referral::create([
                    'seller_id'     => $seller->id,
                    'tenant_id'     => $tenant->id,
                    'referral_code' => $seller->referral_code,
                    'converted_at'  => now(),
                ]);
            }

            // 2. tenant-e referred_by set
            $tenant->update(['referred_by' => $seller->id]);

            // 3. seller stats update
            $seller->increment('total_sales');
            $seller->increment('total_earned', $commission);

            // 4. wallet-e commission credit (pending — 30 din por available hobe)
            $wallet = $seller->wallet;
            if ($wallet) {
                $wallet->increment('pending_balance', $commission);
                $wallet->increment('total_earned', $commission);
            }

            return [
                'seller'     => $seller,
                'commission' => $commission,
            ];
        });
    }
}
