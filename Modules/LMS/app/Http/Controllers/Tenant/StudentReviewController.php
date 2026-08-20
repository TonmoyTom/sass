<?php

namespace Modules\LMS\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\LMS\Models\Course;
use Modules\LMS\Models\Enrollment;
use Modules\LMS\Traits\InteractsWithStudent;

class StudentReviewController extends Controller
{
    use InteractsWithStudent;

    public function store(Request $request, string $tenant, Course $course): RedirectResponse
    {
        $enrolled = Enrollment::where('course_id', $course->id)
            ->where('student_id', $this->studentId())
            ->exists();

        if (! $enrolled) {
            return back()->with('error', 'Only enrolled students can leave a review.');
        }

        $data = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        $course->reviews()->updateOrCreate(
            ['student_id' => $this->studentId()],
            ['rating' => $data['rating'], 'comment' => $data['comment'] ?? null],
        );

        return back()->with('status', 'Thanks for your review!');
    }

    public function destroy(string $tenant, Course $course): RedirectResponse
    {
        $course->reviews()->where('student_id', $this->studentId())->delete();

        return back()->with('status', 'Review removed.');
    }
}