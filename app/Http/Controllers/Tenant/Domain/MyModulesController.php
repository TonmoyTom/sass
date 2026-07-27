<?php

namespace App\Http\Controllers\Tenant\Domain;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessSubscriptionRenewal;
use App\Models\CompanySetting;
use App\Models\ModulePackage;
use App\Models\Sale;
use App\Models\TenantModule;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;

class MyModulesController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:my-modules.view')->only(['index', 'show', 'purchaseHistory']);
        $this->middleware('can:my-modules.invoice')->only(['invoice']);
    }

    public function index(Request $request): Response
    {
        $tenantId = tenant('id');

        // 1. purchased/active modules
        $purchasedModuleIds = TenantModule::on('mysql')
            ->where('tenant_id', $tenantId)
            ->pluck('module_id');

        $modules = TenantModule::on('mysql')
            ->where('tenant_id', $tenantId)
            ->with(['module', 'tier'])
            ->orderByDesc('purchased_at')
            ->get()
            ->map(fn ($tm) => [
                'id' => $tm->id,
                'module_name' => $tm->module?->name ?? '—',
                'module_icon' => $tm->module?->icon,
                'tier_name' => $tm->tier?->name,
                'status' => $tm->status,
                'access_type' => $tm->access_type,
                'billing_cycle' => $tm->billing_cycle,
                'price_paid' => $tm->price_paid,
                'purchased_at' => $tm->purchased_at?->format('d M Y'),
                'expires_at' => $tm->expires_at?->format('d M Y'),
                'is_expiring_soon' => $tm->expires_at && $tm->expires_at->isBetween(now(), now()->addDays(7)),
                'is_expired' => $tm->expires_at && $tm->expires_at->isPast(),
            ]);

        // 2. baki available modules — jegulo purchase kora hoyni ekhono
        $availableModules = ModulePackage::on('mysql')
            ->where('is_active', true)
            ->whereNotIn('id', $purchasedModuleIds)
            ->with('tiers')
            ->orderBy('sort_order')
            ->get()
            ->map(fn ($module) => [
                'id' => $module->id,
                'name' => $module->name,
                'alias' => $module->alias,
                'description' => $module->description,
                'icon' => $module->icon,
                'category' => $module->module_category,
                'pricing_type' => $module->pricing_type,
                'starting_price' => $module->tiers->min('monthly_price') ?? $module->one_time_price,
                'tiers_count' => $module->tiers->count(),
            ]);

        return Inertia::render('Tenant/Domain/MyModules/Index', [
            'modules' => $modules,
            'availableModules' => $availableModules,
        ]);
    }

    public function purchaseHistory(Request $request): Response
    {
        $tenantId = tenant('id');

        $sales = Sale::on('mysql')
            ->where('tenant_id', $tenantId)
            ->with(['module', 'tenant.owner', 'seller.user', 'tier'])
            ->filterAndCache(
                $request,
                searchable: ['module.name', 'invoice_number'],
                filterable: ['status', 'sale_type'],
                sortable: ['amount', 'sold_at', 'created_at', 'invoice_number'],
                ttlSeconds: 180,
                perPage: 20,
                transform: fn ($sale) => [
                    'id' => $sale->id,
                    'invoice_number' => $sale->invoice_number,
                    'module_name' => $sale->module?->name ?? '—',
                    'tier_name' => $sale->tier?->name,
                    'amount' => $sale->amount,
                    'sale_type' => $sale->sale_type,
                    'status' => $sale->status,
                    'sold_at' => $sale->sold_at?->format('d M Y'),
                    'created_at' => $sale->created_at?->format('d M Y'),
                    'invoice_no' => 'INV-'.str_pad((string) $sale->id, 6, '0', STR_PAD_LEFT),
                ]
            );

        $stats = Cache::store('redis')
            ->tags(['table:'.$tenantId.':'.(new Sale)->getTable()])
            ->remember("sale_stats:{$tenantId}", 180, fn () => [
                'counts' => [
                    'all' => Sale::on('mysql')->where('tenant_id', $tenantId)->count(),
                    'completed' => Sale::on('mysql')->where('tenant_id', $tenantId)->where('status', 'completed')->count(),
                    'pending' => Sale::on('mysql')->where('tenant_id', $tenantId)->where('status', 'pending')->count(),
                ],
                'totals' => [
                    'spent' => (float) Sale::on('mysql')->where('tenant_id', $tenantId)->where('status', 'completed')->sum('amount'),
                ],
            ]);

        return Inertia::render('Tenant/Domain/MyModules/History', [
            'sales' => $sales,
            'stats' => $stats,
            'filters' => [
                'search' => $request->input('search', ''),
                'status' => $request->input('status', ''),
                'sale_type' => $request->input('sale_type', ''),
                'sort_by' => $request->input('sort_by', 'created_at'),
                'sort_dir' => $request->input('sort_dir', 'desc'),
            ],
        ]);
    }

    public function show(string $tenant, $id): Response
    {

        $tenantId = tenant('id');
        $order = Sale::on('mysql')
            ->where('tenant_id', $tenantId)
            ->where('id', $id)
            ->with(['module', 'tenant.owner.info', 'seller.user', 'tier', 'commission'])->first();

        return Inertia::render('Tenant/Domain/MyModules/Show', [
            'order' => [
                'id' => $order->id,
                'invoice_number' => $order->invoice_number,
                'amount' => $order->amount,
                'note' => $order->free_renewal_note,
                'commission' => $order->commission_amount,
                'admin_amount' => $order->admin_amount,
                'sale_type' => $order->sale_type,
                'status' => $order->status,
                'sold_at' => $order->sold_at?->format('d M Y, h:i A'),
                'module_name' => $order->module?->name,
                'tier_name' => $order->tier?->name,
                'tenant_name' => $order->tenant?->name,
                'tenant_email' => $order->tenant?->owner?->email,
                'seller_name' => $order->seller?->user?->name,
                'is_free_renewal' => $order->is_free_renewal,
                'free_renewed_by_name' => $order?->free_renewed_by
                  ? User::find($order->free_renewed_by)?->name
                  : null,
            ],
        ]);
    }

    public function invoice(string $tenant, $id)
    {

        $companySetting = CompanySetting::first();
        $tenantId = tenant('id');
        $order = Sale::on('mysql')
            ->where('id', $id)
            ->where('tenant_id', $tenantId)
            ->with(['module', 'tenant.owner.info', 'seller.user', 'tier', 'commission'])->first();

        return Inertia::render('Tenant/Domain/MyModules/Invoice', [
            'order' => [
                'id' => $order->id,
                'invoice_no' => 'INV-'.str_pad((string) $order->id, 6, '0', STR_PAD_LEFT),
                'invoice_number' => $order->invoice_number,
                'note' => $order->free_renewal_note,
                'is_free_renewal' => $order->is_free_renewal,
                'free_renewed_by_name' => $order?->free_renewed_by
                  ? User::find($order->free_renewed_by)?->name
                  : null,
                'commission' => $order->commission_amount,
                'admin_amount' => $order->admin_amount,
                'amount' => $order->amount,
                'sale_type' => $order->sale_type,
                'status' => $order->status,
                'sold_at' => $order->sold_at?->format('d M Y'),
                'module_name' => $order->module?->name,
                'tier_name' => $order->tier?->name,
                'tenant_name' => $order->tenant?->name,
                'tenant_email' => $order->tenant?->owner?->email,
                'tenant_address' => [
                    'city' => $order->tenant?->owner?->info?->city,
                    'country' => $order->tenant?->owner?->info?->country,
                ],
                'platform' => [
                    'name' => $companySetting?->company_name ?? config('app.name'),
                    'logo_url' => $companySetting?->logo_url,
                    'domain' => tenant()->domains->first()?->domain,
                ],
            ],
        ]);
    }

    public function renew(Request $request, string $tenant, string $tenantModuleId): RedirectResponse
    {
        $tenantId = tenant('id');

        $tm = TenantModule::on('mysql')
            ->where('id', (int) $tenantModuleId)
            ->where('tenant_id', $tenantId)
            ->with(['tenant', 'module', 'tier'])
            ->first();
        if (! $tm) {
            return back()->with('error', 'Module not found.');
        }

        if ($tm->access_type === 'lifetime') {
            return back()->with('error', 'This module does not require renewal.');
        }

        if ($tm->status !== 'active') {
            return back()->with('error', 'This module is not currently active.');
        }

        ProcessSubscriptionRenewal::dispatchSync($tm->id, true);

        Cache::store('redis')
            ->tags(['table:'.$tenantId.':tenant_modules'])
            ->flush();

        return back()->with('status', 'Your module has been renewed successfully.');
    }
}
