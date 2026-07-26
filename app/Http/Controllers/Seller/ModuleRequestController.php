<?php

namespace App\Http\Controllers\Seller;

use App\Enums\UserType;
use App\Events\NotificationSent;
use App\Http\Controllers\Controller;
use App\Models\ModulePackage;
use App\Models\SellerModuleRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ModuleRequestController extends Controller
{
    public function index(Request $request): Response
    {
        $seller = auth()->user()->sellerProfile;

        $cacheKey = sprintf(
            'seller_modules:%s:%s',
            $seller?->id ?? 'guest',
            md5($request->input('search', ''))
        );

        $modules = Cache::store('redis')
            ->tags(['table:modules', 'table:seller_module_requests'])
            ->remember($cacheKey, 300, function () use ($seller, $request) {
                return ModulePackage::query()
                    ->where('is_active', true)
                    ->when($request->filled('search'), function ($q) use ($request) {
                        $term = $request->string('search')->trim()->toString();
                        $q->where(function ($sq) use ($term) {
                            $sq->where('name', 'like', "%{$term}%")
                                ->orWhere('alias', 'like', "%{$term}%")
                                ->orWhere('module_category', 'like', "%{$term}%");
                        });
                    })
                    ->orderBy('sort_order')
                    ->with('tiers')
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
                    })
                    ->values()->all();
            });

        return Inertia::render('Seller/Modules/Index', [
            'modules' => $modules,
            'filters' => [
                'search' => $request->input('search', ''),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $seller = $user->sellerProfile;

        abort_unless($seller, 403, 'Seller profile not found');

        $data = $request->validate([
            'module_id' => ['required', Rule::exists(ModulePackage::class, 'id')],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $module = ModulePackage::findOrFail($data['module_id']);

        $created = SellerModuleRequest::firstOrCreate(
            [
                'seller_id' => $seller->id,
                'module_id' => $module->id,
            ],
            [
                'status' => 'pending',
                'note' => $data['note'] ?? null,
            ]
        );

        if (! $created->wasRecentlyCreated) {
            return back()->with('error', 'Ei module-er jonno already request kora ache.');
        }

        User::where('user_type', UserType::SUPER_ADMIN)
            ->pluck('id')
            ->each(fn ($adminId) => NotificationSent::dispatch(
                "New module request: {$user->name} has requested permission to sell the \"{$module->name}\" module.",
                $adminId,
                'info',
                '/admin/module-requests'
            ));

        NotificationSent::dispatch(
            "Your request for the \"{$module->name}\" module has been submitted. You'll be notified once an admin reviews it.",
            $user->id,
            'success',
            '/seller/modules/'
        );

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
