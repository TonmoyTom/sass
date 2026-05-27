<?php

declare(strict_types=1);

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * Seller Dashboard Controller
 *
 * Dashboard for sellers/affiliates.
 * Shows: earnings, referrals, commissions, wallet balance.
 */
class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        return $this->respond(
            page: 'Seller/Dashboard',
            data: [
                'stats' => $this->getStats($user),
                'recentReferrals' => $this->getRecentReferrals($user),
                'recentCommissions' => $this->getRecentCommissions($user),
                'referralLink' => $this->getReferralLink($user),
            ],
        );
    }

    /**
     * Get seller statistics.
     */
    protected function getStats($user): array
    {
        // For now, returning placeholder data
        // Will be implemented when sellers table is created
        return [
            'total_earned' => 0,
            'available_balance' => 0,
            'pending_balance' => 0,
            'this_month_earnings' => 0,
            'total_referrals' => 0,
            'active_referrals' => 0,
            'conversion_rate' => 0,
        ];
    }

    protected function getRecentReferrals($user): array
    {
        return [];
    }

    protected function getRecentCommissions($user): array
    {
        return [];
    }

    /**
     * Generate seller's referral link.
     */
    protected function getReferralLink($user): string
    {
        // Will use seller's unique referral_code from sellers table
        // For now, use user ID
        $code = $user->sellerProfile?->referral_code ?? 'CODE' . $user->id;
        return config('app.url') . '/signup?ref=' . $code;
    }
}
