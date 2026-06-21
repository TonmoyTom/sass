<?php

namespace App\Http\Controllers\Tenant\Domain;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index(): Response
    {
        $roles = Role::withCount('permissions', 'users')->latest()->get()->map(fn ($r) => [
            'id' => $r->id,
            'name' => $r->name,
            'permissions_count' => $r->permissions_count,
            'users_count' => $r->users_count,
            'created_at' => $r->created_at?->format('d M Y'),
        ]);

        return Inertia::render('Tenant/Domain/Roles/Index', [
            'roles' => $roles,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Tenant/Domain/Roles/Create', [
            'permissionGroups' => $this->groupedPermissions(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name'],
            'permissions' => ['array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        $role = Role::create(['name' => $data['name'], 'guard_name' => 'web']);
        $role->syncPermissions($data['permissions'] ?? []);

        return redirect('/roles')->with('status', 'Role created');
    }

    public function edit(string $tenant, Role $role): Response
    {

        return Inertia::render('Tenant/Domain/Roles/Edit', [
            'role' => [
                'id' => $role->id,
                'name' => $role->name,
                'permissions' => $role->permissions->pluck('name'),
            ],
            'permissionGroups' => $this->groupedPermissions(),
        ]);
    }

    public function update(Request $request, string $tenant, Role $role): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('roles', 'name')->ignore($role->id)],
            'permissions' => ['array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        $role->update(['name' => $data['name']]);
        $role->syncPermissions($data['permissions'] ?? []);

        return redirect('/roles')->with('status', 'Role updated');
    }

    public function destroy(string $tenant, Role $role): RedirectResponse
    {

        if (in_array($role->name, ['admin', 'customer'])) {
            return back()->withErrors(['role' => 'Default role delete kora jabe na.']);
        }

        $role->delete();

        return redirect('/roles')->with('status', 'Role deleted');
    }

    private function groupedPermissions(): array
    {
        $permissions = Permission::orderBy('name')->get();

        $groups = [];

        foreach ($permissions as $perm) {
            $parts = explode('.', $perm->name, 2);
            $group = count($parts) === 2 ? $parts[0] : 'general';
            $label = count($parts) === 2 ? $parts[1] : $perm->name;

            $groups[$group][] = [
                'name' => $perm->name,
                'label' => ucwords(str_replace('_', ' ', $label)),
            ];
        }

        return collect($groups)->map(fn ($perms, $group) => [
            'group' => $group,
            'label' => ucwords(str_replace('_', ' ', $group)),
            'permissions' => $perms,
        ])->values()->all();
    }
}
