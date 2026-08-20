<?php

namespace Modules\LMS\Models;

use App\Models\TenantUser;
use Illuminate\Database\Eloquent\Model;

class AssignmentSubmission extends Model
{
    protected $fillable = [
        'assignment_id', 'student_id', 'submitted_text', 'file_path', 'file_name',
        'submitted_at', 'is_late', 'grade', 'feedback', 'graded_at', 'graded_by',
    ];

    protected $appends = ['file_url'];

    protected $casts = [
        'submitted_at' => 'datetime',
        'graded_at' => 'datetime',
        'is_late' => 'boolean',
    ];

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

    public function assignment()
    {
        return $this->belongsTo(Assignment::class);
    }

    public function student()
    {
        return TenantUser::find($this->student_id);
    }

    public function isGraded(): bool
    {
        return $this->grade !== null;
    }
}