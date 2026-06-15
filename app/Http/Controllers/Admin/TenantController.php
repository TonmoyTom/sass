<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTenantRequest;
use App\Http\Requests\Admin\UpdateTenantRequest;
use App\Models\Tenant;
use App\Models\TenantUser;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class TenantController extends Controller
{
    public function index(): Response
    {
        $tenants = Tenant::query()
            ->with('domains')
            ->withCount([])
            ->latest('created_at')
            ->paginate(15)
            ->through(fn ($tenant) => [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'subdomain' => $tenant->subdomain,
                'email' => $tenant->email,
                'status' => $tenant->status,
                'domain' => $tenant->domains->first()?->domain,
                'created_at' => $tenant->created_at?->format('d M Y'),
            ]);

        return Inertia::render('Admin/Tenants/Index', [
            'tenants' => $tenants,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Tenants/Create', [
            'centralDomain' => config('tenancy.central_domains')[0] ?? 'platform.com',
        ]);
    }

    public function store(StoreTenantRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $owner = DB::transaction(function () use ($data) {
            // 1. owner user create
            $owner = User::create([
                'name' => trim($data['owner_first_name'].' '.($data['owner_last_name'] ?? '')),
                'email' => $data['owner_email'],
                'phone' => $data['phone'] ?? null,
                'password' => Hash::make($data['password']),
                'user_type' => 'tenant_owner',
                'status' => 'active',
            ]);

            $owner->info()->create([
                'first_name' => $data['owner_first_name'],
                'last_name' => $data['owner_last_name'] ?? '',
                'country' => $data['country'] ?? null,
                'city' => $data['city'] ?? null,
                'postal_code' => $data['postal_code'] ?? null,
                'facebook' => $data['facebook'] ?? null,
                'twitter' => $data['twitter'] ?? null,
                'lnkedin' => $data['linkedin'] ?? null,
            ]);

            return $owner;
        });
        $tenant = Tenant::create([
            'id' => (string) Str::uuid(),
            'name' => $data['name'],
            'slug' => Str::slug($data['name']),
            'subdomain' => $data['subdomain'],
            'database_name' => 'tenant_'.str_replace('-', '_', $data['subdomain']),
            'owner_id' => $owner->id,
            'email' => $data['owner_email'],
            'phone' => $data['phone'] ?? null,
            'status' => 'trial',
            'trial_ends_at' => now()->addDays(14),
        ]);
        $tenant->domains()->create([
            'domain' => $data['subdomain'].'.'.(config('tenancy.central_domains')[0] ?? 'localhost'),
            'is_primary' => true,
        ]);

        $this->seedTenant($tenant, $data);

        return redirect()->route('admin.tenants.index')
            ->with('status', "Tenant '{$tenant->name}' created successfully");
    }

    public function show(Tenant $tenant): Response
    {
        $tenant->load(['domains', 'owner.info']);

        return Inertia::render('Admin/Tenants/Show', [
            'tenant' => [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'slug' => $tenant->slug,
                'subdomain' => $tenant->subdomain,
                'database_name' => $tenant->database_name,
                'email' => $tenant->email,
                'phone' => $tenant->phone,
                'status' => $tenant->status,
                'trial_ends_at' => $tenant->trial_ends_at?->format('d M Y'),
                'suspended_at' => $tenant->suspended_at?->format('d M Y, h:i A'),
                'created_at' => $tenant->created_at?->format('d M Y'),
                'domain' => $tenant->domains->first()?->domain,
                'owner' => [
                    'name' => $tenant->owner?->name,
                    'email' => $tenant->owner?->email,
                    'phone' => $tenant->owner?->phone,
                    'bio' => $tenant->owner?->info?->bio,
                    'country' => $tenant->owner?->info?->country,
                    'city' => $tenant->owner?->info?->city,
                    'postal_code' => $tenant->owner?->info?->postal_code,
                    'facebook' => $tenant->owner?->info?->facebook,
                    'twitter' => $tenant->owner?->info?->twitter,
                    'linkedin' => $tenant->owner?->info?->lnkedin,
                    'instagram' => $tenant->owner?->info?->instagram,
                ],
            ],
        ]);
    }

    public function edit(Tenant $tenant): Response
    {
        $tenant->load('owner.info');

        return Inertia::render('Admin/Tenants/Edit', [
            'tenant' => [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'subdomain' => $tenant->subdomain,
                'status' => $tenant->status,
                'owner_first_name' => $tenant->owner?->info?->first_name ?? '',
                'owner_last_name' => $tenant->owner?->info?->last_name ?? '',
                'owner_email' => $tenant->owner?->email ?? '',
                'phone' => $tenant->owner?->phone ?? '',
                'country' => $tenant->owner?->info?->country ?? '',
                'city' => $tenant->owner?->info?->city ?? '',
                'postal_code' => $tenant->owner?->info?->postal_code ?? '',
                'facebook' => $tenant->owner?->info?->facebook ?? '',
                'twitter' => $tenant->owner?->info?->twitter ?? '',
                'linkedin' => $tenant->owner?->info?->lnkedin ?? '',
            ],
            'centralDomain' => config('tenancy.central_domains')[0] ?? 'localhost',
            'statuses' => [
                ['value' => 'trial', 'label' => 'Trial'],
                ['value' => 'active', 'label' => 'Active'],
                ['value' => 'suspended', 'label' => 'Suspended'],
                ['value' => 'expired', 'label' => 'Expired'],
            ],
        ]);
    }

    public function update(UpdateTenantRequest $request, Tenant $tenant): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $tenant) {
            // 1. tenant update
            $tenant->update([
                'name' => $data['name'],
                'slug' => Str::slug($data['name']),
                'email' => $data['owner_email'],
                'phone' => $data['phone'] ?? null,
                'status' => $data['status'],
            ]);
            $owner = $tenant->owner;
            if ($owner) {
                $owner->update([
                    'name' => trim($data['owner_first_name'].' '.($data['owner_last_name'] ?? '')),
                    'email' => $data['owner_email'],
                    'phone' => $data['phone'] ?? null,
                ]);

                $owner->info()->updateOrCreate(
                    ['user_id' => $owner->id],
                    [
                        'first_name' => $data['owner_first_name'],
                        'last_name' => $data['owner_last_name'] ?? '',
                        'country' => $data['country'] ?? null,
                        'city' => $data['city'] ?? null,
                        'postal_code' => $data['postal_code'] ?? null,
                        'facebook' => $data['facebook'] ?? null,
                        'twitter' => $data['twitter'] ?? null,
                        'lnkedin' => $data['linkedin'] ?? null,
                    ]
                );
            }
        });

        return redirect()->route('admin.tenants.show', $tenant->id)
            ->with('status', 'Tenant updated successfully');
    }

    public function destroy(Tenant $tenant): RedirectResponse
    {
        $owner = $tenant->owner;
        $tenant->delete();
        if ($owner && $owner->ownedTenants()->count() === 0) {
            DB::transaction(function () use ($owner) {
                $owner->info()?->delete();
                $owner->delete();
            });
        }

        return redirect()->route('admin.tenants.index')
            ->with('status', 'Tenant deleted successfully');
    }

    public function suspend(Tenant $tenant): RedirectResponse
    {
        $tenant->update([
            'status' => 'suspended',
            'suspended_at' => now(),
        ]);

        return back()->with('status', 'Tenant suspended');
    }

    public function reactivate(Tenant $tenant): RedirectResponse
    {
        $tenant->update([
            'status' => 'active',
            'suspended_at' => null,
        ]);

        return back()->with('status', 'Tenant reactivated');
    }

    protected function seedTenant(Tenant $tenant, array $data): void
    {
        $tenant->run(function () use ($data) {
            $permissions = [
                'view users',
                'create users',
                'edit users',
                'update users',
                'delete users',
            ];
            foreach ($permissions as $perm) {
                Permission::firstOrCreate([
                    'name' => $perm,
                    'guard_name' => 'web',
                ]);
            }
            $adminRole = Role::firstOrCreate([
                'name' => 'admin',
                'guard_name' => 'web',
            ]);
            $adminRole->syncPermissions($permissions);
            $admin = TenantUser::create([
                'name' => trim($data['owner_first_name'].' '.($data['owner_last_name'] ?? '')),
                'email' => $data['owner_email'],
                'password' => Hash::make($data['password']),
                'phone' => $data['phone'] ?? null,
                'email_verified_at' => now(),
            ]);

            $admin->assignRole('admin');
            $admin->info()->create([
                'first_name' => $data['owner_first_name'],
                'last_name' => $data['owner_last_name'] ?? '',
                'country' => $data['country'] ?? null,
                'city' => $data['city'] ?? null,
                'postal_code' => $data['postal_code'] ?? null,
                'facebook' => $data['facebook'] ?? null,
                'twitter' => $data['twitter'] ?? null,
                'lnkedin' => $data['linkedin'] ?? null,
            ]);
        });
    }
}
