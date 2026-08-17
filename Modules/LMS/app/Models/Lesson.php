<?php

namespace Modules\LMS\Models;

use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    protected $fillable = [
        'course_module_id', 'title', 'sort_order', 'is_free_preview', 'requires_completion',
        'video_source', 'video_url', 'video_path', 'video_duration_minutes',
        'ebook_path', 'ebook_title',
    ];

    protected $casts = [
        'is_free_preview' => 'boolean',
        'requires_completion' => 'boolean',
    ];

    public function courseModule()
    {
        return $this->belongsTo(CourseModule::class, 'course_module_id');
    }

    public function quizzes()
    {
        return $this->belongsToMany(Quiz::class, 'lesson_quizzes', 'lesson_id', 'quiz_id');
    }

    public function hasVideo(): bool
    {
        return ! empty($this->video_url) || ! empty($this->video_path);
    }

    public function hasEbook(): bool
    {
        return ! empty($this->ebook_path);
    }

    public function hasQuiz(): bool
    {
        return $this->quizzes()->exists();
    }
}
