<?php

namespace Modules\LMS\Traits;

use Modules\LMS\Models\Enrollment;

/**
 * Common "who is the current student / what have they enrolled in" checks,
 * shared across every Tenant-side LMS controller so it isn't re-typed
 * (auth('tenant')->id() etc.) in every single method.
 */
trait InteractsWithStudent
{
    protected function studentId(): ?int
    {
        return auth('tenant')->id();
    }

    protected function currentStudent()
    {
        return auth('tenant')->user();
    }

    protected function isStudentLoggedIn(): bool
    {
        return (bool) $this->studentId();
    }

    /**
     * Course IDs the current student is already enrolled in.
     * Empty array (not a query) when nobody's logged in.
     */
    protected function enrolledCourseIds(): array
    {
        $studentId = $this->studentId();

        if (! $studentId) {
            return [];
        }

        return Enrollment::where('student_id', $studentId)
            ->pluck('course_id')
            ->toArray();
    }

    protected function isEnrolledIn(int $courseId): bool
    {
        return in_array($courseId, $this->enrolledCourseIds(), true);
    }
}