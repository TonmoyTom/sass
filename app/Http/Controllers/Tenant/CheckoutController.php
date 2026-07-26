<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\PaymentSetting;
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

        // active payment methods — admin je gulo enable korche
        $paymentMethods = PaymentSetting::where('is_active', true)
            ->get()
            ->map(fn ($p) => [
                'method' => $p->method,
                'merchant_number' => $p->merchant_number,
                'bank_name' => $p->bank_name,
                'account_name' => $p->account_name,
                'account_number' => $p->account_number,
                'routing_number' => $p->routing_number,
                'branch' => $p->branch,
                'instructions' => $p->instructions,
            ]);

        // cart khali hole
        if (! $cart || $cart->items->isEmpty()) {
            return Inertia::render('Tenant/Checkout/Index', [
                'items' => [],
                'total' => 0,
                'has_credit' => false,
                'payment_methods' => $paymentMethods,
            ]);
        }

        $payTotal = 0;
        $hasCredit = false;

        $items = $cart->items->map(function ($item) use ($tenant, $proration, &$payTotal, &$hasCredit) {
            $fullPrice = (float) $item->price;
            $credit = 0;
            $charge = $fullPrice;

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
                'id' => $item->id,
                'module_name' => $item->module?->name,
                'tier_name' => $item->tier?->name,
                'billing_cycle' => $item->billing_cycle,
                'price' => $fullPrice,
                'credit' => $credit,
                'charge' => $charge,
                'is_upgrade' => $existing !== null,
            ];
        });

        return Inertia::render('Tenant/Checkout/Index', [
            'items' => $items,
            'total' => round($payTotal, 2),
            'full_total' => $cart->total(),
            'has_credit' => $hasCredit,
            'referral_code' => session('referral_code'),
            'payment_methods' => $paymentMethods,
        ]);
    }

    public function store(Request $request, PurchaseService $purchase): RedirectResponse
    {
        $data = $request->validate([
            'referral_code' => ['nullable', 'string', 'max:20'],
            'payment_method' => ['required', 'string', 'in:bkash,nagad,bank'],
            'transaction_id' => ['nullable', 'string', 'max:100'],   // bKash/Nagad TrxID, ba bank slip ref
        ]);

        $tenant = $this->tenant();
        $cart = Cart::where('tenant_id', $tenant->id)->with(['items.module', 'items.tier'])->first();

        if (! $cart || $cart->items->isEmpty()) {
            return back()->with('error', 'Cart is empty');
        }
        $methodActive = PaymentSetting::where('method', $data['payment_method'])
            ->where('is_active', true)
            ->exists();

        if (! $methodActive) {
            return back()->with('error', 'Selected payment method is currently unavailable.');
        }
        $result = $purchase->checkout(
            $tenant,
            $cart,
            $data['referral_code'] ?? null,
            $data['payment_method'],
            $data['transaction_id']
        );
        session()->forget('referral_code');

        return redirect()->route('tenant.checkout.success')
            ->with('purchase', [
                'count' => count($result['sales']),
                'commission' => $result['total_commission'],
                'invoice_numbers' => $result['invoice_numbers'],
            ]);
    }

    public function success(): Response
    {
        return Inertia::render('Tenant/Checkout/Success', [
            'purchase' => session('purchase'),
        ]);
    }
}
