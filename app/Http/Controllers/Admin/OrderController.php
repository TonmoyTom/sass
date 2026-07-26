<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CompanySetting;
use App\Models\Sale;
use Faker\Provider\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:orders.view')->only(['index', 'show']);
        $this->middleware('can:orders.invoice')->only(['invoice']);
    }

    public function index(Request $request): Response
    {
        $orders = Sale::query()
            ->with(['module', 'tenant.owner', 'seller.user', 'tier'])
            ->filterAndCache(
                $request,
                searchable: ['tenant.name', 'module.name', 'invoice_number'],
                filterable: ['status', 'sale_type'],
                sortable: ['amount', 'sold_at', 'created_at', 'invoice_number'],
                ttlSeconds: 180,
                perPage: 20,
                transform: fn ($sale) => [
                    'id' => $sale->id,
                    'invoice_number' => $sale->invoice_number,
                    'tenant_name' => $sale->tenant?->name ?? '—',
                    'module_name' => $sale->module?->name ?? '—',
                    'tier_name' => $sale->tier?->name,
                    'seller_name' => $sale->seller?->user?->name,
                    'amount' => $sale->amount,
                    'sale_type' => $sale->sale_type,
                    'status' => $sale->status,
                    'sold_at' => $sale->sold_at?->format('d M Y'),
                    'created_at' => $sale->created_at?->format('d M Y'),
                ]
            );
        // stats — same table tag e cache, sale create/update/delete hole auto-invalidate
        $stats = Cache::store('redis')
            ->tags(['table:'.(new Sale)->getTable()])
            ->remember('sale_stats', 180, fn () => [
                'counts' => [
                    'all' => Sale::count(),
                    'completed' => Sale::where('status', 'completed')->count(),
                    'pending' => Sale::where('status', 'pending')->count(),
                ],
                'totals' => [
                    'revenue' => (float) Sale::where('status', 'completed')->sum('amount'),
                ],
            ]);

        return Inertia::render('Admin/Orders/Index', [
            'orders' => $orders,
            'filters' => [
                'search' => $request->input('search', ''),
                'status' => $request->input('status', ''),
                'sale_type' => $request->input('sale_type', ''),
                'sort_by' => $request->input('sort_by', 'created_at'),
                'sort_dir' => $request->input('sort_dir', 'desc'),
            ],
            'counts' => $stats['counts'],
            'totals' => $stats['totals'],
        ]);
    }

    public function show(Sale $order): Response
    {
        $order->load(['module', 'tenant.owner.info', 'seller.user', 'tier', 'commission']);

        return Inertia::render('Admin/Orders/Show', [
            'order' => [
                'id' => $order->id,
                'invoice_number' => $order->invoice_number,
                'amount' => $order->amount,
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
            ],
        ]);
    }

    public function invoice(Sale $order)
    {

        $companySetting = CompanySetting::first();
        $order->load(['module', 'tenant.owner.info', 'tier']);

        return Inertia::render('Admin/Orders/Invoice', [
            'order' => [
                'id' => $order->id,
                'invoice_number' => $order->invoice_number,
                'invoice_no' => 'INV-'.str_pad((string) $order->id, 6, '0', STR_PAD_LEFT),
                'amount' => $order->amount,
                'commission' => $order->commission_amount,
                'admin_amount' => $order->admin_amount,
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
                    'domain' => config('app.url'),
                ],
            ],
        ]);
    }
}
