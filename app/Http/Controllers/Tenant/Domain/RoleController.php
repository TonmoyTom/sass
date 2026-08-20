<?php

namespace App\Http\Controllers\Tenant\Domain;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:roles.view')->only(['index']);
        $this->middleware('can:roles.create')->only(['create', 'store']);
        $this->middleware('can:roles.edit')->only(['edit', 'update']);
        $this->middleware('can:roles.delete')->only(['destroy']);
    }

    public function index(Request $request): Response
    {
        $roles = Role::query()
            ->withCount('permissions', 'users')
            ->whereNot('name', 'Super admin')
            ->filterAndCache(
                request: $request,
                searchable: ['name'],
                filterable: [],
                sortable: ['name', 'permissions_count', 'users_count', 'created_at'],
                ttlSeconds: 300,
                perPage: 15,
                transform: fn ($r) => [
                    'id' => $r->id,
                    'name' => $r->name,
                    'permissions_count' => $r->permissions_count,
                    'users_count' => $r->users_count,
                    'created_at' => $r->created_at?->format('d M Y'),
                ]
            );

        return Inertia::render('Tenant/Domain/Roles/Index', [
            'roles' => $roles,
            'filters' => $request->only(['search', 'sort_by', 'sort_dir']),
        ]);
    }

    public function create(string $tenant): Response
    {
       $tenant = tenant('id');
        return Inertia::render('Tenant/Domain/Roles/Create', [
            'permissionGroups' => $this->groupedPermissions(),
            'moduleOptions' => $this->moduleOptions($tenant),
        ]);
    }

    public function store(Request $request, string $tenant): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name'],
            'permissions' => ['array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
            'modules' => ['array'],
            'modules.*' => ['string'],
        ]);

        $role = Role::create(['name' => $data['name'], 'guard_name' => 'tenant']);
        $role->syncPermissions($data['permissions'] ?? []);
        $role->syncModules($data['modules'] ?? []);

        $this->clearRoleRelatedCache($tenant);

        return redirect('/roles')->with('status', 'Role created');
    }

    public function edit(string $tenant, Role $role): Response
    {
         $tenant = tenant('id');
        return Inertia::render('Tenant/Domain/Roles/Edit', [
            'role' => [
                'id' => $role->id,
                'name' => $role->name,
                'permissions' => $role->permissions->pluck('name'),
                'modules' => $role->grantedModuleAliases(),
            ],
            'permissionGroups' => $this->groupedPermissions(),
            'moduleOptions' => $this->moduleOptions($tenant),
        ]);
    }

    public function update(Request $request, string $tenant, Role $role): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('roles', 'name')->ignore($role->id)],
            'permissions' => ['array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
            'modules' => ['array'],
            'modules.*' => ['string'],
        ]);

        $role->update(['name' => $data['name']]);
        $role->syncPermissions($data['permissions'] ?? []);
        $role->syncModules($data['modules'] ?? []);

        $this->clearRoleRelatedCache($tenant);

        return redirect('/roles')->with('status', 'Role updated');
    }

    public function destroy(string $tenant, Role $role): RedirectResponse
    {
        if (in_array($role->name, ['admin', 'customer'])) {
            return back()->withErrors(['role' => 'Default role delete kora jabe na.']);
        }

        $role->delete();

        $this->clearRoleRelatedCache($tenant);

        return redirect('/roles')->with('status', 'Role deleted');
    }

    /**
     * Modules a role could plausibly be granted — the tenant's own
     * purchased/enabled modules. No point offering a module the tenant
     * doesn't even have.
     */
    private function moduleOptions(string $tenant): array
    {
        $t = \App\Models\Tenant::on('mysql')->find($tenant);

        return $t?->enabledModules() ?? [];
    }

    private function clearRoleRelatedCache(string $tenant): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Cache::forget("share:workspace:{$tenant}");
    }

    private function groupedPermissions(): array
    {
        $permissions = Permission::orderBy('name')->get();

        $groups = [];

        foreach ($permissions as $perm) {
            $parts = explode('.', $perm->name, 2);
            $group = count($parts) === 2 ? $parts[0] : 'general';
            $action = count($parts) === 2 ? $parts[1] : $perm->name;

            $groups[$group][] = [
                'name' => $perm->name,
                'label' => $this->humanize($action),
            ];
        }

        return collect($groups)
            ->map(fn ($perms, $group) => [
                'group' => $group,
                'label' => $this->humanize($group),
                'permissions' => $perms,
            ])
            ->sortBy('label')
            ->values()
            ->all();
    }

    private function humanize(string $value): string
    {
        return ucwords(str_replace(['-', '_', '.'], ' ', $value));
    }
}
