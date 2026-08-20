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


    public function assignments()
    {
        return $this->belongsToMany(Assignment::class, 'lesson_assignments');
    }

    /**
     * A directly usable playback URL — uploaded files resolve to the
     * tenant asset URL, YouTube links resolve to their embeddable form.
     */
    public function getResolvedVideoUrlAttribute(): ?string
    {
        if ($this->video_source === 'upload' && $this->video_path) {
            return $this->assetUrl($this->video_path);
        }

        if ($this->video_url) {
            return $this->toYoutubeEmbedUrl($this->video_url) ?? $this->video_url;
        }

        return null;
    }

    protected function assetUrl(string $path): string
    {
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $path = preg_replace('#^storage/#', '', $path);

        if (function_exists('tenant') && tenant()) {
            return url('/tenancy/assets/'.$path);
        }

        return asset('storage/'.$path);
    }

    protected function toYoutubeEmbedUrl(string $url): ?string
    {
        $pattern = '#(?:youtube\.com/(?:watch\?v=|embed/|shorts/)|youtu\.be/)([A-Za-z0-9_-]{11})#';

        if (preg_match($pattern, $url, $matches)) {
            return 'https://www.youtube.com/embed/'.$matches[1];
        }

        return null;
    }

    public function hasEbook(): bool
    {
        return ! empty($this->ebook_path);
    }

    public function getResolvedEbookUrlAttribute(): ?string
    {
        if ($this->ebook_path) {
            return $this->assetUrl($this->ebook_path);
        }

        return null;
    }

    public function hasQuiz(): bool
    {
        return $this->quizzes()->exists();
    }

    public function hasAssignment(): bool
    {
        return $this->assignments()->exists();
    }
}