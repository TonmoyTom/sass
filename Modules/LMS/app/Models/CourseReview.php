<?php

namespace Modules\LMS\Models;

use App\Models\TenantUser;
use Illuminate\Database\Eloquent\Model;

class CourseReview extends Model
{
    protected $fillable = ['course_id', 'student_id', 'rating', 'comment'];

    protected $casts = [
        'rating' => 'integer',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function student()
    {
        return TenantUser::find($this->student_id);
    }
}