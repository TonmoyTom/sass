<?php

namespace Modules\LMS\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\LMS\Models\Course;
use Modules\LMS\Models\CourseCategory;
use Modules\LMS\Models\Enrollment;
use Modules\LMS\Services\CourseEnrollmentService;

class StudentCourseController extends Controller
{
    public function browse(Request $request): Response
    {
        $studentId = auth('tenant')->id();

        $enrolledCourseIds = Enrollment::where('student_id', $studentId)
            ->pluck('course_id')
            ->toArray();

        $courses = Course::query()
            ->where('status', 'published')
            ->with(['category', 'instructor'])
            ->withCount('enrollments')
            ->filterAndCache(
                $request,
                searchable: ['title'],
                filterable: ['category_id', 'is_free'],
                sortable: ['title', 'created_at', 'price'],
                ttlSeconds: 180,
                perPage: 12,
                transform: fn ($c) => [
                    'id' => $c->id,
                    'title' => $c->title,
                    'slug' => $c->slug,
                    'thumbnail' => $c->thumbnail,
                    'description' => $c->description,
                    'category_name' => $c->category?->name,
                    'is_free' => $c->is_free,
                    'price' => $c->price,
                    'enrollments_count' => $c->enrollments_count,
                    'instructor_name' => $c->instructor?->name,
                    'is_enrolled' => in_array($c->id, $enrolledCourseIds ?? []),
                ]
            );

        return Inertia::render('LMS::Tenant/Learn/Browse', [
            'courses' => $courses,
            'categories' => CourseCategory::where('is_active', true)->orderBy('sort_order')->get(['id', 'name']),
            'filters' => [
                'search' => $request->input('search', ''),
                'category_id' => $request->input('category_id', ''),
                'is_free' => $request->input('is_free', ''),
                'sort_by' => $request->input('sort_by', 'created_at'),
                'sort_dir' => $request->input('sort_dir', 'desc'),
            ],
        ]);
    }

    /**
     * Single course detail page — enroll/purchase button soho.
     */
    public function show(string $tenant, Course $course): Response
    {
        abort_unless($course->status === 'published', 404);

        $studentId = auth('tenant')->id();

        $enrollment = Enrollment::where('course_id', $course->id)
            ->where('student_id', $studentId)
            ->first();

        $course->load(['category', 'instructor', 'modules.lessons']);

        return Inertia::render('LMS::Tenant/Learn/CourseDetail', [
            'course' => [
                'id' => $course->id,
                'title' => $course->title,
                'slug' => $course->slug,
                'description' => $course->description,
                'thumbnail' => $course->thumbnail,
                'category_name' => $course->category?->name,
                'instructor_name' => $course->instructor?->name,
                'is_free' => $course->is_free,
                'price' => $course->price,
                'modules' => $course->modules->map(fn ($m) => [
                    'id' => $m->id,
                    'title' => $m->title,
                    'lessons' => $m->lessons->map(fn ($l) => [
                        'id' => $l->id,
                        'title' => $l->title,
                        'is_free_preview' => $l->is_free_preview,
                        'has_video' => (bool) ($l->video_url || $l->video_path),
                        'has_ebook' => (bool) $l->ebook_path,
                        'video_duration_minutes' => $l->video_duration_minutes,
                    ]),
                ]),
                'total_lessons' => $course->modules->sum(fn ($m) => $m->lessons->count()),
            ],
            'enrollment' => $enrollment ? [
                'status' => $enrollment->status,
                'enrolled_at' => $enrollment->enrolled_at?->format('d M Y'),
            ] : null,
        ]);
    }

    /**
     * Purchase/Enroll.
     */
    public function enroll(Request $request, string $tenant, Course $course, CourseEnrollmentService $service): RedirectResponse
    {
        $data = $request->validate([
            'payment_method' => [$course->is_free ? 'nullable' : 'required', 'string'],
            'transaction_id' => [$course->is_free ? 'nullable' : 'required', 'string', 'max:100'],
        ]);

        $student = auth('tenant')->user();

        try {
            $service->purchase(
                $course,
                $student,
                $data['payment_method'] ?? null,
                $data['transaction_id'] ?? null,
            );
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('tenant.lms.my-courses.index')
            ->with('status', 'Enrolled successfully!');
    }

    /**
     * "My Courses" — enrolled course list, progress soho.
     */
    public function myCourses(Request $request): Response
    {
        $studentId = auth('tenant')->id();

        $enrollments = Enrollment::where('student_id', $studentId)
            ->with(['course.category'])
            ->latest('enrolled_at')
            ->get()
            ->map(fn ($e) => [
                'id' => $e->id,
                'course_id' => $e->course_id,
                'course_title' => $e->course?->title,
                'course_thumbnail' => $e->course?->thumbnail,
                'category_name' => $e->course?->category?->name,
                'status' => $e->status,
                'progress' => $e->progressPercentage(),
                'enrolled_at' => $e->enrolled_at?->format('d M Y'),
            ]);

        return Inertia::render('LMS::Tenant/Learn/MyCourses', [
            'enrollments' => $enrollments,
        ]);
    }
}
