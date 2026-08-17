<?php

namespace Modules\LMS\Models;

use App\Traits\Filterable;
use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    use Filterable;

    protected $fillable = [
        'title', 'description', 'time_limit_minutes', 'passing_score', 'max_attempts',
    ];

    public function questions()
    {
        return $this->hasMany(QuizQuestion::class, 'quiz_id')->orderBy('sort_order');
    }

    public function lessons()
    {
        return $this->belongsToMany(Lesson::class, 'lesson_quizzes', 'quiz_id', 'lesson_id');
    }

    public function attempts()
    {
        return $this->hasMany(QuizAttempt::class, 'quiz_id');
    }

    public function totalPoints(): int
    {
        return $this->questions()->sum('points');
    }
}
