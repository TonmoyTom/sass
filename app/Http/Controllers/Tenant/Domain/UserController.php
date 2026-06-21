<?php

namespace App\Http\Controllers\Tenant\Domain;

use App\Http\Controllers\Controller;
use App\Models\TenantUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(string $tenant): Response
    {
        $users = TenantUser::with('roles')
            ->whereDoesntHave('roles', function ($q) {
                $q->where('name', 'Super Admin');
            })
            ->latest()
            ->get()
            ->map(fn ($u) => [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'phone' => $u->phone,
                'avatar' => $u->avatar ? $u->avatar_url : null,
                'roles' => $u->roles->pluck('name'),
            ]);

        return Inertia::render('Tenant/Domain/Users/Index', [
            'users' => $users,
        ]);
    }

    public function create(string $tenant): Response
    {
        return Inertia::render('Tenant/Domain/Users/Create', [
            'roles' => Role::whereNot('name', 'Super Admin')->orderBy('name')->pluck('name'),
        ]);
    }

    public function store(Request $request, string $tenant): RedirectResponse
    {
        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'confirmed', 'min:8'],
            'avatar' => ['nullable', 'image', 'max:2048'],   // ← avatar
            'roles' => ['array'],
            'roles.*' => ['string', 'exists:roles,name'],
        ]);

        $avatarPath = null;
        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
        }

        $user = TenantUser::create([
            'name' => $data['first_name'].' '.$data['last_name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'avatar' => $avatarPath,
            'password' => Hash::make($data['password']),
        ]);

        $user->info()->create([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
        ]);

        $user->syncRoles($data['roles'] ?? []);

        return redirect('/users')->with('status', 'User created');
    }

    public function edit(string $tenant, string $id): Response
    {
        $user = TenantUser::with(['roles', 'info'])->findOrFail($id);

        return Inertia::render('Tenant/Domain/Users/Edit', [
            'user' => [
                'id' => $user->id,
                'first_name' => $user->info?->first_name,
                'last_name' => $user->info?->last_name,
                'email' => $user->email,
                'phone' => $user->phone,
                'roles' => $user->roles->pluck('name'),
            ],
            'roles' => Role::whereNot('name', 'Super Admin')->orderBy('name')->pluck('name'),
        ]);
    }

    public function update(Request $request, string $tenant, string $id): RedirectResponse
    {
        $user = TenantUser::findOrFail($id);

        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['nullable', 'confirmed', 'min:8'],
            'avatar' => ['nullable', 'image', 'max:2048'],
            'roles' => ['array'],
            'roles.*' => ['string', 'exists:roles,name'],
        ]);

        $user->update([
            'name' => $data['first_name'].' '.$data['last_name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
        ]);

        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $user->update(['avatar' => $request->file('avatar')->store('avatars', 'public')]);
        }

        if (! empty($data['password'])) {
            $user->update(['password' => Hash::make($data['password'])]);
        }

        $user->info()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
            ]
        );

        $user->syncRoles($data['roles'] ?? []);

        return redirect('/users')->with('status', 'User updated');
    }

    public function destroy(string $tenant, string $id): RedirectResponse
    {
        $user = TenantUser::findOrFail($id);
        if ($user->id === auth()->id()) {
            return back()->withErrors(['user' => 'Nijeke delete kora jabe na.']);
        }

        $user->delete();

        return redirect('/users')->with('status', 'User deleted');
    }
}
