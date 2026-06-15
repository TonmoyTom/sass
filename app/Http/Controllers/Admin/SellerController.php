<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSellerRequest;
use App\Http\Requests\Admin\UpdateSellerRequest;
use App\Models\Seller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class SellerController extends Controller
{
    public function index(): Response
    {
        $sellers = Seller::query()
            ->with(['user', 'wallet'])
            ->latest()
            ->paginate(15)
            ->through(fn ($s) => [
                'id' => $s->id,
                'name' => $s->user?->name,
                'email' => $s->user?->email,
                'referral_code' => $s->referral_code,
                'status' => $s->status,
                'commission_rate' => $s->commission_rate,
                'total_sales' => $s->total_sales,
                'total_earned' => $s->total_earned,
                'balance' => $s->wallet?->available_balance ?? 0,
                'created_at' => $s->created_at?->format('d M Y'),
            ]);

        return Inertia::render('Admin/Sellers/Index', ['sellers' => $sellers]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Sellers/Create');
    }

    public function store(StoreSellerRequest $request): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data) {
            // 1. central user (seller)
            $user = User::create([
                'name' => trim($data['first_name'].' '.($data['last_name'] ?? '')),
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'password' => Hash::make($data['password']),
                'user_type' => 'seller',
                'status' => 'active',
            ]);

            $user->info()->create([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'] ?? '',
                'country' => $data['country'] ?? null,
                'city' => $data['city'] ?? null,
                'postal_code' => $data['postal_code'] ?? null,
            ]);

            // 2. seller profile
            $seller = Seller::create([
                'user_id' => $user->id,
                'referral_code' => Seller::generateReferralCode(),
                'status' => $data['status'] ?? 'active',
                'commission_rate' => $data['commission_rate'] ?? 70.00,
                'bank_name' => $data['bank_name'] ?? null,
                'bank_account' => $data['bank_account'] ?? null,
                'bkash_number' => $data['bkash_number'] ?? null,
                'nid_number' => $data['nid_number'] ?? null,
                'joined_at' => now(),
            ]);

            // 3. wallet (empty)
            $seller->wallet()->create([
                'available_balance' => 0,
                'pending_balance' => 0,
                'total_earned' => 0,
                'total_withdrawn' => 0,
                'currency' => 'BDT',
            ]);
        });

        return redirect()->route('admin.sellers.index')
            ->with('status', 'Seller created successfully');
    }

    public function show(Seller $seller): Response
    {
        $seller->load(['user.info', 'wallet']);

        return Inertia::render('Admin/Sellers/Show', [
            'seller' => [
                'id' => $seller->id,
                'name' => $seller->user?->name,
                'email' => $seller->user?->email,
                'phone' => $seller->user?->phone,
                'referral_code' => $seller->referral_code,
                'status' => $seller->status,
                'commission_rate' => $seller->commission_rate,
                'total_sales' => $seller->total_sales,
                'total_earned' => $seller->total_earned,
                'nid_number' => $seller->nid_number,
                'nid_verified' => $seller->nid_verified,
                'bank_name' => $seller->bank_name,
                'bank_account' => $seller->bank_account,
                'bkash_number' => $seller->bkash_number,
                'joined_at' => $seller->joined_at?->format('d M Y'),
                'created_at' => $seller->created_at?->format('d M Y'),
                'country' => $seller->user?->info?->country,
                'city' => $seller->user?->info?->city,
                'postal_code' => $seller->user?->info?->postal_code,
                'wallet' => [
                    'available_balance' => $seller->wallet?->available_balance ?? 0,
                    'pending_balance' => $seller->wallet?->pending_balance ?? 0,
                    'total_earned' => $seller->wallet?->total_earned ?? 0,
                    'total_withdrawn' => $seller->wallet?->total_withdrawn ?? 0,
                    'currency' => $seller->wallet?->currency ?? 'BDT',
                ],
            ],
        ]);
    }

    public function edit(Seller $seller): Response
    {
        $seller->load('user.info');

        return Inertia::render('Admin/Sellers/Edit', [
            'seller' => [
                'id' => $seller->id,
                'first_name' => $seller->user?->info?->first_name ?? '',
                'last_name' => $seller->user?->info?->last_name ?? '',
                'email' => $seller->user?->email ?? '',
                'phone' => $seller->user?->phone ?? '',
                'referral_code' => $seller->referral_code,
                'status' => $seller->status,
                'commission_rate' => $seller->commission_rate,
                'bank_name' => $seller->bank_name,
                'bank_account' => $seller->bank_account,
                'bkash_number' => $seller->bkash_number,
                'nid_number' => $seller->nid_number,
                'nid_verified' => $seller->nid_verified,
                'country' => $seller->user?->info?->country,      // notun
                'city' => $seller->user?->info?->city,         // notun
                'postal_code' => $seller->user?->info?->postal_code,
            ],
            'statuses' => [
                ['value' => 'active', 'label' => 'Active'],
                ['value' => 'suspended', 'label' => 'Suspended'],
                ['value' => 'pending', 'label' => 'Pending'],
            ],
        ]);
    }

    public function update(UpdateSellerRequest $request, Seller $seller): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $seller) {
            $user = $seller->user;
            if ($user) {
                $user->update([
                    'name' => trim($data['first_name'].' '.($data['last_name'] ?? '')),
                    'email' => $data['email'],
                    'phone' => $data['phone'] ?? null,
                ]);

                $user->info()->updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'first_name' => $data['first_name'],
                        'last_name' => $data['last_name'] ?? '',
                        'country' => $data['country'] ?? null,
                        'city' => $data['city'] ?? null,
                        'postal_code' => $data['postal_code'] ?? null,
                    ]
                );
            }

            $seller->update([
                'status' => $data['status'],
                'commission_rate' => $data['commission_rate'],
                'bank_name' => $data['bank_name'] ?? null,
                'bank_account' => $data['bank_account'] ?? null,
                'bkash_number' => $data['bkash_number'] ?? null,
                'nid_number' => $data['nid_number'] ?? null,
                'nid_verified' => $data['nid_verified'] ?? false,
            ]);
        });

        return redirect()->route('admin.sellers.show', $seller->id)
            ->with('status', 'Seller updated successfully');
    }

    public function destroy(Seller $seller): RedirectResponse
    {
        DB::transaction(function () use ($seller) {
            $user = $seller->user;
            $seller->delete();
            if ($user) {
                $user->info()?->delete();
                $user->delete();
            }
        });

        return redirect()->route('admin.sellers.index')
            ->with('status', 'Seller deleted successfully');
    }

    public function approve(Seller $seller): RedirectResponse
    {
        $seller->update(['status' => 'active']);

        // central user-o active
        $seller->user?->update(['status' => 'active']);

        return back()->with('status', 'Seller approved');
    }

    public function suspend(Seller $seller): RedirectResponse
    {
        $seller->update(['status' => 'suspended']);

        $seller->user?->update(['status' => 'suspended']);

        return back()->with('status', 'Seller suspended');
    }

    public function reject(Seller $seller): RedirectResponse
    {
        $seller->update(['status' => 'suspended']); // ba 'rejected' jodi enum-e thake

        $seller->user?->update(['status' => 'suspended']);

        return back()->with('status', 'Seller rejected');
    }
}
