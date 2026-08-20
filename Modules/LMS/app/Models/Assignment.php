<?php

namespace Modules\LMS\Models;

use App\Traits\Filterable;
use Illuminate\Database\Eloquent\Model;

class Assignment extends Model
{
    use Filterable;

    protected $fillable = ['course_id', 'title', 'instructions', 'file_path', 'file_name', 'due_date', 'max_score', 'allow_late_submission'];

    protected $appends = ['file_url'];

    protected $casts = [
        'due_date' => 'datetime',
        'allow_late_submission' => 'boolean',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function lessons()
    {
        return $this->belongsToMany(Lesson::class, 'lesson_assignments');
    }

    public function submissions()
    {
        return $this->hasMany(AssignmentSubmission::class);
    }

    public function isPastDue(): bool
    {
        return $this->due_date !== null && $this->due_date->isPast();
    }

    public function getFileUrlAttribute(): ?string
    {
        if (! $this->file_path) {
            return null;
        }

        if (str_starts_with($this->file_path, 'http://') || str_starts_with($this->file_path, 'https://')) {
            return $this->file_path;
        }

        $path = preg_replace('#^storage/#', '', $this->file_path);

        if (function_exists('tenant') && tenant()) {
            return url('/tenancy/assets/'.$path);
        }

        return asset('storage/'.$path);
    }
}