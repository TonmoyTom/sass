<?php

namespace Modules\LMS\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\LMS\Models\Enrollment;
use Modules\LMS\Models\Quiz;
use Modules\LMS\Models\QuizAttempt;
use Modules\LMS\Classes\Services\LessonAccessService;
use Modules\LMS\Classes\Services\QuizGradingService;
use Modules\LMS\Traits\InteractsWithStudent;

class StudentQuizController extends Controller
{
    use InteractsWithStudent;

    public function start(string $tenant, Quiz $quiz, QuizGradingService $grading): JsonResponse
    {
        try {
            $attempt = $grading->startAttempt($quiz, $this->currentStudent());
        } catch (\RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }

        return response()->json([
            'attempt_id' => $attempt->id,
            'questions' => $quiz->questions->map(fn ($q) => [
                'id' => $q->id,
                'question_text' => $q->question_text,
                'type' => $q->type,
                'points' => $q->points,
                // is_correct intentionally withheld until after submission
                'options' => $q->options->map(fn ($o) => [
                    'id' => $o->id,
                    'option_text' => $o->option_text,
                ]),
            ]),
        ]);
    }

    public function submit(Request $request, string $tenant, QuizAttempt $attempt, QuizGradingService $grading, LessonAccessService $accessService): JsonResponse
    {
        if ($attempt->student_id !== $this->studentId()) {
            abort(403);
        }

        $data = $request->validate([
            'answers' => ['required', 'array'],
            'answers.*.question_id' => ['required', 'integer'],
            'answers.*.selected_option_id' => ['nullable', 'integer'],
            'answers.*.answer_text' => ['nullable', 'string'],
        ]);

        $attempt = $grading->submitAttempt($attempt, $data['answers']);

        // QuizGradingService only grades the attempt — it doesn't know which
        // lesson(s) this quiz is attached to, so propagate pass/fail into
        // LessonProgress ourselves (this is what actually unlocks the next
        // lesson / completes the course).
        $quiz = $attempt->quiz()->with('lessons.courseModule')->first();

        foreach ($quiz->lessons as $lesson) {
            $enrollment = Enrollment::where('course_id', $lesson->courseModule->course_id)
                ->where('student_id', $this->studentId())
                ->first();

            if ($enrollment) {
                $accessService->updateQuizProgress($enrollment, $lesson, (bool) $attempt->passed);
            }
        }

        $attempt->load('answers.question.correctOption');

        return response()->json([
            'score' => $attempt->score,
            'passed' => $attempt->passed,
            'status' => $attempt->status,
            'answers' => $attempt->answers->map(fn ($a) => [
                'question_id' => $a->quiz_question_id,
                'selected_option_id' => $a->selected_option_id,
                'is_correct' => $a->is_correct,
                'correct_option_id' => $a->question?->correctOption?->id,
            ]),
        ]);
    }

    /**
     * Attempt history summary for a quiz (used to render "you've taken
     * this N times, best score X%" on the lesson page).
     */
    public function attempts(string $tenant, Quiz $quiz): JsonResponse
    {
        $attempts = QuizAttempt::where('quiz_id', $quiz->id)
            ->where('student_id', $this->studentId())
            ->orderByDesc('attempt_number')
            ->get();

        return response()->json([
            'attempts_used' => $attempts->count(),
            'max_attempts' => $quiz->max_attempts,
            'best_score' => $attempts->max('score'),
            'passed' => $attempts->contains('passed', true),
            'can_attempt' => $quiz->max_attempts <= 0 || $attempts->count() < $quiz->max_attempts,
        ]);
    }
}