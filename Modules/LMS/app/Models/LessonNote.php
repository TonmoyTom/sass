<?php

namespace Modules\LMS\Models;

use Illuminate\Database\Eloquent\Model;

class LessonNote extends Model
{
    protected $fillable = ['lesson_id', 'student_id', 'content'];

    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }
}