<?php

namespace Modules\LMS\Database\Seeders;

use App\Models\Tenant;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class LmsPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Multi-tenant SaaS — not every tenant has purchased/enabled the
        // LMS module. Don't seed LMS-specific permissions/roles for a
        // tenant that doesn't have access to the module at all.
        if (! $this->tenantHasLmsAccess()) {
            $this->command?->info('LMS module not active for this tenant — skipping LmsPermissionSeeder.');

            return;
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // ── admin/instructor-side permissions (course management) ──
        $adminPermissions = [
            'lms.dashboard.view',

            'categories.view', 'categories.create', 'categories.edit', 'categories.delete',
            'subcategories.view', 'subcategories.create', 'subcategories.edit', 'subcategories.delete',
            'instructors.view', 'instructors.create', 'instructors.edit', 'instructors.delete',

            'courses.view', 'courses.create', 'courses.edit', 'courses.delete',
            'lessons.manage', // modules/lessons/faqs live inside course edit, one permission covers all of it

            'quizzes.view', 'quizzes.create', 'quizzes.edit',

            'assignments.view', 'assignments.create', 'assignments.edit', 'assignments.delete',
            'assignments.grade',
        ];

        // ── student-side permissions (their own learning area) ──
        $studentPermissions = [
            'lms.learn.view',
            'lms.my-courses.view',
            'lms.my-orders.view',
            'lms.assignments.submit',
            'lms.quiz.attempt',
            'lms.reviews.submit',
        ];

        foreach ([...$adminPermissions, ...$studentPermissions] as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'tenant',
            ]);
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Super Admin — everything (admin + student areas both, so they can
        // preview/QA the student experience too)
        $superAdmin = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'tenant']);
        $superAdmin->givePermissionTo([...$adminPermissions, ...$studentPermissions]);

        // staff — course management, but not deleting courses/categories/
        // instructors, and not grading (that's for instructors specifically)
        $staff = Role::firstOrCreate(['name' => 'staff', 'guard_name' => 'tenant']);
        $staff->givePermissionTo(array_filter(
            $adminPermissions,
            fn ($p) => ! str_ends_with($p, '.delete')
        ));

        // Instructor — full course/content management including grading,
        // but not deleting categories/subcategories (structural, admin-only)
        $instructor = Role::firstOrCreate(['name' => 'Instructor', 'guard_name' => 'tenant']);
        $instructor->givePermissionTo(array_filter(
            $adminPermissions,
            fn ($p) => ! str_starts_with($p, 'categories.') && ! str_starts_with($p, 'subcategories.')
        ));

        // Student — only their own learning area, no admin permissions at all
        $student = Role::firstOrCreate(['name' => 'Student', 'guard_name' => 'tenant']);
        $student->syncPermissions($studentPermissions);
    }

    /**
     * Whether the currently-active tenant has the LMS module
     * purchased/enabled. This seeder runs inside tenant context (via
     * tenants:seed), so the `tenant()` helper resolves the current tenant.
     */
    protected function tenantHasLmsAccess(): bool
    {
        if (! function_exists('tenant') || ! tenant()) {
            // not running inside a tenant context at all — nothing to gate,
            // let it proceed rather than silently no-op.
            return true;
        }

        $currentTenant = Tenant::on('mysql')->find(tenant('id'));

        return $currentTenant?->hasModule('lms') ?? false;
    }
}
