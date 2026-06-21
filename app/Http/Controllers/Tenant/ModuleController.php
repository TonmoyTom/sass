<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\ModulePackage;
use App\Models\TenantLoginToken;
use App\Models\TenantModule;
use App\Services\ProrationService;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ModuleController extends Controller
{
    public function index(ProrationService $proration): Response
    {
        $tenant = auth()->user()->ownedTenants()->firstOrFail();

        $cart = Cart::where('tenant_id', $tenant->id)->first();
        $cartCount = $cart?->items()->count() ?? 0;
        $cartModuleIds = $cart ? $cart->items()->pluck('module_id')->all() : [];

        $ownedModules = TenantModule::where('tenant_id', $tenant->id)
            ->whereIn('status', ['active', 'purchased', 'trial'])
            ->get()
            ->keyBy('module_id');

        $modules = ModulePackage::query()
            ->where('is_active', true)
            ->with(['tiers' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order')])
            ->orderBy('sort_order')
            ->get()
            ->map(function ($m) use ($cartModuleIds, $ownedModules, $proration) {
                $owned = $ownedModules->get($m->id);

                $credit = 0;
                if ($owned) {
                    $p = $proration->calculate($owned, 999999, $owned->billing_cycle);
                    $credit = $p['credit'];
                }

                return [
                    'id' => $m->id,
                    'name' => $m->name,
                    'description' => $m->description,
                    'icon' => $m->icon,
                    'category' => $m->module_category,
                    'in_cart' => in_array($m->id, $cartModuleIds),
                    'is_owned' => (bool) $owned,
                    'owned_tier_id' => $owned?->module_tier_id,
                    'owned_billing_cycle' => $owned?->billing_cycle,
                    'expires_at' => $owned?->expires_at?->format('d M Y'),
                    'credit' => $credit,   // current plan-er baki value (switch korle pabe)
                    'tiers' => $m->tiers->map(fn ($t) => [
                        'id' => $t->id,
                        'name' => $t->name,
                        'monthly_price' => $t->monthly_price,
                        'yearly_price' => $t->yearly_price,
                        'limits' => $t->limits ?? [],
                        'is_popular' => $t->is_popular,
                    ])->values()->all(),
                ];
            });

        return Inertia::render('Tenant/Modules/Index', [
            'modules' => $modules,
            'cartCount' => $cartCount,
        ]);
    }

    public function open(int $tenantModuleId)
    {
        $user = auth()->user();
        $tenant = $user->ownedTenants()->with('domains')->firstOrFail();

        // one-time token
        $token = Str::random(64);

        TenantLoginToken::create([
            'tenant_id' => $tenant->id,
            'token' => $token,
            'email' => $user->email,
            'expires_at' => now()->addMinutes(2),
        ]);

        // subdomain URL
        $domain = $tenant->domains->first()?->domain;
        $scheme = request()->getScheme();
        $port = request()->getPort();
        $portPart = in_array($port, [80, 443]) ? '' : ':'.$port;
        $domain = str_replace('127.0.0.1', 'localhost', $domain);
        $url = "{$scheme}://{$domain}{$portPart}/auto-login?token={$token}";

        return redirect()->away($url);
    }

    public function openWorkspace()
    {
        $user = auth()->user();
        $tenant = $user->ownedTenants()->with('domains')->firstOrFail();

        $token = Str::random(64);

        TenantLoginToken::create([
            'tenant_id' => $tenant->id,
            'token' => $token,
            'email' => $user->email,
            'expires_at' => now()->addMinutes(2),
        ]);
        $domain = $tenant->domains->first()?->domain;
        $domain = str_replace('127.0.0.1', 'localhost', $domain);

        $scheme = request()->getScheme();
        $port = request()->getPort();
        $portPart = in_array($port, [80, 443]) ? '' : ':'.$port;
        $redirect = '/settings';
        $url = "{$scheme}://{$domain}{$portPart}/auto-login?token={$token}&redirect=".urlencode($redirect);

        return redirect()->away($url);
    }
}
