<?php

namespace Modules\LMS\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\LMS\Models\Course;
use Modules\LMS\Models\Enrollment;
use Modules\LMS\Services\CourseEnrollmentService;
use Modules\LMS\Traits\InteractsWithStudent;

class EnrollmentController extends Controller
{
    use InteractsWithStudent;

    public function index(Request $request): Response
    {
        $enrollments = Enrollment::where('student_id', $this->studentId())
                ->with(['course.category'])
                ->latest('enrolled_at')
                ->get()
                ->map(fn ($e) => [
                    'id' => $e->id,
                    'course_id' => $e->course_id,
                    'course_title' => $e->course?->title,
                    'course_thumbnail' => $e->course?->thumbnail_url,
                    'category_name' => $e->course?->category?->name,
                    'status' => $e->status,
                    'progress' => $e->progressPercentage(),
                    'enrolled_at' => $e->enrolled_at?->format('d M Y'),
                ]);
        return Inertia::render('LMS::Tenant/Learn/MyCourses', [
            'enrollments' => $enrollments,
            'seo' => [
                'title' => 'My Courses',
                'robots' => 'noindex,nofollow',
            ],
        ]);
    }


}
