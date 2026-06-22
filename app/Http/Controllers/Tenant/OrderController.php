<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function index(Request $request): Response
    {
        $tenant = auth()->user()->ownedTenants()->firstOrFail();

        $base = fn () => Sale::where('tenant_id', $tenant->id);

        $orders = $base()
            ->with(['module', 'tier'])
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->when($request->search, fn ($q, $s) => $q->whereHas('module', fn ($mq) => $mq->where('name', 'like', "%{$s}%")
            ))
            ->latest()
            ->paginate(20)
            ->withQueryString()
            ->through(fn ($sale) => [
                'id' => $sale->id,
                'module_name' => $sale->module?->name ?? '—',
                'tier_name' => $sale->tier?->name,
                'amount' => $sale->amount,
                'sale_type' => $sale->sale_type,
                'status' => $sale->status,
                'sold_at' => $sale->sold_at?->format('d M Y'),
                'created_at' => $sale->created_at?->format('d M Y'),
            ]);

        return Inertia::render('Tenant/Orders/Index', [
            'orders' => $orders,
            'filters' => [
                'status' => $request->status,
                'search' => $request->search,
            ],
            'counts' => [
                'all' => $base()->count(),
                'completed' => $base()->where('status', 'completed')->count(),
                'pending' => $base()->where('status', 'pending')->count(),
            ],
            'totals' => [
                'spent' => $base()->where('status', 'completed')->sum('amount'),
            ],
        ]);
    }

    public function show(Sale $order): Response
    {
        $tenant = auth()->user()->ownedTenants()->firstOrFail();
        abort_unless($order->tenant_id === $tenant->id, 403);

        $order->load(['module', 'tier']);

        return Inertia::render('Tenant/Orders/Show', [
            'order' => [
                'id' => $order->id,
                'amount' => $order->amount,
                'sale_type' => $order->sale_type,
                'status' => $order->status,
                'sold_at' => $order->sold_at?->format('d M Y, h:i A'),
                'module_name' => $order->module?->name,
                'tier_name' => $order->tier?->name,
            ],
        ]);
    }

    public function invoice(Sale $order)
    {
        $tenant = auth()->user()->ownedTenants()->with('owner.info')->firstOrFail();
        abort_unless($order->tenant_id === $tenant->id, 403);

        $order->load(['module', 'tier']);

        return Inertia::render('Tenant/Orders/Invoice', [
            'order' => [
                'id' => $order->id,
                'invoice_no' => 'INV-'.str_pad((string) $order->id, 6, '0', STR_PAD_LEFT),
                'amount' => $order->amount,
                'sale_type' => $order->sale_type,
                'status' => $order->status,
                'sold_at' => $order->sold_at?->format('d M Y'),
                'module_name' => $order->module?->name,
                'tier_name' => $order->tier?->name,
                'tenant_name' => $tenant->name,
                'tenant_email' => $tenant->owner?->email,
                'tenant_address' => [
                    'city' => $tenant->owner?->info?->city,
                    'country' => $tenant->owner?->info?->country,
                ],
            ],
        ]);
    }
}
