<?php

namespace Modules\LMS\Models;

use Illuminate\Database\Eloquent\Model;

class LessonProgress extends Model
{
    protected $fillable = [
        'enrollment_id', 'lesson_id',
        'video_watched_seconds', 'video_completed',
        'ebook_opened_at', 'quiz_passed',
    ];

    protected $casts = [
        'video_completed' => 'boolean',
        'quiz_passed' => 'boolean',
        'ebook_opened_at' => 'datetime',
    ];

    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class, 'enrollment_id');
    }

    public function lesson()
    {
        return $this->belongsTo(Lesson::class, 'lesson_id');
    }

    /**
     * Ei lesson-er shob content (video/ebook/quiz) complete kina.
     */
    public function isFullyComplete(): bool
    {
        $lesson = $this->lesson;

        if ($lesson->hasVideo() && ! $this->video_completed) {
            return false;
        }

        if ($lesson->hasQuiz() && $this->quiz_passed !== true) {
            return false;
        }

        // ebook — "opened" thakleI complete dhori (mandatory read-through track kora jay na easily)
        if ($lesson->hasEbook() && ! $this->ebook_opened_at) {
            return false;
        }

        return true;
    }
}
