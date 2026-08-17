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

class EnrollmentController extends Controller
{
    public function index(Request $request): Response
    {
        $studentId = auth('tenant')->id();

        $enrollments = Enrollment::where('student_id', $studentId)
            ->with('course')
            ->latest('enrolled_at')
            ->get()
            ->map(fn ($e) => [
                'id' => $e->id,
                'course_id' => $e->course_id,
                'course_title' => $e->course?->title,
                'course_thumbnail' => $e->course?->thumbnail,
                'status' => $e->status,
                'progress' => $e->progressPercentage(),
                'enrolled_at' => $e->enrolled_at?->format('d M Y'),
            ]);

        return Inertia::render('LMS::Tenant/MyCourses/Index', [
            'enrollments' => $enrollments,
        ]);
    }

    /**
     * Course purchase/enroll.
     */
    public function store(Request $request, Course $course, CourseEnrollmentService $service): RedirectResponse
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
}
