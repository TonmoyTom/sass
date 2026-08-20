<?php

namespace Modules\LMS\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\LMS\Models\CourseCategory;
use Modules\LMS\Models\CourseSubcategory;

class CourseSubcategoryController extends Controller
{

    public function __construct()
    {
        $this->middleware('can:subcategories.view')->only(['index']);
        $this->middleware('can:subcategories.create')->only(['store']);
        $this->middleware('can:subcategories.edit')->only(['update']);
        $this->middleware('can:subcategories.delete')->only(['destroy']);
    }
        
    public function index(Request $request): Response
    {
        $subcategories = CourseSubcategory::query()
            ->with('category:id,name')
            // ->withCount('courses')
            ->filterAndCache(
                $request,
                searchable: ['name'],
                filterable: ['is_active', 'category_id'],
                sortable: ['name', 'sort_order', 'created_at'],
                ttlSeconds: 300,
                perPage: 20,
                transform: fn ($s) => [
                    'id' => $s->id,
                    'name' => $s->name,
                    'slug' => $s->slug,
                    'is_active' => $s->is_active,
                    'sort_order' => $s->sort_order,
                    // 'courses_count' => $s->courses_count,
                    'category_id' => $s->category_id,
                    'category_name' => $s->category?->name,
                ]
            );

        $categoryOptions = CourseCategory::where('is_active', true)
            ->orderBy('sort_order')
            ->get(['id', 'name']);

        return Inertia::render('LMS::Tenant/Subcategories/Index', [
            'subcategories' => $subcategories,
            'categoryOptions' => $categoryOptions,
            'filters' => [
                'search' => $request->input('search', ''),
                'is_active' => $request->input('is_active', ''),
                'category_id' => $request->input('category_id', ''),
                'sort_by' => $request->input('sort_by', 'sort_order'),
                'sort_dir' => $request->input('sort_dir', 'asc'),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'category_id' => ['required', 'exists:course_categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        CourseSubcategory::create($data);

        return back()->with('status', 'Subcategory created successfully.');
    }

    public function update(Request $request, CourseSubcategory $subcategory): RedirectResponse
    {
        $data = $request->validate([
            'category_id' => ['required', 'exists:course_categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $subcategory->update($data);

        return back()->with('status', 'Subcategory updated successfully.');
    }

    public function destroy(CourseSubcategory $subcategory): RedirectResponse
    {
        if ($subcategory->courses()->exists()) {
            return back()->with('error', 'Cannot delete subcategory with existing courses.');
        }

        $subcategory->delete();

        return back()->with('status', 'Subcategory deleted.');
    }
}
