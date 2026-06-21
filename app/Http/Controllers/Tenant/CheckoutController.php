<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\TenantModule;
use App\Services\ProrationService;
use App\Services\PurchaseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CheckoutController extends Controller
{
    protected function tenant()
    {
        return auth()->user()->ownedTenants()->firstOrFail();
    }

    public function index(ProrationService $proration): Response
    {
        $tenant = $this->tenant();
        $cart = Cart::where('tenant_id', $tenant->id)->with(['items.module', 'items.tier'])->first();

        // cart khali hole
        if (! $cart || $cart->items->isEmpty()) {
            return Inertia::render('Tenant/Checkout/Index', [
                'items'      => [],
                'total'      => 0,
                'has_credit' => false,
            ]);
        }

        $payTotal = 0;
        $hasCredit = false;

        $items = $cart->items->map(function ($item) use ($tenant, $proration, &$payTotal, &$hasCredit) {
            $fullPrice = (float) $item->price;
            $credit = 0;
            $charge = $fullPrice;

            // already owned (upgrade/change) hole proration
            $existing = TenantModule::where('tenant_id', $tenant->id)
                ->where('module_id', $item->module_id)
                ->where('status', 'active')
                ->first();

            if ($existing) {
                $p = $proration->calculate($existing, $fullPrice, $item->billing_cycle);
                $credit = $p['credit'];
                $charge = $p['charge'];
                if ($credit > 0) {
                    $hasCredit = true;
                }
            }

            $payTotal += $charge;

            return [
                'id'            => $item->id,
                'module_name'   => $item->module?->name,
                'tier_name'     => $item->tier?->name,
                'billing_cycle' => $item->billing_cycle,
                'price'         => $fullPrice,   // full price
                'credit'        => $credit,      // baki value (less)
                'charge'        => $charge,      // actual pay
                'is_upgrade'    => $existing !== null,
            ];
        });

        return Inertia::render('Tenant/Checkout/Index', [
            'items'         => $items,
            'total'         => round($payTotal, 2),   // actual pay total (prorated)
            'full_total'    => $cart->total(),         // full price total
            'has_credit'    => $hasCredit,
            'referral_code' => session('referral_code'),
        ]);
    }

    public function store(Request $request, PurchaseService $purchase): RedirectResponse
    {
        $data = $request->validate([
            'referral_code' => ['nullable', 'string', 'max:20'],
        ]);

        $tenant = $this->tenant();
        $cart = Cart::where('tenant_id', $tenant->id)->with(['items.module', 'items.tier'])->first();

        if (! $cart || $cart->items->isEmpty()) {
            return back()->with('error', 'Cart is empty');
        }

        // === PAYMENT GATEWAY EKHANE ASHBE (pore) ===
        // ekhon payment skip — direct purchase

        $result = $purchase->checkout($tenant, $cart, $data['referral_code'] ?? null);

        session()->forget('referral_code');

        return redirect()->route('tenant.checkout.success')
            ->with('purchase', [
                'count'      => count($result['sales']),
                'commission' => $result['total_commission'],
            ]);
    }

    public function success(): Response
    {
        return Inertia::render('Tenant/Checkout/Success', [
            'purchase' => session('purchase'),
        ]);
    }
}
