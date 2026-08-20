<?php

namespace Modules\LMS\Http\Controllers\Tenant;

use App\Events\NotificationSent;
use App\Http\Controllers\Controller;
use App\Models\TenantUser;
use App\Services\FileStorageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\LMS\Models\Assignment;
use Modules\LMS\Models\AssignmentSubmission;
use Modules\LMS\Traits\InteractsWithStudent;

class StudentAssignmentController extends Controller
{
    use InteractsWithStudent;

    public function submit(Request $request, string $tenant, Assignment $assignment, FileStorageService $storage): JsonResponse
    {
        if ($assignment->isPastDue() && ! $assignment->allow_late_submission) {
            return response()->json(['error' => 'The deadline for this assignment has passed.'], 422);
        }

        $data = $request->validate([
            'submitted_text' => ['nullable', 'string', 'max:20000'],
            'file' => ['nullable', 'file', 'max:20480'], // 20MB
        ]);

        if (empty($data['submitted_text']) && ! $request->hasFile('file')) {
            return response()->json(['error' => 'Add some text or attach a file before submitting.'], 422);
        }

        $filePath = null;
        $fileName = null;

        if ($request->hasFile('file')) {
            $fileName = $request->file('file')->getClientOriginalName();
            $filePath = $storage->uploadFile($request->file('file'), 'lms/assignment-submissions');
        }

        $isLate = $assignment->isPastDue();

        $existing = AssignmentSubmission::where('assignment_id', $assignment->id)
            ->where('student_id', $this->studentId())
            ->first();

        if ($filePath && $existing?->file_path) {
            $storage->deleteFile($existing->file_path);
        }

        $submission = AssignmentSubmission::updateOrCreate(
            ['assignment_id' => $assignment->id, 'student_id' => $this->studentId()],
            [
                'submitted_text' => $data['submitted_text'] ?? null,
                'file_path' => $filePath ?? null,
                'file_name' => $fileName,
                'submitted_at' => now(),
                'is_late' => $isLate,
                // resubmitting clears any previous grade — it's a new attempt
                'grade' => null,
                'feedback' => null,
                'graded_at' => null,
                'graded_by' => null,
            ]
        );

        $this->notifyInstructors($assignment, $submission);

        return response()->json([
            'submitted_at' => $submission->submitted_at->format('d M Y, h:i A'),
            'is_late' => $submission->is_late,
            'file_url' => $submission->file_url,
            'file_name' => $submission->file_name,
        ]);
    }

    /**
     * Assignment submit hole, course-er shob instructor-ke notify koro.
     */
    protected function notifyInstructors(Assignment $assignment, AssignmentSubmission $submission): void
    {
        $course = $assignment->course;
        if (! $course) {
            return;
        }

        $student = $this->currentStudent();

        $instructorIds = $course->instructors()->pluck('users.id')
            ->push($course->instructor_id)
            ->filter()
            ->unique();

        $instructors = TenantUser::whereIn('id', $instructorIds)->get();

        foreach ($instructors as $instructor) {
            NotificationSent::dispatch(
                ($student?->name ?? 'A student')." submitted \"{$assignment->title}\"".($submission->is_late ? ' (late)' : ''),
                $instructor->id,
                'info',
                "/lms/assignments/{$assignment->id}/submissions",
                $this->studentId(),
            );
        }
    }
}
