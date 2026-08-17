<?php

namespace Modules\LMS\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Services\FileStorageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Modules\LMS\Models\Course;
use Modules\LMS\Models\CourseCategory;
use Modules\LMS\Models\CourseSubcategory;

class CourseController extends Controller
{
    public function index(Request $request): Response
    {
        $instructorId = auth('tenant')->id();
        $isAdmin = auth('tenant')->user()->hasRole(['Super admin', 'Admin']);

        $courses = Course::query()
            ->with(['category', 'subcategory'])
            ->withCount(['enrollments'])
            ->when(! $isAdmin, fn ($q) => $q->where('instructor_id', $instructorId))
            ->filterAndCache(
                $request,
                searchable: ['title'],
                filterable: ['status', 'category_id', 'is_free'],
                sortable: ['title', 'created_at', 'price'],
                ttlSeconds: 180,
                perPage: 20,
                transform: fn ($c) => [
                    'id' => $c->id,
                    'title' => $c->title,
                    'slug' => $c->slug,
                    'thumbnail' => $c->thumbnail_url,
                    'category_name' => $c->category?->name,
                    'subcategory_name' => $c->subcategory?->name,
                    'is_free' => $c->is_free,
                    'price' => $c->price,
                    'status' => $c->status,
                    'enrollments_count' => $c->enrollments_count,
                    'instructor_id' => $c->instructor_id,
                    'created_at' => $c->created_at?->format('d M Y'),
                ]
            );

        return Inertia::render('LMS::Tenant/Courses/Index', [
            'courses' => $courses,
            'filters' => [
                'search' => $request->input('search', ''),
                'status' => $request->input('status', ''),
                'category_id' => $request->input('category_id', ''),
                'is_free' => $request->input('is_free', ''),
                'sort_by' => $request->input('sort_by', 'created_at'),
                'sort_dir' => $request->input('sort_dir', 'desc'),
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('LMS::Tenant/Courses/Create', [
            'categories' => CourseCategory::where('is_active', true)->orderBy('sort_order')->get(['id', 'name']),
            'subcategories' => CourseSubcategory::where('is_active', true)->get(['id', 'category_id', 'name']),

        ]);
    }

    public function store(Request $request, FileStorageService $storage): RedirectResponse
    {
        $data = $this->validateCourse($request);
        $data['instructor_id'] = auth('tenant')->id();

        if ($request->hasFile('thumbnail')) {
            $data['thumbnail'] = $storage->uploadImage(
                $request->file('thumbnail'),
                'lms/thumbnails',
                ['width' => 800, 'quality' => 85]
            );
        }

        $course = Course::create($data);

        return redirect("/lms/courses/{$course->id}/edit")
            ->with('status', 'Course created. Now add modules and lessons.');
    }

    public function edit(string $tenant, $id): Response
    {

        $course = Course::findOrFail($id);
        // $this->authorizeOwnership($course);

        $course->load(['modules.lessons.quizzes', 'category', 'subcategory']);

        return Inertia::render('LMS::Tenant/Courses/Edit', [
            'course' => $course,
            'categories' => CourseCategory::where('is_active', true)->orderBy('sort_order')->get(['id', 'name']),
            'subcategories' => CourseSubcategory::where('is_active', true)->get(['id', 'category_id', 'name']),

        ]);
    }

    public function update(string $tenant, Request $request, $id, FileStorageService $storage): RedirectResponse
    {

        $course = Course::findOrFail($id);
        // $this->authorizeOwnership($course);

        $data = $this->validateCourse($request, $course->id);

        if ($request->hasFile('thumbnail')) {
            $storage->deleteFile($course->thumbnail);

            $data['thumbnail'] = $storage->uploadImage(
                $request->file('thumbnail'),
                'lms/thumbnails',
                ['width' => 800, 'quality' => 85]
            );
        }

        $course->update($data);

        return back()->with('status', 'Course updated.');
    }

    public function destroy(string $tenant, Course $course): RedirectResponse
    {
        $this->authorizeOwnership($course);

        if ($course->enrollments()->exists()) {
            return back()->with('error', 'Cannot delete a course with active enrollments.');
        }

        $course->delete();

        return redirect()->route('tenant.lms.courses.index')->with('status', 'Course deleted.');
    }

    protected function validateCourse(Request $request, ?int $courseId = null): array
    {
        return $request->validate([
            'category_id' => ['nullable', 'exists:course_categories,id'],
            'subcategory_id' => ['nullable', 'exists:course_subcategories,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            // 'thumbnail' => ['nullable', 'image', 'max:2048'],
            'is_free' => ['boolean'],
            'price' => ['required_if:is_free,false', 'nullable', 'numeric', 'min:0'],
            // 'sequential_unlock' => ['boolean'],
            'status' => ['required', Rule::in(['draft', 'published', 'archived'])],
        ]);
    }

    protected function authorizeOwnership(Course $course): void
    {
        $isAdmin = auth('tenant')->user()->hasRole(['Super admin', 'Admin']);

        if (! $isAdmin && $course->instructor_id !== auth('tenant')->id()) {
            abort(403, 'You can only manage your own courses.');
        }
    }
}
