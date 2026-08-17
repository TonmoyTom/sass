<?php

namespace Modules\LMS\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\LMS\Models\CourseCategory;

class CourseCategoryController extends Controller
{
    public function index(Request $request): Response
    {
        $categories = CourseCategory::query()
            // ->withCount('courses')
            ->filterAndCache(
                $request,
                searchable: ['name'],
                filterable: ['is_active'],
                sortable: ['name', 'sort_order', 'created_at'],
                ttlSeconds: 300,
                perPage: 20,
                transform: fn ($c) => [
                    'id' => $c->id,
                    'name' => $c->name,
                    'slug' => $c->slug,
                    'description' => $c->description,
                    'is_active' => $c->is_active,
                    'courses_count' => $c->courses_count,
                    'sort_order' => $c->sort_order,
                ]
            );

        return Inertia::render('LMS::Tenant/Categories/Index', [
            'categories' => $categories,
            'filters' => [
                'search' => $request->input('search', ''),
                'sort_by' => $request->input('sort_by', 'sort_order'),
                'sort_dir' => $request->input('sort_dir', 'asc'),
                'is_active' => $request->input('is_active', ''),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        CourseCategory::create($data);

        return back()->with('status', 'Category created successfully.');
    }

    public function update(Request $request, string $tenant, CourseCategory $category): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $category->update($data);

        return back()->with('status', 'Category updated successfully.');
    }

    public function destroy(CourseCategory $category): RedirectResponse
    {
        if ($category->courses()->exists()) {
            return back()->with('error', 'Cannot delete category with existing courses.');
        }

        $category->delete();

        return back()->with('status', 'Category deleted.');
    }
}
