<?php

namespace Modules\LMS\Models;

use Illuminate\Database\Eloquent\Model;

class QuizQuestionOption extends Model
{
    protected $fillable = ['quiz_question_id', 'option_text', 'is_correct', 'sort_order'];

    protected $casts = ['is_correct' => 'boolean'];

    public function question()
    {
        return $this->belongsTo(QuizQuestion::class, 'quiz_question_id');
    }
}
