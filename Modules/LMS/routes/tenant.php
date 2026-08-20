<?php

use Illuminate\Support\Facades\Route;
use Modules\LMS\Http\Controllers\Tenant\CourseCategoryController;
use Modules\LMS\Http\Controllers\Tenant\CourseController;
use Modules\LMS\Http\Controllers\Tenant\CourseModuleController;
use Modules\LMS\Http\Controllers\Tenant\CourseSubcategoryController;
use Modules\LMS\Http\Controllers\Tenant\EnrollmentController;
use Modules\LMS\Http\Controllers\Tenant\LessonController;
use Modules\LMS\Http\Controllers\Tenant\QuizController;
use Modules\LMS\Http\Controllers\Tenant\StudentCourseController;
use Modules\LMS\Http\Controllers\Tenant\StudentLessonController;
use Modules\LMS\Http\Controllers\Tenant\InstructorController;
use Modules\LMS\Http\Controllers\Tenant\CourseFaqController;
use Modules\LMS\Http\Controllers\Tenant\StudentQuizController;
use Modules\LMS\Http\Controllers\Tenant\StudentReviewController;
use Modules\LMS\Http\Controllers\Tenant\StudentNoteController;
use Modules\LMS\Http\Controllers\Tenant\StudentOrderController;
use Modules\LMS\Http\Controllers\Tenant\AssignmentController;
use Modules\LMS\Http\Controllers\Tenant\AssignmentSubmissionController;
use Modules\LMS\Http\Controllers\Tenant\StudentAssignmentController;
use Modules\LMS\Http\Controllers\Tenant\StudentLeaderboardController;
use Modules\LMS\Http\Controllers\Tenant\StudentCertificateController;
use Modules\LMS\Http\Controllers\Tenant\CertificateVerificationController;

Route::prefix('lms')->name('tenant.lms.')->group(function () {

    Route::get('/verify-certificate/{certificateNumber?}', [CertificateVerificationController::class, 'show'])->name('verify-certificate');
    Route::get('/dashboard', function () {
        return response()->json([
            'user' => auth()->user(),
            'session_id' => session()->getId(),
        ]);
    });

    Route::prefix('learn')->name('learn.')->group(function () {
        Route::get('/', [StudentCourseController::class, 'landing'])->name('index');
        Route::get('/browse', [StudentCourseController::class, 'browse'])->name('browse');
        Route::get('/browse/{course}', [StudentCourseController::class, 'show'])->name('browse.show');
    });

    Route::middleware(['auth:tenant'])->group(function () {
        Route::resource('categories', CourseCategoryController::class)->except(['show'])->names('categories');
        Route::resource('subcategories', CourseSubcategoryController::class)->except(['show'])->names('subcategories');
        Route::post('/courses/{course}/enroll', [EnrollmentController::class, 'store'])->name('courses.enroll');
        Route::resource('quizzes', QuizController::class)->except(['show', 'destroy'])->names('quizzes');
        Route::get('/quizzes-search', [QuizController::class, 'search'])->name('quizzes.search');

        Route::resource('courses', CourseController::class)->except(['show'])->names('courses');
        Route::post('/courses/upload-video', [CourseController::class, 'uploadVideo'])->name('courses.upload-video');

        Route::resource('instructors', InstructorController::class)->except(['show', 'create', 'edit'])->names('instructors');
         Route::get('/courses-instructors-search', [CourseController::class, 'searchInstructors'])->name('courses.instructors-search');
        Route::post('/courses/{course}/modules', [CourseModuleController::class, 'store'])->name('modules.store');
        Route::put('/modules/{module}', [CourseModuleController::class, 'update'])->name('modules.update');
        Route::post('/courses/{course}/modules/reorder', [CourseModuleController::class, 'reorder'])->name('modules.reorder');
        Route::delete('/modules/{module}', [CourseModuleController::class, 'destroy'])->name('modules.destroy');
        Route::post('/courses/{course}/faqs', [CourseFaqController::class, 'store'])->name('faqs.store');
        Route::put('/faqs/{faq}', [CourseFaqController::class, 'update'])->name('faqs.update');
        Route::post('/courses/{course}/faqs/reorder', [CourseFaqController::class, 'reorder'])->name('faqs.reorder');
        Route::delete('/faqs/{faq}', [CourseFaqController::class, 'destroy'])->name('faqs.destroy');
        Route::post('/modules/{module}/lessons', [LessonController::class, 'store'])->name('lessons.store');
        Route::put('/lessons/{lesson}', [LessonController::class, 'update'])->name('lessons.update');
        Route::post('/modules/{module}/lessons/reorder', [LessonController::class, 'reorder'])->name('lessons.reorder');
        Route::post('/lessons/upload-video', [LessonController::class, 'uploadVideo'])->name('lessons.upload-video');
        Route::delete('/lessons/{lesson}', [LessonController::class, 'destroy'])->name('lessons.destroy');
        Route::post('/lessons/{lesson}/quiz', [LessonController::class, 'attachQuiz'])->name('lessons.attach-quiz');
        Route::delete('/lessons/{lesson}/quiz/{quizId}', [LessonController::class, 'detachQuiz'])->name('lessons.detach-quiz');



        Route::get('/learn/{lesson}', [StudentLessonController::class, 'show'])->name('learn.show');
        Route::post('/learn/{lesson}/track-video', [StudentLessonController::class, 'trackVideo'])->name('learn.track-video');
        Route::post('/learn/{lesson}/mark-ebook-read', [StudentLessonController::class, 'markEbookRead'])->name('learn.mark-ebook-read');
        Route::post('/lessons/{lesson}/assignment', [LessonController::class, 'attachAssignment'])->name('lessons.attach-assignment');
        Route::delete('/lessons/{lesson}/assignment/{assignmentId}', [LessonController::class, 'detachAssignment'])->name('lessons.detach-assignment');


        Route::post('/learn/{lesson}/notes', [StudentNoteController::class, 'save'])->name('learn.notes.save');
        Route::post('/quizzes/{quiz}/start', [StudentQuizController::class, 'start'])->name('quizzes.start');
        Route::post('/quiz-attempts/{attempt}/submit', [StudentQuizController::class, 'submit'])->name('quiz-attempts.submit');
        Route::get('/quizzes/{quiz}/attempts', [StudentQuizController::class, 'attempts'])->name('quizzes.attempts');

        Route::post('/courses/{course}/reviews', [StudentReviewController::class, 'store'])->name('reviews.store');
        Route::delete('/courses/{course}/reviews', [StudentReviewController::class, 'destroy'])->name('reviews.destroy');
        Route::post('/browse/{course}/enroll', [StudentCourseController::class, 'enroll'])->name('browse.enroll');
        Route::get('/my-courses', [EnrollmentController::class, 'index'])->name('my-courses.index');
        Route::get('/my-courses/{course}', [StudentCourseController::class, 'myCourseShow'])->name('my-courses.show');

        Route::get('/my-orders', [StudentOrderController::class, 'index'])->name('my-orders.index');
        Route::get('/my-orders/{order}/invoice', [StudentOrderController::class, 'invoice'])->name('my-orders.invoice');

        Route::get('/assignments', [AssignmentController::class, 'index'])->name('assignments.index');
        Route::get('/assignments-search', [AssignmentController::class, 'search'])->name('assignments.search');
        Route::post('/courses/{course}/assignments', [AssignmentController::class, 'store'])->name('assignments.store');
        Route::put('/assignments/{assignment}', [AssignmentController::class, 'update'])->name('assignments.update');
        Route::delete('/assignments/{assignment}', [AssignmentController::class, 'destroy'])->name('assignments.destroy');
        Route::get('/assignments/{assignment}/submissions', [AssignmentSubmissionController::class, 'index'])->name('assignments.submissions');
        Route::put('/assignment-submissions/{submission}/grade', [AssignmentSubmissionController::class, 'grade'])->name('assignment-submissions.grade');
        Route::post('/assignments/{assignment}/submit', [StudentAssignmentController::class, 'submit'])->name('assignments.submit');
        Route::get('/courses/{course}/leaderboard', [StudentLeaderboardController::class, 'show'])->name('leaderboard.show');
        Route::get('/courses/{course}/leaderboard.json', [StudentLeaderboardController::class, 'json'])->name('leaderboard.json');
         Route::get('/courses/{course}/certificate', [StudentCertificateController::class, 'download'])->name('certificate.download');
    });
});
