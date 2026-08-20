<?php

namespace Modules\LMS\Http\Controllers\Tenant;

use App\Events\NotificationSent;
use App\Http\Controllers\Controller;
use App\Models\TenantUser;
use App\Services\FileStorageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\LMS\Models\Assignment;
use Modules\LMS\Models\AssignmentSubmission;

class AssignmentSubmissionController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:assignments.view')->only(['index']);
        $this->middleware('can:assignments.grade')->only(['grade']);
    }

    public function index(Request $request, string $tenant, Assignment $assignment, FileStorageService $storage): Response
    {
        $query = $assignment->submissions()->latest('submitted_at');

        if ($search = $request->input('search')) {
            $matchingStudentIds = TenantUser::where('name', 'like', "%{$search}%")->pluck('id');
            $query->whereIn('student_id', $matchingStudentIds);
        }

        $submissions = $query->paginate(10)->withQueryString();

        $submissions->getCollection()->transform(fn ($s) => [
            'id' => $s->id,
            'student_name' => $s->student()?->name ?? 'Student',
            'student_avatar' => $s->student()?->avatar_url,
            'submitted_text' => $s->submitted_text,
            'file_name' => $s->file_name,
            'file_path' => $s->file_path ? $storage->getUrl($s->file_path) : null,
            'submitted_at' => $s->submitted_at->format('d M Y, h:i A'),
            'is_late' => $s->is_late,
            'grade' => $s->grade,
            'feedback' => $s->feedback,
            'graded_at' => $s->graded_at?->format('d M Y'),
        ]);

        return Inertia::render('LMS::Tenant/Assignments/Submissions', [
            'assignment' => [
                'id' => $assignment->id,
                'title' => $assignment->title,
                'max_score' => $assignment->max_score,
                'due_date' => $assignment->due_date?->format('d M Y, h:i A'),
            ],
            'submissions' => $submissions,
            'filters' => [
                'search' => $search ?? '',
            ],
        ]);
    }

    public function grade(Request $request, string $tenant, AssignmentSubmission $submission): RedirectResponse
    {
        $data = $request->validate([
            'grade' => ['required', 'integer', 'min:0', 'max:'.$submission->assignment->max_score],
            'feedback' => ['nullable', 'string', 'max:2000'],
        ]);

        $submission->update([
            'grade' => $data['grade'],
            'feedback' => $data['feedback'] ?? null,
            'graded_at' => now(),
            'graded_by' => auth('tenant')->id(),
        ]);

        $this->notifyStudent($submission);

        return back()->with('status', 'Grade saved.');
    }

    /**
     * Grade dewa hole, student-ke notify koro — direct sei lesson-e niye
     * jay jekhane assignment-ta attached (course-level page-e na, karon
     * grade/feedback lesson-er AssignmentWidget-e dekha jay).
     */
    protected function notifyStudent(AssignmentSubmission $submission): void
    {
        $student = $submission->student();
        if (! $student) {
            return;
        }

        $assignment = $submission->assignment;
        $lessonId = $assignment?->lessons()->value('lessons.id');
        $courseId = $assignment?->course_id;

        $link = $lessonId
            ? "/lms/learn/{$lessonId}"
            : ($courseId ? "/lms/my-courses/{$courseId}" : null);

        NotificationSent::dispatch(
            "Your submission for \"{$assignment->title}\" was graded: {$submission->grade}/{$assignment->max_score}",
            $student->id,
            'success',
            $link,
            auth('tenant')->id(),
        );
    }
}
