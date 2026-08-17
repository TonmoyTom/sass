<?php

namespace Modules\LMS\Services;

use App\Models\TenantUser;
use Illuminate\Support\Facades\DB;
use Modules\LMS\Models\Quiz;
use Modules\LMS\Models\QuizAttempt;

class QuizGradingService
{
    /**
     * Notun attempt shuru koro.
     */
    public function startAttempt(Quiz $quiz, TenantUser $student): QuizAttempt
    {
        $existingAttempts = QuizAttempt::where('quiz_id', $quiz->id)
            ->where('student_id', $student->id)
            ->count();

        if ($quiz->max_attempts > 0 && $existingAttempts >= $quiz->max_attempts) {
            throw new \RuntimeException('Maximum attempts reached for this quiz.');
        }

        return QuizAttempt::create([
            'quiz_id' => $quiz->id,
            'student_id' => $student->id,
            'attempt_number' => $existingAttempts + 1,
            'status' => 'in_progress',
            'started_at' => now(),
        ]);
    }

    /**
     * Answer submit + auto-grade.
     *
     * @param  array  $answers  [['question_id' => 1, 'selected_option_id' => 3], ...]
     */
    public function submitAttempt(QuizAttempt $attempt, array $answers): QuizAttempt
    {
        return DB::transaction(function () use ($attempt, $answers) {
            $quiz = $attempt->quiz()->with('questions.options')->first();
            $totalPoints = $quiz->totalPoints();
            $earnedPoints = 0;

            foreach ($answers as $ans) {
                $question = $quiz->questions->firstWhere('id', $ans['question_id']);

                if (! $question) {
                    continue;
                }

                $isCorrect = null;

                if (in_array($question->type, ['mcq', 'true_false'])) {
                    $selectedOption = $question->options->firstWhere('id', $ans['selected_option_id'] ?? null);
                    $isCorrect = $selectedOption?->is_correct ?? false;

                    if ($isCorrect) {
                        $earnedPoints += $question->points;
                    }
                }

                $attempt->answers()->create([
                    'quiz_question_id' => $question->id,
                    'selected_option_id' => $ans['selected_option_id'] ?? null,
                    'answer_text' => $ans['answer_text'] ?? null,
                    'is_correct' => $isCorrect,
                ]);
            }

            $scorePercentage = $totalPoints > 0
                ? round(($earnedPoints / $totalPoints) * 100, 2)
                : 0;

            $hasShortAnswer = $quiz->questions->contains('type', 'short_answer');

            $attempt->update([
                'score' => $scorePercentage,
                'passed' => $scorePercentage >= $quiz->passing_score,
                'status' => $hasShortAnswer ? 'submitted' : 'graded', // short_answer thakle manual grading baki
                'submitted_at' => now(),
            ]);

            return $attempt->fresh();
        });
    }

    /**
     * Instructor manually short_answer grade kore.
     */
    public function gradeShortAnswer(QuizAttempt $attempt, int $answerId, bool $isCorrect): void
    {
        $answer = $attempt->answers()->findOrFail($answerId);
        $answer->update(['is_correct' => $isCorrect]);

        // recalculate total score
        $quiz = $attempt->quiz()->with('questions')->first();
        $totalPoints = $quiz->totalPoints();

        $earnedPoints = $attempt->answers()
            ->where('is_correct', true)
            ->join('quiz_questions', 'quiz_answers.quiz_question_id', '=', 'quiz_questions.id')
            ->sum('quiz_questions.points');

        $scorePercentage = $totalPoints > 0 ? round(($earnedPoints / $totalPoints) * 100, 2) : 0;

        $attempt->update([
            'score' => $scorePercentage,
            'passed' => $scorePercentage >= $quiz->passing_score,
            'status' => 'graded',
        ]);
    }
}
