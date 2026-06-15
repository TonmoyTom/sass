<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Referral;
use Inertia\Inertia;
use Inertia\Response;

class ReferralController extends Controller
{
    public function index(): Response
    {
        $seller = auth()->user()->sellerProfile;

        abort_unless($seller, 403, 'Seller profile not found');

        // referral stats

        $conversions = Referral::where('seller_id', $seller->id)
            ->whereNotNull('converted_at')
            ->count();

        // recent referrals (converted)
        $recent = Referral::where('seller_id', $seller->id)
            ->with('tenant')
            ->whereNotNull('converted_at')
            ->latest('converted_at')
            ->limit(10)
            ->get()
            ->map(fn ($r) => [
                'tenant_name' => $r->tenant?->name ?? 'Unknown',
                'converted_at' => $r->converted_at?->format('d M Y'),
            ]);

        $baseUrl = config('app.url');

        return Inertia::render('Seller/Referrals/Index', [
            'referral' => [
                'code' => $seller->referral_code,
                'link' => $baseUrl.'/?ref='.$seller->referral_code,
                'conversions' => $conversions,
                'total_sales' => $seller->total_sales,
                'total_earned' => $seller->total_earned,
                'recent' => $recent,
                'commission_rate' => $seller->commission_rate,
            ],
        ]);
    }
}
