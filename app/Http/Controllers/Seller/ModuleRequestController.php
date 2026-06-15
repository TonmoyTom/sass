<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\ModulePackage;
use App\Models\SellerModuleRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ModuleRequestController extends Controller
{
    public function index(): Response
    {
        $seller = auth()->user()->sellerProfile;
        $modules = ModulePackage::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(function ($module) use ($seller) {
                $req = $seller?->moduleRequests->firstWhere('module_id', $module->id);

                return [
                    'id' => $module->id,
                    'name' => $module->name,
                    'alias' => $module->alias,
                    'description' => $module->description,
                    'icon' => $module->icon,
                    'category' => $module->module_category,
                    'pricing_type' => $module->pricing_type,
                    'commission_rate' => $seller?->commission_rate ?? 0,
                    'm_commission_rate' => $module->commission_rate ?? 0,
                    'starting_price' => $module->tiers->min('monthly_price'),
                    'tiers_count' => $module->tiers->count(),
                    'request_status' => $req?->status,
                    'request_id' => $req?->id,
                    'admin_note' => $req?->admin_note,
                ];
            });

        return Inertia::render('Seller/Modules/Index', [
            'modules' => $modules,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $seller = auth()->user()->sellerProfile;

        abort_unless($seller, 403, 'Seller profile not found');

        $data = $request->validate([
            'module_id' => ['required', 'exists:modules,id'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $exists = SellerModuleRequest::where('seller_id', $seller->id)
            ->where('module_id', $data['module_id'])
            ->exists();

        if ($exists) {
            return back()->with('error', 'Ei module-er jonno already request kora ache.');
        }

        SellerModuleRequest::create([
            'seller_id' => $seller->id,
            'module_id' => $data['module_id'],
            'status' => 'pending',
            'note' => $data['note'] ?? null,
        ]);

        return back()->with('status', 'Module sell korar request pathano hoyeche. Admin review korbe.');
    }

    public function show(SellerModuleRequest $moduleRequest): Response
    {
        $seller = auth()->user()->sellerProfile;

        abort_unless($moduleRequest->seller_id === $seller?->id, 403);

        $moduleRequest->load('module.tiers');

        return Inertia::render('Seller/Modules/Show', [
            'request' => [
                'id' => $moduleRequest->id,
                'status' => $moduleRequest->status,
                'note' => $moduleRequest->note,
                'admin_note' => $moduleRequest->admin_note,
                'reviewed_at' => $moduleRequest->reviewed_at?->format('d M Y'),
                'created_at' => $moduleRequest->created_at?->format('d M Y'),
                'module' => [
                    'name' => $moduleRequest->module?->name,
                    'description' => $moduleRequest->module?->description,
                    'category' => $moduleRequest->module?->module_category,
                    'module_commission' => $moduleRequest->module?->commission_rate, // module-er (reference)
                    'seller_commission' => $seller?->commission_rate,                 // seller-er (actual)
                    'tiers' => $moduleRequest->module?->tiers->where('is_active', true)->map(fn ($t) => [
                        'name' => $t->name,
                        'monthly_price' => $t->monthly_price,
                        'yearly_price' => $t->yearly_price,
                    ])->values()->all(),
                ],
            ],
        ]);
    }
}
