<?php

namespace Modules\LMS\Models;

use Illuminate\Database\Eloquent\Model;

class QuizQuestion extends Model
{
    protected $fillable = ['quiz_id', 'question_text', 'type', 'points', 'sort_order'];

    public function quiz()
    {
        return $this->belongsTo(Quiz::class, 'quiz_id');
    }

    public function options()
    {
        return $this->hasMany(QuizQuestionOption::class, 'quiz_question_id')->orderBy('sort_order');
    }

    public function correctOption()
    {
        return $this->hasOne(QuizQuestionOption::class, 'quiz_question_id')->where('is_correct', true);
    }
}
