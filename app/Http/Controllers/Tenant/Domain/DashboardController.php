<?php

namespace App\Http\Controllers\Tenant\Domain;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;
use Modules\LMS\Models\Assignment;
use Modules\LMS\Models\AssignmentSubmission;
use Modules\LMS\Models\Certificate;
use Modules\LMS\Models\Course;
use Modules\LMS\Models\CourseOrder;
use Modules\LMS\Models\CourseReview;
use Modules\LMS\Models\Enrollment;
use Modules\LMS\Models\Quiz;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $enabledModules = tenant()?->enabledModules() ?? [];

        return Inertia::render('Tenant/Domain/Dashboard', [
            'lms' => in_array('learning-system-management', $enabledModules)
                ? $this->buildLmsDashboard(auth()->user())
                : null,
        ]);
    }

    /**
     * Role-aware LMS dashboard data — same widget-set shape regardless of
     * role, but scope differs: admin/staff sees the whole tenant, instructor
     * sees only their own courses, student sees only their own enrollments.
     */
    protected function buildLmsDashboard($user): array
    {
        $roleNames = $user->roles->pluck('name')->all();

        $view = match (true) {
            in_array('Super admin', $roleNames), in_array('staff', $roleNames) => 'admin',
            in_array('Instructor', $roleNames) => 'instructor',
            in_array('Student', $roleNames) => 'student',
            default => 'student', // no role assigned — least-privilege fallback
        };

        return match ($view) {
            'admin' => ['view' => 'admin', ...$this->adminLmsStats()],
            'instructor' => ['view' => 'instructor', ...$this->instructorLmsStats($user)],
            'student' => ['view' => 'student', ...$this->studentLmsStats($user)],
        };
    }

    protected function adminLmsStats(): array
    {
        $totalCourses = Course::count();
        $totalStudents = Enrollment::distinct('student_id')->count('student_id');

        $revenueStats = CourseOrder::where('status', 'completed')
            ->selectRaw('COALESCE(SUM(amount), 0) as revenue, COUNT(*) as total_orders')
            ->first();

        $pendingGrading = AssignmentSubmission::whereNull('grade')->count();

        $chart = $this->monthlyRevenueChart(CourseOrder::where('status', 'completed'));

        $topCourses = Course::withCount('enrollments')
            ->orderByDesc('enrollments_count')
            ->take(5)
            ->get(['id', 'title'])
            ->map(fn ($c) => ['id' => $c->id, 'title' => $c->title, 'enrollments' => $c->enrollments_count]);

        $recentEnrollments = Enrollment::with('course:id,title')
            ->latest('enrolled_at')
            ->take(6)
            ->get()
            ->map(fn ($e) => [
                'student_name' => \App\Models\TenantUser::find($e->student_id)?->name ?? 'Student',
                'course_title' => $e->course?->title,
                'enrolled_at' => $e->enrolled_at?->format('d M Y'),
            ]);

        $recentOrders = CourseOrder::with('course:id,title')
            ->latest('purchased_at')
            ->take(6)
            ->get()
            ->map(fn ($o) => [
                'student_name' => \App\Models\TenantUser::find($o->student_id)?->name ?? 'Student',
                'course_title' => $o->course?->title,
                'amount' => (float) $o->amount,
                'status' => $o->status,
                'purchased_at' => $o->purchased_at?->format('d M Y'),
            ]);

        $avgRating = CourseReview::avg('rating');

        return [
            'total_courses' => $totalCourses,
            'total_students' => $totalStudents,
            'revenue' => (float) $revenueStats->revenue,
            'total_orders' => (int) $revenueStats->total_orders,
            'pending_grading' => $pendingGrading,
            'total_quizzes' => Quiz::count(),
            'total_assignments' => Assignment::count(),
            'total_certificates' => Certificate::count(),
            'avg_rating' => $avgRating ? round($avgRating, 1) : null,
            'revenue_chart' => $chart,
            'top_courses' => $topCourses,
            'recent_enrollments' => $recentEnrollments,
            'recent_orders' => $recentOrders,
        ];
    }

    protected function instructorLmsStats($user): array
    {
        $courseIds = Course::where('instructor_id', $user->id)
            ->orWhereHas('instructors', fn ($q) => $q->where('users.id', $user->id))
            ->pluck('id');

        $totalCourses = $courseIds->count();
        $totalStudents = Enrollment::whereIn('course_id', $courseIds)
            ->distinct('student_id')
            ->count('student_id');

        $pendingGrading = AssignmentSubmission::whereNull('grade')
            ->whereHas('assignment', fn ($q) => $q->whereIn('course_id', $courseIds))
            ->count();

        $topCourses = Course::whereIn('id', $courseIds)
            ->withCount('enrollments')
            ->orderByDesc('enrollments_count')
            ->take(5)
            ->get(['id', 'title'])
            ->map(fn ($c) => ['id' => $c->id, 'title' => $c->title, 'enrollments' => $c->enrollments_count]);

        $recentEnrollments = Enrollment::whereIn('course_id', $courseIds)
            ->with('course:id,title')
            ->latest('enrolled_at')
            ->take(6)
            ->get()
            ->map(fn ($e) => [
                'student_name' => \App\Models\TenantUser::find($e->student_id)?->name ?? 'Student',
                'course_title' => $e->course?->title,
                'enrolled_at' => $e->enrolled_at?->format('d M Y'),
            ]);

        return [
            'total_courses' => $totalCourses,
            'total_students' => $totalStudents,
            'pending_grading' => $pendingGrading,
            'top_courses' => $topCourses,
            'recent_enrollments' => $recentEnrollments,
        ];
    }

    protected function studentLmsStats($user): array
    {
        $enrollments = Enrollment::where('student_id', $user->id)->get();

        $upcomingAssignments = Assignment::whereIn('course_id', $enrollments->pluck('course_id'))
            ->whereNotNull('due_date')
            ->where('due_date', '>=', now())
            ->whereDoesntHave('submissions', fn ($q) => $q->where('student_id', $user->id))
            ->orderBy('due_date')
            ->take(5)
            ->get(['id', 'title', 'due_date'])
            ->map(fn ($a) => [
                'title' => $a->title,
                'due_date' => $a->due_date->format('d M Y, h:i A'),
            ]);

        return [
            'total_enrolled' => $enrollments->count(),
            'completed' => $enrollments->where('status', 'completed')->count(),
            'in_progress' => $enrollments->where('status', 'active')->count(),
            'upcoming_assignments' => $upcomingAssignments,
        ];
    }

    protected function monthlyRevenueChart($query): array
    {
        $start = now()->copy()->subMonths(11)->startOfMonth();

        $rows = (clone $query)
            ->where('purchased_at', '>=', $start)
            ->selectRaw("DATE_FORMAT(purchased_at, '%Y-%m') as ym, SUM(amount) as t")
            ->groupBy('ym')
            ->pluck('t', 'ym');

        $chart = [];
        for ($i = 0; $i < 12; $i++) {
            $key = $start->copy()->addMonths($i)->format('Y-m');
            $chart[] = (float) ($rows[$key] ?? 0);
        }

        return $chart;
    }
}
