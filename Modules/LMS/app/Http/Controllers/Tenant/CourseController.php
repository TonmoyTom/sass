<?php

namespace Modules\LMS\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\TenantUser;
use App\Services\FileStorageService;
use Illuminate\Http\JsonResponse;
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
    
    public function __construct()
       {
           $this->middleware('can:courses.view')->only(['index', 'searchInstructors']);
           $this->middleware('can:courses.create')->only(['create', 'store']);
           $this->middleware('can:courses.edit')->only(['edit', 'update', 'uploadVideo']);
           $this->middleware('can:courses.delete')->only(['destroy']);
       }
    public function index(Request $request): Response
    {
        $instructorId = auth('tenant')->id();
        $isAdmin = auth('tenant')->user()->hasRole(['Super admin', 'Admin']);

        $courses = Course::query()
            ->with(['category', 'subcategory'])
            ->withCount(['enrollments'])
            // ->when(! $isAdmin, fn ($q) => $q->where(
            //     fn ($q2) => $q2->where('instructor_id', $instructorId)
            //         ->orWhereHas('instructors', fn ($q3) => $q3->where('users.id', $instructorId))
            // ))
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
                    'discount_price' => $c->discount_price,
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
            'instructors' => $this->instructorOptions(),
        ]);
    }

    public function store(Request $request, FileStorageService $storage): RedirectResponse
    {
        $data = $this->validateCourse($request);

        $instructorIds = $data['instructor_ids'] ?? [];
        unset($data['instructor_ids']);
        $data['instructor_id'] = $instructorIds[0];

        if ($request->hasFile('thumbnail')) {
            $data['thumbnail'] = $storage->uploadImage(
                $request->file('thumbnail'),
                'lms/thumbnails',
                ['width' => 800, 'quality' => 85]
            );
        }

        if ($request->hasFile('preview_image')) {
            $data['preview_image'] = $storage->uploadImage(
                $request->file('preview_image'),
                'lms/preview-images',
                ['width' => 1280, 'quality' => 85]
            );
        }

        if (! empty($data['preview_video_path'])) {
            $data['preview_video_source'] = 'upload';
            $data['preview_video_url'] = null;
        } elseif (! empty($data['preview_video_url'])) {
            $data['preview_video_source'] = 'youtube';
            $data['preview_video_path'] = null;
        }

        $course = Course::create($data);
        $course->instructors()->sync($instructorIds);

        return redirect("/lms/courses/{$course->id}/edit")
            ->with('status', 'Course created. Now add modules and lessons.');
    }

    public function edit(string $tenant, $id): Response
    {

        $course = Course::findOrFail($id);
        // $this->authorizeOwnership($course);

        $course->load(['modules.lessons.quizzes', 'modules.lessons.assignments', 'category', 'subcategory', 'instructors:id,name', 'faqs', 'assignments']);

        return Inertia::render('LMS::Tenant/Courses/Edit', [
            'course' => $course,
            'categories' => CourseCategory::where('is_active', true)->orderBy('sort_order')->get(['id', 'name']),
            'subcategories' => CourseSubcategory::where('is_active', true)->get(['id', 'category_id', 'name']),
            'instructors' => $this->instructorOptions(),

        ]);
    }

    public function update(string $tenant, Request $request, $id, FileStorageService $storage): RedirectResponse
    {

        $course = Course::findOrFail($id);
        // $this->authorizeOwnership($course);

        $data = $this->validateCourse($request, $course->id);

        $instructorIds = $data['instructor_ids'] ?? [];
        unset($data['instructor_ids']);

       

        $data['instructor_id'] = $instructorIds[0];

        if ($request->hasFile('thumbnail')) {
            $storage->deleteFile($course->thumbnail);

            $data['thumbnail'] = $storage->uploadImage(
                $request->file('thumbnail'),
                'lms/thumbnails',
                ['width' => 800, 'quality' => 85]
            );
        }

        if ($request->hasFile('preview_image')) {
            $storage->deleteFile($course->preview_image);

            $data['preview_image'] = $storage->uploadImage(
                $request->file('preview_image'),
                'lms/preview-images',
                ['width' => 1280, 'quality' => 85]
            );
        }

        if (! empty($data['preview_video_path'])) {
            $data['preview_video_source'] = 'upload';
            $data['preview_video_url'] = null;
        } elseif (! empty($data['preview_video_url'])) {
            $data['preview_video_source'] = 'youtube';
            $data['preview_video_path'] = null;
        } else {
            $data['preview_video_source'] = null;
            $data['preview_video_path'] = null;
            $data['preview_video_url'] = null;
        }

        $course->update($data);
        $course->instructors()->sync($instructorIds);

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

    public function uploadVideo(Request $request, FileStorageService $storage): JsonResponse
    {
        $request->validate([
            'video' => ['required', 'file', 'mimes:mp4,mov', 'max:512000'], // 500MB
        ]);

        $originalName = $request->file('video')->getClientOriginalName();

        $path = $storage->uploadFile(
            $request->file('video'),
            'lms/course-preview-videos',
        );

        return response()->json([
            'path' => $path,
            'url' => $storage->getUrl($path),
            'name' => $originalName,
        ]);
    }

    protected function validateCourse(Request $request, ?int $courseId = null): array
    {
        return $request->validate([
            'category_id' => ['nullable', 'exists:course_categories,id'],
            'subcategory_id' => ['nullable', 'exists:course_subcategories,id'],
            'title' => ['required', 'string', 'max:255'],
            'short_description' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            // 'thumbnail' => ['nullable', 'image', 'max:2048'],
            // 'preview_image' => ['nullable', 'image', 'max:4096'],
            'preview_video_source' => ['nullable', Rule::in(['youtube', 'upload'])],
            'preview_video_url' => ['nullable', 'url', 'max:255'],
            'preview_video_path' => ['nullable', 'string'],
            'is_free' => ['boolean'],
            'price' => ['required_if:is_free,false', 'nullable', 'numeric', 'min:0'],
            'discount_price' => ['nullable', 'numeric', 'min:0', 'lt:price'],
            'live_class_starts_at' => ['nullable', 'date'],
            // 'sequential_unlock' => ['boolean'],
            'status' => ['required', Rule::in(['draft', 'published', 'archived'])],
            'instructor_ids' => ['nullable', 'array'],
            'instructor_ids.*' => ['integer', 'exists:users,id'],
        ]);
    }

    /**
     * Base query for users selectable as a course instructor — anyone
     * with the "Instructor" role, plus whoever's currently logged in (so
     * an admin/staff creating a course can still credit themselves).
     */
    protected function instructorQuery(?string $search = null)
    {
        $currentId = auth('tenant')->id();

        return TenantUser::query()
            ->where(fn ($q) => $q->whereHas('roles', fn ($q2) => $q2->where('name', 'Instructor'))
                ->orWhere('id', $currentId))
            ->when($search, fn ($q) => $q->where(
                fn ($q2) => $q2->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
            ))
            ->orderBy('name');
    }

    /**
     * First page for the Create/Edit form's instructor picker.
     */
    protected function instructorOptions(): array
    {
        $paginator = $this->instructorQuery()->paginate(15, ['id', 'name', 'email']);

        return [
            'data' => $paginator->items(),
            'next_page' => $paginator->hasMorePages() ? $paginator->currentPage() + 1 : null,
        ];
    }

    /**
     * AJAX endpoint the picker calls to scroll-load further pages / search.
     */
    public function searchInstructors(Request $request): JsonResponse
    {
        $paginator = $this->instructorQuery($request->input('q'))
            ->paginate(15, ['id', 'name', 'email'], 'page', (int) $request->input('page', 1));

        return response()->json([
            'data' => $paginator->items(),
            'next_page' => $paginator->hasMorePages() ? $paginator->currentPage() + 1 : null,
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