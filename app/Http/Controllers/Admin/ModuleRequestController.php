<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SellerModuleRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ModuleRequestController extends Controller
{
    public function index(Request $request): Response
    {
        $requests = SellerModuleRequest::query()
            ->with(['seller.user', 'module', 'reviewer'])
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->latest()
            ->paginate(15)
            ->through(fn ($r) => [
                'id' => $r->id,
                'seller_name' => $r->seller?->user?->name,
                'seller_email' => $r->seller?->user?->email,
                'module_name' => $r->module?->name,
                'module_alias' => $r->module?->alias,
                'commission' => $r->module?->commission_rate,
                'status' => $r->status,
                'note' => $r->note,
                'admin_note' => $r->admin_note,
                'reviewed_by' => $r->reviewer?->name,
                'reviewed_at' => $r->reviewed_at?->format('d M Y, h:i A'),
                'created_at' => $r->created_at?->format('d M Y'),
            ]);

        return Inertia::render('Admin/ModuleRequests/Index', [
            'requests' => $requests,
            'filters' => ['status' => $request->status],
        ]);
    }

    public function approve(SellerModuleRequest $moduleRequest): RedirectResponse
    {
        $moduleRequest->update([
            'status' => 'approved',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        return back()->with('status', 'Request approved');
    }

    public function reject(Request $request, SellerModuleRequest $moduleRequest): RedirectResponse
    {
        $data = $request->validate([
            'admin_note' => ['nullable', 'string', 'max:500'],
        ]);

        $moduleRequest->update([
            'status' => 'rejected',
            'admin_note' => $data['admin_note'] ?? null,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        return back()->with('status', 'Request rejected');
    }

    public function show(SellerModuleRequest $moduleRequest): Response
    {
        $moduleRequest->load(['seller.user', 'module.tiers', 'reviewer']);

        return Inertia::render('Admin/ModuleRequests/Show', [
            'request' => [
                'id' => $moduleRequest->id,
                'status' => $moduleRequest->status,
                'note' => $moduleRequest->note,
                'admin_note' => $moduleRequest->admin_note,
                'reviewed_by' => $moduleRequest->reviewer?->name,
                'reviewed_at' => $moduleRequest->reviewed_at?->format('d M Y, h:i A'),
                'created_at' => $moduleRequest->created_at?->format('d M Y, h:i A'),
                'seller' => [
                    'id' => $moduleRequest->seller?->id,
                    'name' => $moduleRequest->seller?->user?->name,
                    'email' => $moduleRequest->seller?->user?->email,
                    'phone' => $moduleRequest->seller?->user?->phone,
                    'referral_code' => $moduleRequest->seller?->referral_code,
                ],
                'module' => [
                    'name' => $moduleRequest->module?->name,
                    'alias' => $moduleRequest->module?->alias,
                    'category' => $moduleRequest->module?->module_category,
                    'commission_rate' => $moduleRequest->module?->commission_rate,
                    'tiers' => $moduleRequest->module?->tiers->map(fn ($t) => [
                        'name' => $t->name,
                        'monthly_price' => $t->monthly_price,
                    ])->values()->all(),
                ],
            ],
        ]);
    }
}
