<?php

namespace Modules\LMS\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\LMS\Models\Quiz;

class QuizController extends Controller
{
    public function index(Request $request): Response
    {
        $quizzes = Quiz::query()
            ->withCount('questions')
            ->filterAndCache(
                $request,
                searchable: ['title'],
                filterable: [],
                sortable: ['title', 'created_at'],
                ttlSeconds: 180,
                perPage: 20,
                transform: fn ($q) => [
                    'id' => $q->id,
                    'title' => $q->title,
                    'questions_count' => $q->questions_count,
                    'passing_score' => $q->passing_score,
                    'max_attempts' => $q->max_attempts,
                    'time_limit_minutes' => $q->time_limit_minutes,
                ]
            );

        return Inertia::render('LMS::Tenant/Quizzes/Index', [
            'quizzes' => $quizzes,
            'filters' => [
                'search' => $request->input('search', ''),
                'sort_by' => $request->input('sort_by', 'created_at'),
                'sort_dir' => $request->input('sort_dir', 'desc'),
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('LMS::Tenant/Quizzes/Builder', [
            'quiz' => null,
        ]);
    }

    public function edit(string $tenant, Quiz $quiz ): Response
    {
        $quiz->load('questions.options');

        return Inertia::render('LMS::Tenant/Quizzes/Builder', [
            'quiz' => $quiz,
        ]);
    }

    public function store(Request $request, ): RedirectResponse
    {
        $data = $this->validateQuiz($request);

        $quiz = Quiz::create([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'time_limit_minutes' => $data['time_limit_minutes'] ?? null,
            'passing_score' => $data['passing_score'],
            'max_attempts' => $data['max_attempts'],
        ]);

        $this->syncQuestions($quiz, $data['questions']);

        return redirect('/lms/quizzes/')->with('status', 'Quiz created.');
    }

    public function update(Request $request, string $tenant, Quiz $quiz): RedirectResponse
    {
        $data = $this->validateQuiz($request);

        $quiz->update([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'time_limit_minutes' => $data['time_limit_minutes'] ?? null,
            'passing_score' => $data['passing_score'],
            'max_attempts' => $data['max_attempts'],
        ]);

        // purano question/option shob muche, notun kore save (simple approach)
        $quiz->questions()->delete(); // cascade — options-o delete hobe

        $this->syncQuestions($quiz, $data['questions']);

        return redirect()->route('tenant.lms.quizzes.index')->with('status', 'Quiz updated.');
    }

    protected function syncQuestions(Quiz $quiz, array $questions): void
    {
        foreach ($questions as $qIndex => $questionData) {
            $question = $quiz->questions()->create([
                'question_text' => $questionData['question_text'],
                'type' => $questionData['type'],
                'points' => $questionData['points'],
                'sort_order' => $qIndex,
            ]);

            if (! empty($questionData['options'])) {
                foreach ($questionData['options'] as $oIndex => $optionData) {
                    $question->options()->create([
                        'option_text' => $optionData['option_text'],
                        'is_correct' => $optionData['is_correct'] ?? false,
                        'sort_order' => $oIndex,
                    ]);
                }
            }
        }
    }

    public function search(Request $request): \Illuminate\Http\JsonResponse
{
    $quizzes = Quiz::withCount('questions')
        ->when($request->q, fn ($q) => $q->where('title', 'like', "%{$request->q}%"))
        ->orderBy('title')
        ->limit(20)
        ->get(['id', 'title']);

    return response()->json($quizzes);
}

    protected function validateQuiz(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'time_limit_minutes' => ['nullable', 'integer', 'min:1'],
            'passing_score' => ['required', 'integer', 'min:0', 'max:100'],
            'max_attempts' => ['required', 'integer', 'min:0'],
            'questions' => ['required', 'array', 'min:1'],
            'questions.*.question_text' => ['required', 'string'],
            'questions.*.type' => ['required', 'in:mcq,true_false,short_answer'],
            'questions.*.points' => ['required', 'integer', 'min:1'],
            'questions.*.options' => ['required_if:questions.*.type,mcq,true_false', 'array'],
            'questions.*.options.*.option_text' => ['required', 'string'],
            'questions.*.options.*.is_correct' => ['boolean'],
        ]);
    }
}
