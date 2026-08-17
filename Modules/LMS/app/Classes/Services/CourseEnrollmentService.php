<?php

namespace Modules\LMS\Services;

use App\Models\TenantUser;
use Illuminate\Support\Facades\DB;
use Modules\LMS\Models\Course;
use Modules\LMS\Models\CourseOrder;
use Modules\LMS\Models\Enrollment;

class CourseEnrollmentService
{
    /**
     * Course purchase + enroll — ek transaction-e.
     */
    public function purchase(
        Course $course,
        TenantUser $student,
        ?string $paymentMethod = null,
        ?string $transactionId = null,
    ): Enrollment {
        $existing = Enrollment::where('course_id', $course->id)
            ->where('student_id', $student->id)
            ->first();

        if ($existing) {
            throw new \RuntimeException('Already enrolled in this course.');
        }

        return DB::transaction(function () use ($course, $student, $paymentMethod, $transactionId) {
            $amount = $course->is_free ? 0 : $course->price;

            $order = CourseOrder::create([
                'course_id' => $course->id,
                'student_id' => $student->id,
                'amount' => $amount,
                'status' => 'completed',
                'payment_method' => $course->is_free ? 'free' : $paymentMethod,
                'transaction_id' => $transactionId,
                'purchased_at' => now(),
            ]);

            $enrollment = Enrollment::create([
                'course_id' => $course->id,
                'student_id' => $student->id,
                'order_id' => $order->id,
                'status' => 'active',
                'enrolled_at' => now(),
            ]);

            return $enrollment;
        });
    }

    /**
     * Admin/Instructor manually enroll (free, offline payment).
     */
    public function manualEnroll(Course $course, TenantUser $student, ?string $note = null): Enrollment
    {
        return $this->purchase($course, $student, paymentMethod: 'manual');
    }
}
