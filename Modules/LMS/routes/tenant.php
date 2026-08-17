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

Route::prefix('lms')->name('tenant.lms.')->group(function () {
    Route::get('/dashboard', function () {
        return response()->json([
            'user' => auth()->user(),
            'session_id' => session()->getId(),
        ]);
    });

    Route::prefix('learn')->name('learn.')->group(function () {
        Route::get('/browse', [StudentCourseController::class, 'browse'])->name('browse');
        Route::get('/browse/{course}', [StudentCourseController::class, 'show'])->name('browse.show');

        Route::middleware(['auth:tenant'])->group(function () {
            Route::post('/browse/{course}/enroll', [StudentCourseController::class, 'enroll'])->name('browse.enroll');
            Route::get('/my-courses', [StudentCourseController::class, 'myCourses'])->name('my-courses.index');
        });
    });

    Route::middleware(['auth:tenant'])->group(function () {
        Route::resource('categories', CourseCategoryController::class)->except(['show'])->names('categories');
        Route::resource('subcategories', CourseSubcategoryController::class)->except(['show'])->names('subcategories');
        Route::get('/my-courses', [EnrollmentController::class, 'index'])->name('my-courses.index');
        Route::post('/courses/{course}/enroll', [EnrollmentController::class, 'store'])->name('courses.enroll');
        Route::resource('quizzes', QuizController::class)->except(['show', 'destroy'])->names('quizzes');
        Route::get('/quizzes-search', [QuizController::class, 'search'])->name('quizzes.search');

        Route::resource('courses', CourseController::class)->except(['show'])->names('courses');

        Route::post('/courses/{course}/modules', [CourseModuleController::class, 'store'])->name('modules.store');
        Route::put('/modules/{module}', [CourseModuleController::class, 'update'])->name('modules.update');
        Route::post('/courses/{course}/modules/reorder', [CourseModuleController::class, 'reorder'])->name('modules.reorder');
        Route::delete('/modules/{module}', [CourseModuleController::class, 'destroy'])->name('modules.destroy');

        Route::post('/modules/{module}/lessons', [LessonController::class, 'store'])->name('lessons.store');
        Route::put('/lessons/{lesson}', [LessonController::class, 'update'])->name('lessons.update');
        Route::post('/modules/{module}/lessons/reorder', [LessonController::class, 'reorder'])->name('lessons.reorder');
        Route::post('/lessons/upload-video', [LessonController::class, 'uploadVideo'])->name('lessons.upload-video');
        Route::delete('/lessons/{lesson}', [LessonController::class, 'destroy'])->name('lessons.destroy');
        Route::post('/lessons/{lesson}/quiz', [LessonController::class, 'attachQuiz'])->name('lessons.attach-quiz');
        Route::delete('/lessons/{lesson}/quiz/{quizId}', [LessonController::class, 'detachQuiz'])->name('lessons.detach-quiz');

        Route::get('/my-courses', [EnrollmentController::class, 'index'])->name('my-courses.index');
        Route::post('/courses/{course}/enroll', [EnrollmentController::class, 'store'])->name('courses.enroll');

        Route::get('/learn/{lesson}', [StudentLessonController::class, 'show'])->name('learn.show');
        Route::post('/learn/{lesson}/track-video', [StudentLessonController::class, 'trackVideo'])->name('learn.track-video');
        Route::post('/learn/{lesson}/mark-ebook-read', [StudentLessonController::class, 'markEbookRead'])->name('learn.mark-ebook-read');

        Route::post('/browse/{course}/enroll', [StudentCourseController::class, 'enroll'])->name('browse.enroll');
        Route::get('/my-courses', [StudentCourseController::class, 'myCourses'])->name('my-courses.index');
    });
});
