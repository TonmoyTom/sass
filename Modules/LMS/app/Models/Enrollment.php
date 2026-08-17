<?php

namespace Modules\LMS\Models;

use App\Models\TenantUser;
use Illuminate\Database\Eloquent\Model;

class Enrollment extends Model
{
    protected $fillable = [
        'course_id', 'student_id', 'order_id', 'status', 'enrolled_at', 'completed_at',
    ];

    protected $casts = [
        'enrolled_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function order()
    {
        return $this->belongsTo(CourseOrder::class, 'order_id');
    }

    public function progress()
    {
        return $this->hasMany(LessonProgress::class, 'enrollment_id');
    }

    public function getStudentAttribute()
    {
        return TenantUser::find($this->student_id);
    }

    /**
     * Overall course completion percentage.
     */
    public function progressPercentage(): int
    {
        $totalLessons = $this->course->lessons()->count();

        if ($totalLessons === 0) {
            return 0;
        }

        $completedLessons = $this->progress()
            ->where(function ($q) {
                $q->where('video_completed', true)
                    ->orWhereNotNull('ebook_opened_at');
            })
            ->count();

        return (int) round(($completedLessons / $totalLessons) * 100);
    }

    public function progressSummary(): array
    {
        $totalLessons = $this->course->lessons()->count();
        $progressRecords = $this->progress()->get();

        $completedLessons = $progressRecords->filter(fn ($p) => $p->isFullyComplete())->count();

        $totalQuizzes = $this->course->lessons()->get()->filter(fn ($l) => $l->hasQuiz())->count();
        $passedQuizzes = $progressRecords->where('quiz_passed', true)->count();

        $totalVideos = $this->course->lessons()->get()->filter(fn ($l) => $l->hasVideo())->count();
        $completedVideos = $progressRecords->where('video_completed', true)->count();

        return [
            'overall_percentage' => $totalLessons > 0 ? (int) round(($completedLessons / $totalLessons) * 100) : 0,
            'lessons_completed' => $completedLessons,
            'lessons_total' => $totalLessons,
            'quizzes_passed' => $passedQuizzes,
            'quizzes_total' => $totalQuizzes,
            'videos_completed' => $completedVideos,
            'videos_total' => $totalVideos,
        ];
    }
}
