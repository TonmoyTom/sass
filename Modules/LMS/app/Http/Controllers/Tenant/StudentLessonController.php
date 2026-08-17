<?php

namespace Modules\LMS\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\LMS\Models\Enrollment;
use Modules\LMS\Models\Lesson;
use Modules\LMS\Services\LessonAccessService;

class StudentLessonController extends Controller
{
    public function show(Lesson $lesson, LessonAccessService $accessService): Response
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

        return Inertia::render('LMS::Tenant/Learn/Lesson', [
            'lesson' => $lesson->load('quizzes'),
            'progress' => $progress,
            'course' => $course->load('modules.lessons'),
        ]);
    }

    public function trackVideo(Request $request, Lesson $lesson, LessonAccessService $accessService): JsonResponse
    {
        $data = $request->validate(['watched_seconds' => ['required', 'integer', 'min:0']]);

        $enrollment = Enrollment::where('course_id', $lesson->courseModule->course_id)
            ->where('student_id', auth('tenant')->id())
            ->firstOrFail();

        $progress = $accessService->updateVideoProgress($enrollment, $lesson, $data['watched_seconds']);

        return response()->json(['completed' => $progress->video_completed]);
    }

    public function markEbookRead(Lesson $lesson, LessonAccessService $accessService): JsonResponse
    {
        $enrollment = Enrollment::where('course_id', $lesson->courseModule->course_id)
            ->where('student_id', auth('tenant')->id())
            ->firstOrFail();

        $accessService->markEbookOpened($enrollment, $lesson);

        return response()->json(['status' => 'ok']);
    }
}
