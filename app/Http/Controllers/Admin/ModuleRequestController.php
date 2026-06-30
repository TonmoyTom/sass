<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ModulePackage;
use App\Models\Seller;
use App\Models\SellerModuleRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ModuleRequestController extends Controller
{

      public function __construct()
    {
        $this->middleware('can:module-requests.view')->only(['index', 'show']);
        $this->middleware('can:module-requests.create')->only(['create', 'store']);
        $this->middleware('can:module-requests.approve')->only(['approve']);
        $this->middleware('can:module-requests.reject')->only(['reject']);
        $this->middleware('can:module-requests.update-status')->only(['updateStatus']);
        $this->middleware('can:module-requests.update-note')->only(['updateNote']);
    }
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

    public function create(): Response
    {
        $sellers = Seller::with('user')
            ->get()
            ->map(fn ($s) => [
                'id' => $s->id,
                'name' => $s->user?->name,
                'email' => $s->user?->email,
            ]);
 
        $modules = ModulePackage::where('is_active', true)
            ->orderBy('sort_order')
            ->get(['id', 'name', 'alias', 'module_category']);
 
        return Inertia::render('Admin/ModuleRequests/Create', [
            'sellers' => $sellers,
            'modules' => $modules,
        ]);
    }


    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'seller_id' => ['required', 'exists:sellers,id'],
            'module_id' => ['required', 'exists:modules,id'],
            'note' => ['nullable', 'string', 'max:500'],
            'auto_approve' => ['nullable', 'boolean'],
        ]);
 
        $exists = SellerModuleRequest::where('seller_id', $data['seller_id'])
            ->where('module_id', $data['module_id'])
            ->exists();
 
        if ($exists) {
            return back()->with('error', 'Ei seller-er jonno ei module-er request already ache.');
        }
 
        SellerModuleRequest::create([
            'seller_id' => $data['seller_id'],
            'module_id' => $data['module_id'],
            'status' => !empty($data['auto_approve']) ? 'approved' : 'pending',
            'note' => $data['note'] ?? null,
            'reviewed_at' => !empty($data['auto_approve']) ? now() : null,
        ]);
 
        return redirect()
            ->route('admin.module-requests.index')
            ->with('status', 'Seller-er jonno module request create kora hoyeche.');
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

    public function updateStatus(Request $request, SellerModuleRequest $moduleRequest): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:pending,approved,rejected'],
            'admin_note' => ['nullable', 'string', 'max:500'],
        ]);
 
        $moduleRequest->update([
            'status' => $data['status'],
            'admin_note' => $data['status'] === 'rejected' ? ($data['admin_note'] ?? $moduleRequest->admin_note) : null,
            'reviewed_at' => $data['status'] === 'pending' ? null : now(),
        ]);
 
        return back()->with('status', 'Request status update kora hoyeche.');
    }

    public function updateNote(Request $request, SellerModuleRequest $moduleRequest): RedirectResponse
    {
        $data = $request->validate([
            'admin_note' => ['nullable', 'string', 'max:1000'],
        ]);
 
        $moduleRequest->update([
            'admin_note' => $data['admin_note'] ?? null,
        ]);
 
        return back()->with('status', 'Note save kora hoyeche.');
    }
}
