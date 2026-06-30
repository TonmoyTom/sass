<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserType;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\FileStorageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class StaffController extends Controller
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
        $staff = User::query()
            ->role('staff')
            ->when($request->search, fn ($q) => $q->where('name', 'like', "%{$request->search}%")
                ->orWhere('email', 'like', "%{$request->search}%"))
            ->latest()
            ->paginate(15)
            ->withQueryString()
            ->through(fn ($u) => [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'avatar' => $u->avatar,
                'status' => $u->status,
                'created_at' => $u->created_at?->format('d M Y'),
            ]);

        return Inertia::render('Admin/Staff/Index', [
            'staff' => $staff,
            'filters' => $request->only('search'),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Staff/Create');
    }

    public function store(Request $request, FileStorageService $storage): RedirectResponse
    {
        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['nullable', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
        ]);

        $avatarPath = null;
        if ($request->hasFile('avatar')) {
            $avatarPath = $storage->uploadImage(
                $request->file('avatar'),
                'avatars',
                ['width' => 400, 'height' => 400, 'quality' => 85]
            );
        }

        $user = User::create([
            'name' => trim($data['first_name'].' '.($data['last_name'] ?? '')),
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'password' => Hash::make($data['password']),
            'avatar' => $avatarPath,
            'status' => 'active',
            'email_verified_at' => now(),
            'user_type' => UserType::STAFF,
            'bio' => 'Staff',
        ]);

        $user->info()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'] ?? '',
                'address' => $data['address'] ?? null,
                'city' => $data['city'] ?? null,
                'country' => $data['country'] ?? null,
                'postal_code' => $data['postal_code'] ?? null,
            ]
        );

        $user->assignRole('staff');

        return redirect()
            ->route('admin.staff.index')
            ->with('status', 'Staff account create kora hoyeche.');
    }

    public function edit(User $staff): Response
    {
        $staff->load('info');

        return Inertia::render('Admin/Staff/Edit', [
            'staff' => [
                'id' => $staff->id,
                'first_name' => $staff->info?->first_name,
                'last_name' => $staff->info?->last_name,
                'email' => $staff->email,
                'phone' => $staff->phone,
                'avatar' => $staff->avatar,
                'status' => $staff->status,
                'address' => $staff->info?->address,
                'city' => $staff->info?->city,
                'country' => $staff->info?->country,
                'postal_code' => $staff->info?->postal_code,
            ],
        ]);
    }

    public function update(Request $request, User $staff, FileStorageService $storage): RedirectResponse
    {
        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['nullable', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$staff->id],
            'phone' => ['nullable', 'string', 'max:20'],
            'status' => ['required', 'in:active,inactive'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
        ]);

        $avatarPath = $staff->avatar;
        if ($request->hasFile('avatar')) {
            $storage->deleteFile($staff->avatar);
            $avatarPath = $storage->uploadImage(
                $request->file('avatar'),
                'avatars',
                ['width' => 400, 'height' => 400, 'quality' => 85]
            );
        }

        $staff->update([
            'name' => trim($data['first_name'].' '.($data['last_name'] ?? '')),
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'status' => $data['status'],
            'avatar' => $avatarPath,
        ]);

        if (! empty($data['password'])) {
            $staff->update(['password' => Hash::make($data['password'])]);
        }

        $staff->info()->updateOrCreate(
            ['user_id' => $staff->id],
            [
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'] ?? '',
                'address' => $data['address'] ?? null,
                'city' => $data['city'] ?? null,
                'country' => $data['country'] ?? null,
                'postal_code' => $data['postal_code'] ?? null,
            ]
        );

        return redirect()
            ->route('admin.staff.index')
            ->with('status', 'Staff account update kora hoyeche.');
    }

    public function destroy(User $staff, FileStorageService $storage): RedirectResponse
    {
        if ($staff->id === auth()->id()) {
            return back()->with('error', 'Nijeke delete kora jay na.');
        }

        $storage->deleteFile($staff->avatar);
        $staff->delete();

        return back()->with('status', 'Staff account delete kora hoyeche.');
    }
}
