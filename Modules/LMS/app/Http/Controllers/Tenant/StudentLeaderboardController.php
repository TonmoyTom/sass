<?php

namespace Modules\LMS\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\TenantUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;
use Modules\LMS\Models\AssignmentSubmission;
use Modules\LMS\Models\Course;
use Modules\LMS\Models\Enrollment;
use Modules\LMS\Models\Quiz;
use Modules\LMS\Models\QuizAttempt;
use Modules\LMS\Traits\InteractsWithStudent;

class StudentLeaderboardController extends Controller
{
    use InteractsWithStudent;

    public function show(Request $request, string $tenant, Course $course): Response
    {
        $leaderboard = $this->buildLeaderboard($course, $request->input('search'));

        return Inertia::render('LMS::Tenant/Learn/Leaderboard', [
            'course' => [
                'id' => $course->id,
                'title' => $course->title,
            ],
            'leaderboard' => $leaderboard,
            'current_student_id' => $this->studentId(),
            'filters' => ['search' => $request->input('search', '')],
        ]);
    }

    /**
     * JSON version — used by the in-page leaderboard modal (e.g. on the
     * compact course view) so it doesn't need a full page navigation.
     */
    public function json(Request $request, string $tenant, Course $course): JsonResponse
    {
        $perPage = 10;
        $page = max(1, (int) $request->input('page', 1));

        // rank must be computed against the FULL list (not just this page),
        // so build the whole ranked/filtered collection first, then slice.
        $fullLeaderboard = $this->buildLeaderboard($course, $request->input('search'));

        $paginated = new LengthAwarePaginator(
            $fullLeaderboard->forPage($page, $perPage)->values(),
            $fullLeaderboard->count(),
            $perPage,
            $page,
        );

        return response()->json([
            'leaderboard' => $paginated->items(),
            'current_page' => $paginated->currentPage(),
            'last_page' => $paginated->lastPage(),
            'total' => $paginated->total(),
            'current_student_id' => $this->studentId(),
        ]);
    }

    protected function buildLeaderboard(Course $course, ?string $search = null): Collection
    {
        $enrollments = Enrollment::where('course_id', $course->id)
            ->whereIn('status', ['active', 'completed'])
            ->get(['id', 'student_id']);

        $quizIds = Quiz::whereHas('lessons.courseModule', fn ($q) => $q->where('course_id', $course->id))
            ->pluck('id');

        $assignmentIds = $course->assignments()->pluck('id');

        // best score per student per quiz, then summed per student
        $quizScoresByStudent = QuizAttempt::whereIn('quiz_id', $quizIds)
            ->whereIn('student_id', $enrollments->pluck('student_id'))
            ->whereNotNull('score')
            ->get(['student_id', 'quiz_id', 'score'])
            ->groupBy('student_id')
            ->map(fn ($attempts) => $attempts->groupBy('quiz_id')->sum(fn ($g) => $g->max('score')));

        // graded assignment points, summed per student
        $assignmentScoresByStudent = AssignmentSubmission::whereIn('assignment_id', $assignmentIds)
            ->whereIn('student_id', $enrollments->pluck('student_id'))
            ->whereNotNull('grade')
            ->get(['student_id', 'grade'])
            ->groupBy('student_id')
            ->map(fn ($subs) => $subs->sum('grade'));

        $leaderboard = $enrollments->map(function ($enrollment) use ($quizScoresByStudent, $assignmentScoresByStudent) {
            $student = $this->studentById($enrollment->student_id);
            $quizScore = $quizScoresByStudent->get($enrollment->student_id, 0);
            $assignmentScore = $assignmentScoresByStudent->get($enrollment->student_id, 0);

            return [
                'student_id' => $enrollment->student_id,
                'name' => $student?->name ?? 'Student',
                'avatar' => $student?->avatar_url,
                'quiz_score' => round($quizScore),
                'assignment_score' => round($assignmentScore),
                'total_score' => round($quizScore + $assignmentScore),
            ];
        })
            ->sortByDesc('total_score')
            ->values()
            ->map(function ($row, $index) {
                $row['rank'] = $index + 1;

                return $row;
            });

        if ($search) {
            $leaderboard = $leaderboard->filter(
                fn ($row) => str_contains(strtolower($row['name']), strtolower($search))
            )->values();
        }

        return $leaderboard;
    }

    protected function studentById(int $id)
    {
        return TenantUser::find($id);
    }
}