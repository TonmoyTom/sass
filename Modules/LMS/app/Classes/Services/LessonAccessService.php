<?php

namespace Modules\LMS\Services;

use Modules\LMS\Models\Enrollment;
use Modules\LMS\Models\Lesson;
use Modules\LMS\Models\LessonProgress;

class LessonAccessService
{
    /**
     * Ei lesson-e student-er access ache kina (enrolled + unlocked).
     */
    public function canAccess(Lesson $lesson, ?Enrollment $enrollment): bool
    {
        // free preview — enrollment chara-o dekha jabe
        if ($lesson->is_free_preview) {
            return true;
        }

        // enroll na thakle, paid content-e access nai
        if (! $enrollment || $enrollment->status !== 'active') {
            return false;
        }

        return $this->isUnlocked($lesson, $enrollment);
    }

    /**
     * Sequential-lock logic — age discuss kora rule onujayi.
     */
    public function isUnlocked(Lesson $lesson, Enrollment $enrollment): bool
    {
        $previousLesson = Lesson::where('course_module_id', $lesson->course_module_id)
            ->where('sort_order', '<', $lesson->sort_order)
            ->orderByDesc('sort_order')
            ->first();

        // ei module-e first lesson — age-er module-er last lesson check koro
        if (! $previousLesson) {
            $previousModule = $lesson->courseModule()->first()
                ?->course
                ?->modules()
                ->where('sort_order', '<', $lesson->courseModule->sort_order)
                ->orderByDesc('sort_order')
                ->first();

            $previousLesson = $previousModule?->lessons()->orderByDesc('sort_order')->first();
        }

        // course-er ekdom first lesson — always unlocked
        if (! $previousLesson) {
            return true;
        }

        // previous lesson requires_completion FALSE hole — restriction nai
        if (! $previousLesson->requires_completion) {
            return true;
        }

        // previous lesson complete kina check koro
        $progress = LessonProgress::where('enrollment_id', $enrollment->id)
            ->where('lesson_id', $previousLesson->id)
            ->first();

        return $progress?->isFullyComplete() ?? false;
    }

    /**
     * Video progress update koro.
     */
    public function updateVideoProgress(Enrollment $enrollment, Lesson $lesson, int $watchedSeconds): LessonProgress
    {
        $progress = LessonProgress::firstOrCreate(
            ['enrollment_id' => $enrollment->id, 'lesson_id' => $lesson->id]
        );

        $isCompleted = $lesson->video_duration_minutes
            ? $watchedSeconds >= ($lesson->video_duration_minutes * 60 * 0.9) // 90% dekhle completed dhori
            : false;

        $progress->update([
            'video_watched_seconds' => max($progress->video_watched_seconds, $watchedSeconds),
            'video_completed' => $progress->video_completed || $isCompleted,
        ]);

        $this->checkCourseCompletion($enrollment);

        return $progress;
    }

    /**
     * Ebook "opened" mark koro.
     */
    public function markEbookOpened(Enrollment $enrollment, Lesson $lesson): LessonProgress
    {
        $progress = LessonProgress::firstOrCreate(
            ['enrollment_id' => $enrollment->id, 'lesson_id' => $lesson->id]
        );

        if (! $progress->ebook_opened_at) {
            $progress->update(['ebook_opened_at' => now()]);
        }

        $this->checkCourseCompletion($enrollment);

        return $progress;
    }

    /**
     * Quiz result-er upor progress update koro (QuizGradingService theke call hobe).
     */
    public function updateQuizProgress(Enrollment $enrollment, Lesson $lesson, bool $passed): LessonProgress
    {
        $progress = LessonProgress::firstOrCreate(
            ['enrollment_id' => $enrollment->id, 'lesson_id' => $lesson->id]
        );

        // ekbar pass korle, permanently passed thake (retry kore fail korleo)
        $progress->update([
            'quiz_passed' => $progress->quiz_passed === true ? true : $passed,
        ]);

        $this->checkCourseCompletion($enrollment);

        return $progress;
    }

    /**
     * Shob lesson complete hoyeche kina check kore, enrollment-ke 'completed' mark koro.
     */
    protected function checkCourseCompletion(Enrollment $enrollment): void
    {
        $totalLessons = $enrollment->course->lessons()->count();

        if ($totalLessons === 0) {
            return;
        }

        $completedCount = LessonProgress::where('enrollment_id', $enrollment->id)
            ->get()
            ->filter(fn ($p) => $p->isFullyComplete())
            ->count();

        if ($completedCount >= $totalLessons && $enrollment->status !== 'completed') {
            $enrollment->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);
        }
    }
}
