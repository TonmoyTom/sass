<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function index(Request $request): Response
    {
        $orders = Sale::query()
            ->with(['module', 'tenant.owner', 'seller.user', 'tier'])
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->when($request->search, fn ($q, $s) => $q->whereHas('tenant', fn ($tq) =>
                $tq->where('name', 'like', "%{$s}%")
            ))
            ->latest()
            ->paginate(20)
            ->through(fn ($sale) => [
                'id'          => $sale->id,
                'tenant_name' => $sale->tenant?->name ?? '—',
                'module_name' => $sale->module?->name ?? '—',
                'tier_name'   => $sale->tier?->name,
                'seller_name' => $sale->seller?->user?->name,
                'amount'      => $sale->amount,
                'sale_type'   => $sale->sale_type,
                'status'      => $sale->status,
                'sold_at'     => $sale->sold_at?->format('d M Y'),
                'created_at'  => $sale->created_at?->format('d M Y'),
            ]);

        
        return Inertia::render('Admin/Orders/Index', [
            'orders'  => $orders,
            'filters' => [
                'status' => $request->status,
                'search' => $request->search,
            ],
            'counts'  => [
                'all'       => Sale::count(),
                'completed' => Sale::where('status', 'completed')->count(),
                'pending'   => Sale::where('status', 'pending')->count(),
            ],
            'totals'  => [
                'revenue' => Sale::where('status', 'completed')->sum('amount'),
            ],
        ]);
    }

    public function show(Sale $order): Response
    {
        $order->load(['module', 'tenant.owner.info', 'seller.user', 'tier', 'commission']);

        return Inertia::render('Admin/Orders/Show', [
            'order' => [
                'id'              => $order->id,
                'amount'          => $order->amount,
                'commission'      => $order->commission_amount,
                'admin_amount'    => $order->admin_amount,
                'sale_type'       => $order->sale_type,
                'status'          => $order->status,
                'sold_at'         => $order->sold_at?->format('d M Y, h:i A'),
                'module_name'     => $order->module?->name,
                'tier_name'       => $order->tier?->name,
                'tenant_name'     => $order->tenant?->name,
                'tenant_email'    => $order->tenant?->owner?->email,
                'seller_name'     => $order->seller?->user?->name,
            ],
        ]);
    }

    public function invoice(Sale $order)
    {
        $order->load(['module', 'tenant.owner.info', 'tier']);

        return Inertia::render('Admin/Orders/Invoice', [
            'order' => [
                'id'           => $order->id,
                'invoice_no'   => 'INV-'.str_pad((string) $order->id, 6, '0', STR_PAD_LEFT),
                'amount'       => $order->amount,
                'sale_type'    => $order->sale_type,
                'status'       => $order->status,
                'sold_at'      => $order->sold_at?->format('d M Y'),
                'module_name'  => $order->module?->name,
                'tier_name'    => $order->tier?->name,
                'tenant_name'  => $order->tenant?->name,
                'tenant_email' => $order->tenant?->owner?->email,
                'tenant_address' => [
                    'city'    => $order->tenant?->owner?->info?->city,
                    'country' => $order->tenant?->owner?->info?->country,
                ],
            ],
        ]);
    }
}
