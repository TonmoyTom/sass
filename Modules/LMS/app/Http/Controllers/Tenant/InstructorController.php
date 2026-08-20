<?php

namespace Modules\LMS\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\TenantUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Modules\LMS\Models\Course;
use Spatie\Permission\Models\Role;

class InstructorController extends Controller
{

    public function __construct()
    {
        $this->middleware('can:instructors.view')->only(['index']);
        $this->middleware('can:instructors.create')->only(['store']);
        $this->middleware('can:instructors.edit')->only(['update']);
        $this->middleware('can:instructors.delete')->only(['destroy']);
    }
        
    public function index(Request $request): Response
    {
        // Instructor is a TenantUser carrying the "Instructor" role, not a
        // separate table — courses.instructor_id already points at users.id.
        $instructors = TenantUser::query()
            ->whereHas('roles', fn ($q) => $q->where('name', 'Instructor'))
            ->with('info:user_id,bio')
            ->filterAndCache(
                $request,
                searchable: ['name', 'email', 'phone'],
                filterable: [],
                sortable: ['name', 'email', 'created_at'],
                ttlSeconds: 120,
                perPage: 15,
                transform: fn ($u) => [
                    'id' => $u->id,
                    'name' => $u->name,
                    'email' => $u->email,
                    'phone' => $u->phone,
                    'avatar' => $u->avatar_url,
                    'bio' => $u->info?->bio,
                    'courses_count' => Course::where('instructor_id', $u->id)->count(),
                ]
            );

        return Inertia::render('LMS::Tenant/Instructors/Index', [
            'instructors' => $instructors,
            'filters' => [
                'search' => $request->input('search', ''),
                'sort_by' => $request->input('sort_by', 'name'),
                'sort_dir' => $request->input('sort_dir', 'asc'),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'password' => ['required', 'confirmed', 'min:8'],
            'avatar' => ['nullable', 'image', 'max:2048'],
        ]);

        $avatarPath = null;
        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
        }

        $user = TenantUser::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'avatar' => $avatarPath,
            'password' => Hash::make($data['password']),
        ]);

        $user->info()->create([
            'first_name' => $data['name'],
            'last_name' => '',
            'bio' => $data['bio'] ?? null,
        ]);

        $user->assignRole($this->instructorRole());

        return back()->with('status', 'Instructor added.');
    }

    public function update(Request $request, string $tenant, TenantUser $instructor): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($instructor->id)],
            'phone' => ['nullable', 'string', 'max:20'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'password' => ['nullable', 'confirmed', 'min:8'],
            'avatar' => ['nullable', 'image', 'max:2048'],
        ]);

        $instructor->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
        ]);

        if ($request->hasFile('avatar')) {
            if ($instructor->avatar) {
                Storage::disk('public')->delete($instructor->avatar);
            }
            $instructor->update(['avatar' => $request->file('avatar')->store('avatars', 'public')]);
        }

        if (! empty($data['password'])) {
            $instructor->update(['password' => Hash::make($data['password'])]);
        }

        $instructor->info()->updateOrCreate(
            ['user_id' => $instructor->id],
            ['bio' => $data['bio'] ?? null]
        );

        return back()->with('status', 'Instructor updated.');
    }

    public function destroy(string $tenant, TenantUser $instructor): RedirectResponse
    {
        if (Course::where('instructor_id', $instructor->id)->exists()) {
            return back()->with('error', 'Reassign this instructor\'s courses to someone else before removing them.');
        }

        $instructor->delete();

        return back()->with('status', 'Instructor removed.');
    }

    protected function instructorRole(): Role
    {
        return Role::firstOrCreate(['name' => 'Instructor', 'guard_name' => 'tenant']);
    }
}