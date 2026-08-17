<?php

namespace Modules\LMS\Models;

use Illuminate\Database\Eloquent\Model;

class QuizAttempt extends Model
{
    protected $fillable = [
        'quiz_id', 'student_id', 'attempt_number', 'score', 'passed',
        'status', 'started_at', 'submitted_at',
    ];

    protected $casts = [
        'passed' => 'boolean',
        'score' => 'decimal:2',
        'started_at' => 'datetime',
        'submitted_at' => 'datetime',
    ];

    public function quiz()
    {
        return $this->belongsTo(Quiz::class, 'quiz_id');
    }

    public function answers()
    {
        return $this->hasMany(QuizAnswer::class, 'quiz_attempt_id');
    }

    public function getStudentAttribute()
    {
        return \App\Models\TenantUser::find($this->student_id);
    }
}
