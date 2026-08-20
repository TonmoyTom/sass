<?php

namespace Modules\LMS\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\LMS\Models\Enrollment;
use Modules\LMS\Models\Lesson;
use Modules\LMS\Models\QuizAttempt;
use Modules\LMS\Models\AssignmentSubmission;
use Modules\LMS\Classes\Services\LessonAccessService;
use Modules\LMS\Models\LessonNote;

class StudentLessonController extends Controller
{
    public function show(string $tenant, Lesson $lesson, LessonAccessService $accessService): Response
        {
            $studentId = auth('tenant')->id();
            $course = $lesson->courseModule->course;

            $enrollment = Enrollment::where('course_id', $course->id)
                ->where('student_id', $studentId)
                ->first();

            if (! $accessService->canAccess($lesson, $enrollment)) {
                abort(403, 'Complete the previous lesson to unlock this one.');
            }

            $progress = $enrollment
                ? $enrollment->progress()->where('lesson_id', $lesson->id)->first()
                : null;

            $course->load('modules.lessons');

            // flat, ordered list of every lesson in the course, so we can find
            // prev/next and build the sidebar with lock/complete state.
            $allLessons = $course->modules->flatMap->lessons->values();
            $currentIndex = $allLessons->search(fn ($l) => $l->id === $lesson->id);

            $allProgress = $enrollment
                ? $enrollment->progress()->get()->keyBy('lesson_id')
                : collect();

            $sidebarModules = $course->modules->map(fn ($module) => [
                'id' => $module->id,
                'title' => $module->title,
                'lessons' => $module->lessons->map(function ($l) use ($enrollment, $accessService, $allProgress, $lesson) {
                    $lessonProgress = $allProgress->get($l->id);

                    return [
                        'id' => $l->id,
                        'title' => $l->title,
                        'has_video' => $l->hasVideo(),
                        'has_ebook' => $l->hasEbook(),
                        'has_quiz' => $l->hasQuiz(),
                        'has_assignment' => $l->hasAssignment(),
                        'video_duration_minutes' => $l->video_duration_minutes,
                        'is_current' => $l->id === $lesson->id,
                        'is_unlocked' => $accessService->canAccess($l, $enrollment),
                        'is_complete' => $lessonProgress?->isFullyComplete() ?? false,
                    ];
                }),
            ]);

            $note = LessonNote::where('lesson_id', $lesson->id)
                ->where('student_id', $studentId)
                ->first();

            return Inertia::render('LMS::Tenant/Learn/Lesson', [
                'lesson' => [
                    'id' => $lesson->id,
                    'title' => $lesson->title,
                    'has_video' => $lesson->hasVideo(),
                    'has_ebook' => $lesson->hasEbook(),
                    'video_source' => $lesson->video_source,
                    'video_url' => $lesson->resolved_video_url,
                    'ebook_url' => $lesson->resolved_ebook_url,
                    'video_duration_minutes' => $lesson->video_duration_minutes,
                    'video_complete_threshold_seconds' => $lesson->video_complete_threshold_seconds ?? 120,
                    'quizzes' => $lesson->quizzes->map(function ($quiz) use ($studentId) {
                        $attempts = QuizAttempt::where('quiz_id', $quiz->id)
                            ->where('student_id', $studentId)
                            ->orderByDesc('attempt_number')
                            ->get();

                        return [
                            'id' => $quiz->id,
                            'title' => $quiz->title,
                            'description' => $quiz->description,
                            'question_count' => $quiz->questions()->count(),
                            'passing_score' => $quiz->passing_score,
                            'time_limit_minutes' => $quiz->time_limit_minutes,
                            'max_attempts' => $quiz->max_attempts,
                            'attempts_used' => $attempts->count(),
                            'best_score' => $attempts->max('score'),
                            'has_passed' => $attempts->contains('passed', true),
                            'can_attempt' => $quiz->max_attempts <= 0 || $attempts->count() < $quiz->max_attempts,
                        ];
                    }),
                    'assignments' => $lesson->assignments->map(function ($assignment) use ($studentId) {
                        $submission = AssignmentSubmission::where('assignment_id', $assignment->id)
                            ->where('student_id', $studentId)
                            ->first();

                        return [
                            'id' => $assignment->id,
                            'title' => $assignment->title,
                            'instructions' => $assignment->instructions,
                            'file_url' => $assignment->file_url,
                            'file_name' => $assignment->file_name,
                            'due_date' => $assignment->due_date?->toIso8601String(),
                            'max_score' => $assignment->max_score,
                            'allow_late_submission' => $assignment->allow_late_submission,
                            'is_past_due' => $assignment->isPastDue(),
                            'submission' => $submission ? [
                                'submitted_text' => $submission->submitted_text,
                                'file_name' => $submission->file_name,
                                'file_url' => $submission->file_url,
                                'submitted_at' => $submission->submitted_at->format('d M Y, h:i A'),
                                'is_late' => $submission->is_late,
                                'grade' => $submission->grade,
                                'feedback' => $submission->feedback,
                                'is_graded' => $submission->isGraded(),
                            ] : null,
                        ];
                    }),
                ],
                'progress' => $progress ? [
                    'video_watched_seconds' => $progress->video_watched_seconds,
                    'video_completed' => $progress->video_completed,
                    'ebook_opened_at' => (bool) $progress->ebook_opened_at,
                ] : null,
                'course' => [
                    'id' => $course->id,
                    'title' => $course->title,
                    'modules' => $sidebarModules,
                ],
                'navigation' => [
                    'prev_lesson_id' => $currentIndex > 0 ? $allLessons[$currentIndex - 1]->id : null,
                    'next_lesson_id' => $currentIndex < $allLessons->count() - 1 ? $allLessons[$currentIndex + 1]->id : null,
                ],
                'note' => $note?->content ?? '',
            ]);
        }

        public function trackVideo(Request $request, string $tenant, Lesson $lesson, LessonAccessService $accessService): JsonResponse
        {
            $data = $request->validate([
                'watched_seconds' => ['required', 'integer', 'min:0'],
                'duration_seconds' => ['nullable', 'integer', 'min:0'],
            ]);

            $enrollment = Enrollment::where('course_id', $lesson->courseModule->course_id)
                ->where('student_id', auth('tenant')->id())
                ->firstOrFail();

            // The player reports the video's real, actual duration — trust that
            // over admin-entered `video_duration_minutes`, which is an easy
            // field to leave blank or enter wrong. Always sync it (not just
            // when empty) so a wrong stored value gets corrected too.
            if (! empty($data['duration_seconds'])) {
                $realMinutes = (int) ceil($data['duration_seconds'] / 60);

                if ($lesson->video_duration_minutes !== $realMinutes) {
                    $lesson->update(['video_duration_minutes' => $realMinutes]);
                }
            }

            $progress = $accessService->updateVideoProgress(
                $enrollment,
                $lesson,
                $data['watched_seconds'],
                $data['duration_seconds'] ?? null,
            );

            return response()->json(['completed' => $progress->video_completed]);
        }

        public function markEbookRead(string $tenant, Lesson $lesson, LessonAccessService $accessService): JsonResponse
        {
            $enrollment = Enrollment::where('course_id', $lesson->courseModule->course_id)
                ->where('student_id', auth('tenant')->id())
                ->firstOrFail();

            $accessService->markEbookOpened($enrollment, $lesson);

            return response()->json(['status' => 'ok']);
        }
}
