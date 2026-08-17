<?php

namespace Modules\LMS\Classes\AI;

use App\Services\AI\Contracts\AiToolProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LmsToolProvider implements AiToolProvider
{
    public function moduleAlias(): string
    {
        return 'lms';
    }

    public function tools(): array
    {
        return [
            [
                'type' => 'function',
                'function' => [
                    'name' => 'get_mcq_count_by_course',
                    'description' => 'নির্দিষ্ট কোর্সে মোট কতগুলো MCQ প্রশ্ন আছে তা জানায়',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'course_title' => [
                                'type' => 'string',
                                'description' => 'কোর্সের নাম বা তার একাংশ',
                            ],
                        ],
                        'required' => ['course_title'],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'get_recent_quiz_uploads',
                    'description' => 'সাম্প্রতিক তৈরি হওয়া quiz/MCQ প্রশ্নগুলোর তালিকা ও তারিখ দেখায়',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'limit' => [
                                'type' => 'integer',
                                'description' => 'কয়টা দেখাবে, ডিফল্ট ৫',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'get_course_enrollment_stats',
                    'description' => 'একটা কোর্সে মোট কতজন এনরোল করেছে, কতজন সম্পন্ন করেছে তা দেখায়',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'course_title' => [
                                'type' => 'string',
                                'description' => 'কোর্সের নাম বা তার একাংশ',
                            ],
                        ],
                        'required' => ['course_title'],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'get_quiz_pass_rate',
                    'description' => 'একটা quiz-এ কতজন attempt দিয়েছে, কতজন pass করেছে, গড় স্কোর কত',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'quiz_title' => [
                                'type' => 'string',
                                'description' => 'quiz-এর নাম বা তার একাংশ',
                            ],
                        ],
                        'required' => ['quiz_title'],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'get_total_courses_and_students',
                    'description' => 'মোট কতগুলো কোর্স আছে এবং মোট কতজন ইউনিক শিক্ষার্থী এনরোল করেছে',
                    'parameters' => ['type' => 'object', 'properties' => []],
                ],
            ],
        ];
    }

    public function execute(string $toolName, array $args, string $tenantId): mixed
    {
        return match ($toolName) {
            'get_mcq_count_by_course' => $this->getMcqCountByCourse($args['course_title']),
            'get_recent_quiz_uploads' => $this->getRecentQuizUploads($args['limit'] ?? 5),
            'get_course_enrollment_stats' => $this->getCourseEnrollmentStats($args['course_title']),
            'get_quiz_pass_rate' => $this->getQuizPassRate($args['quiz_title']),
            'get_total_courses_and_students' => $this->getTotalCoursesAndStudents(),
            default => ['error' => 'Unknown LMS tool: '.$toolName],
        };
    }

    /**
     * courses -> course_modules -> lessons -> lesson_quizzes -> quizzes -> quiz_questions
     * চেইন ধরে course_title match করা quiz_questions গুনে দেয়।
     */
    protected function getMcqCountByCourse(string $courseTitle): array
    {
        $course = DB::table('courses')
            ->where('title', 'like', "%{$courseTitle}%")
            ->first(['id', 'title']);

        if (! $course) {
            return ['error' => "'{$courseTitle}' নামে কোনো কোর্স পাওয়া যায়নি"];
        }

        $count = DB::table('quiz_questions')
            ->join('quizzes', 'quizzes.id', '=', 'quiz_questions.quiz_id')
            ->join('lesson_quizzes', 'lesson_quizzes.quiz_id', '=', 'quizzes.id')
            ->join('lessons', 'lessons.id', '=', 'lesson_quizzes.lesson_id')
            ->join('course_modules', 'course_modules.id', '=', 'lessons.course_module_id')
            ->where('course_modules.course_id', $course->id)
            ->where('quiz_questions.type', 'mcq')
            ->count();

        return [
            'course' => $course->title,
            'mcq_count' => $count,
        ];
    }

    protected function getRecentQuizUploads(int $limit): array
    {
        $rows = DB::table('quiz_questions')
            ->join('quizzes', 'quizzes.id', '=', 'quiz_questions.quiz_id')
            ->select('quiz_questions.question_text', 'quiz_questions.type', 'quizzes.title as quiz_title', 'quiz_questions.created_at')
            ->orderByDesc('quiz_questions.created_at')
            ->limit($limit)
            ->get();

        return $rows->map(fn ($r) => [
            'question' => Str::limit($r->question_text, 80),
            'type' => $r->type,
            'quiz' => $r->quiz_title,
            'uploaded_at' => $r->created_at,
        ])->toArray();
    }

    protected function getCourseEnrollmentStats(string $courseTitle): array
    {
        $course = DB::table('courses')
            ->where('title', 'like', "%{$courseTitle}%")
            ->first(['id', 'title']);

        if (! $course) {
            return ['error' => "'{$courseTitle}' নামে কোনো কোর্স পাওয়া যায়নি"];
        }

        $total = DB::table('enrollments')->where('course_id', $course->id)->count();
        $completed = DB::table('enrollments')
            ->where('course_id', $course->id)
            ->where('status', 'completed')
            ->count();

        return [
            'course' => $course->title,
            'total_enrollments' => $total,
            'completed' => $completed,
            'completion_rate' => $total > 0 ? round(($completed / $total) * 100, 1).'%' : '0%',
        ];
    }

    protected function getQuizPassRate(string $quizTitle): array
    {
        $quiz = DB::table('quizzes')
            ->where('title', 'like', "%{$quizTitle}%")
            ->first(['id', 'title', 'passing_score']);

        if (! $quiz) {
            return ['error' => "'{$quizTitle}' নামে কোনো quiz পাওয়া যায়নি"];
        }

        $attempts = DB::table('quiz_attempts')->where('quiz_id', $quiz->id);
        $total = (clone $attempts)->count();
        $passed = (clone $attempts)->where('passed', true)->count();
        $avgScore = (clone $attempts)->whereNotNull('score')->avg('score');

        return [
            'quiz' => $quiz->title,
            'passing_score' => $quiz->passing_score,
            'total_attempts' => $total,
            'passed' => $passed,
            'pass_rate' => $total > 0 ? round(($passed / $total) * 100, 1).'%' : '0%',

            'average_score' => $avgScore ? round($avgScore, 1) : null,
        ];
    }

    protected function getTotalCoursesAndStudents(): array
    {
        return [
            'total_courses' => DB::table('courses')->count(),
            'published_courses' => DB::table('courses')->where('status', 'published')->count(),
            'unique_students_enrolled' => DB::table('enrollments')->distinct('student_id')->count('student_id'),
        ];
    }
}
