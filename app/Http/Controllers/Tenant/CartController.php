<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\ModuleTier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\TenantModule;
use App\Services\ProrationService;

class CartController extends Controller
{

    protected function tenantId(): string
    {
        return auth()->user()->ownedTenants()->firstOrFail()->id;
    }

    protected function cart(): Cart
    {
        return Cart::firstOrCreate(
            ['tenant_id' => $this->tenantId()],
            ['user_id' => auth()->id()]
        );
    }

    public function index(ProrationService $proration): Response
    {
        $cart = $this->cart()->load(['items.module', 'items.tier']);
        $tenantId = $this->tenantId();

        $payTotal = 0;
        $hasCredit = false;

        $items = $cart->items->map(function ($item) use ($tenantId, $proration, &$payTotal, &$hasCredit) {
            $fullPrice = (float) $item->price;
            $credit = 0;
            $charge = $fullPrice;

            // already owned (upgrade) hole proration
            $existing = TenantModule::where('tenant_id', $tenantId)
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

        return Inertia::render('Tenant/Cart/Index', [
            'items' => $items,
            'total' => round($payTotal, 2),
            'full_total' => $cart->total(),
            'has_credit' => $hasCredit,
        ]);
    }

    public function add(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'module_tier_id' => ['required', 'exists:module_tiers,id'],
            'billing_cycle' => ['required', 'in:monthly,yearly'],
            'referral_code' => ['nullable', 'string', 'max:20'],
        ]);

        $tier = ModuleTier::with('module')->findOrFail($data['module_tier_id']);

        $price = $data['billing_cycle'] === 'yearly'
            ? $tier->yearly_price
            : $tier->monthly_price;

        $cart = $this->cart();
        $cart->items()->updateOrCreate(
            ['module_id' => $tier->module_id],
            [
                'module_tier_id' => $tier->id,
                'billing_cycle' => $data['billing_cycle'],
                'price' => $price,
                'referral_code' => $data['referral_code'] ?? session('referral_code'),
            ]
        );

        return back()->with('status', 'Added to cart');
    }

    public function remove(int $itemId): RedirectResponse
    {
        $this->cart()->items()->where('id', $itemId)->delete();

        return back()->with('status', 'Removed from cart');
    }

    public function clear(): RedirectResponse
    {
        $this->cart()->items()->delete();

        return back()->with('status', 'Cart cleared');
    }
}
