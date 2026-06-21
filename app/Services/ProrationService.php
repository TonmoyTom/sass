<?php

namespace App\Services;

use App\Models\TenantModule;
use Carbon\Carbon;

class ProrationService
{
    /**
     * Plan change/upgrade-e prorated charge ber kore.
     *
     * Logic: purono plan-er baki (unused) din-er value credit hoy,
     * notun plan-er full price theke baad jay. User shudhu difference dey.
     * Notun expiry ekhon theke notun cycle (next month/year).
     *
     * @param  TenantModule  $current  ekhon-er active module
     * @param  float  $newPrice  notun tier/cycle-er full price
     * @param  string  $newCycle  monthly | yearly | one_time
     * @return array{credit: float, charge: float, full_price: float, remaining_days: int, new_expires_at: ?Carbon}
     */
    public function calculate(TenantModule $current, float $newPrice, string $newCycle): array
    {
        $now = Carbon::now();

        $credit = 0.0;
        $remainingDays = 0;

        // purono plan-er baki value (credit) — shudhu active + future expiry + paid hole
        if (
            $current->expires_at
            && $current->expires_at->isFuture()
            && $current->price_paid > 0
        ) {
            // current cycle-er total din (activate theke expiry)
            $cycleStart = $current->activated_at ?? $current->created_at ?? $now;
            $totalDays = max(1, $cycleStart->diffInDays($current->expires_at));

            // baki din (ekhon theke expiry porjonto)
            $remainingDays = (int) max(0, $now->diffInDays($current->expires_at, false));

            // per-day rate × baki din = unused credit
            $perDay = (float) $current->price_paid / $totalDays;
            $credit = round($perDay * $remainingDays, 2);

            // credit notun price-er beshi hote parbe na (over-credit na)
            $credit = min($credit, $newPrice);
        }

        // actual charge = notun full price − baki credit (0-er kom na)
        $charge = max(0.0, round($newPrice - $credit, 2));

        // notun expiry — ekhon theke notun cycle shuru
        $newExpiresAt = match ($newCycle) {
            'yearly' => $now->copy()->addYear(),
            'monthly' => $now->copy()->addMonth(),
            default => null, // one_time = lifetime
        };

        return [
            'credit' => $credit,         // purono baki value (less hocche)
            'charge' => $charge,         // ekhon actual charge
            'full_price' => round($newPrice, 2), // notun plan full price
            'remaining_days' => $remainingDays,  // purono plan-er baki din
            'new_expires_at' => $newExpiresAt,
        ];
    }

    /**
     * Shudhu charge ber kore (quick helper).
     */
    public function chargeFor(TenantModule $current, float $newPrice, string $newCycle): float
    {
        return $this->calculate($current, $newPrice, $newCycle)['charge'];
    }

    public function remainingValue(TenantModule $current): float
    {
        if (
            ! $current->expires_at
            || ! $current->expires_at->isFuture()
            || $current->price_paid <= 0
        ) {
            return 0.0;
        }

        $now = Carbon::now();
        $cycleStart = $current->activated_at ?? $current->created_at ?? $now;
        $totalDays = max(1, $cycleStart->diffInDays($current->expires_at));
        $remainingDays = (int) max(0, $now->diffInDays($current->expires_at, false));
        $perDay = (float) $current->price_paid / $totalDays;

        return round($perDay * $remainingDays, 2);
    }
}
