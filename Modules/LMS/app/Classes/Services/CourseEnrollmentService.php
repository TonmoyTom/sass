<?php

namespace Modules\LMS\Classes\Services;

use App\Events\NotificationSent;
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

        $enrollment = DB::transaction(function () use ($course, $student, $paymentMethod, $transactionId) {
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

            return Enrollment::create([
                'course_id' => $course->id,
                'student_id' => $student->id,
                'order_id' => $order->id,
                'status' => 'active',
                'enrolled_at' => now(),
            ]);
        });

        // notification kept outside the transaction on purpose — a
        // notify() failure (future email channel etc.) shouldn't be able
        // to roll back an otherwise-successful enrollment.
        $this->notifyAdminAndInstructors($course, $student, $course->is_free ? 0 : $course->price);

        return $enrollment;
    }

    /**
     * Course purchase/enrollment (free ba paid) hole, Super Admin + oi
     * course-er instructor-der notify koro.
     */
    protected function notifyAdminAndInstructors(Course $course, TenantUser $student, float $amount): void
    {
        $instructorIds = $course->instructors()->pluck('users.id')
            ->push($course->instructor_id)
            ->filter();

        $adminIds = TenantUser::role('Super Admin')->pluck('id');

        $recipientIds = $instructorIds->concat($adminIds)->unique();

        $recipients = TenantUser::whereIn('id', $recipientIds)->get();

        $message = $amount > 0
            ? "{$student->name} purchased \"{$course->title}\" for ৳".number_format($amount)
            : "{$student->name} enrolled in \"{$course->title}\" (free)";

        foreach ($recipients as $recipient) {
            NotificationSent::dispatch(
                $message,
                $recipient->id,
                'success',
                "/lms/courses/{$course->id}/edit",
                $student->id,
            );
        }
    }

    /**
     * Admin/Instructor manually enroll (free, offline payment).
     */
    public function manualEnroll(Course $course, TenantUser $student, ?string $note = null): Enrollment
    {
        return $this->purchase($course, $student, paymentMethod: 'manual');
    }
}
