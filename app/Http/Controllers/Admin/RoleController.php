<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:roles.view')->only(['index', 'show']);
        $this->middleware('can:roles.create')->only(['create', 'store']);
        $this->middleware('can:roles.edit')->only(['edit', 'update']);
        $this->middleware('can:roles.delete')->only(['destroy']);
    }

    public function index(Request $request): Response
    {
        $roles = Role::query()
            ->withCount('permissions', 'users')
            ->where('name', '!=', 'super-admin')
            ->when($request->search, fn ($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString()
            ->through(fn ($r) => [
                'id' => $r->id,
                'name' => $r->name,
                'guard_name' => $r->guard_name,
                'permissions_count' => $r->permissions_count,
                'users_count' => $r->users_count,
            ]);

        return Inertia::render('Admin/Roles/Index', [
            'roles' => $roles,
            'filters' => $request->only('search'),
        ]);
    }

    public function create(): Response
    {
        $permissions = Permission::orderBy('name')->get(['id', 'name']);

        return Inertia::render('Admin/Roles/Create', [
            'permissions' => $this->groupPermissions($permissions),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:roles,name'],
            'permissions' => ['array'],
            'permissions.*' => ['exists:permissions,id'],
        ]);

        $role = Role::create([
            'name' => $data['name'],
            'guard_name' => 'web',
        ]);

        $role->syncPermissions($data['permissions'] ?? []);

        return redirect()
            ->route('admin.roles.index')
            ->with('status', 'Role create kora hoyeche.');
    }

    public function edit(Role $role): Response
    {
        abort_if($role->name === 'super-admin', 403, 'Ei role edit kora jabe na.');

        $permissions = Permission::orderBy('name')->get(['id', 'name']);

        return Inertia::render('Admin/Roles/Edit', [
            'role' => [
                'id' => $role->id,
                'name' => $role->name,
                'permission_ids' => $role->permissions->pluck('id'),
            ],
            'permissions' => $this->groupPermissions($permissions),
        ]);
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        abort_if($role->name === 'super-admin', 403, 'Ei role update kora jabe na.');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:roles,name,' . $role->id],
            'permissions' => ['array'],
            'permissions.*' => ['exists:permissions,id'],
        ]);

        $role->update(['name' => $data['name']]);
        $role->syncPermissions($data['permissions'] ?? []);

        return redirect()
            ->route('admin.roles.index')
            ->with('status', 'Role update kora hoyeche.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        if ($role->name === 'super-admin') {
            return back()->with('error', 'Ei role delete kora jabe na.');
        }

        if ($role->users()->count() > 0) {
            return back()->with('error', 'Ei role-e user assign kora ache, delete kora jabe na.');
        }

        $role->delete();

        return back()->with('status', 'Role delete kora hoyeche.');
    }

    /**
     * Group permissions by their prefix (before the dot) for a nicer checkbox UI.
     * e.g. "module-requests.view" -> group "module-requests"
     */
    private function groupPermissions($permissions): array
    {
        return $permissions
            ->groupBy(fn ($p) => str_contains($p->name, '.') ? explode('.', $p->name)[0] : 'general')
            ->map(fn ($group, $key) => [
                'label' => str($key)->replace('-', ' ')->title(),
                'items' => $group->map(fn ($p) => [
                    'id' => $p->id,
                    'name' => $p->name,
                ])->values(),
            ])
            ->values()
            ->all();
    }
}
