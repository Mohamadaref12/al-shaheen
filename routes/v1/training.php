<?php

use App\Http\Controllers\Api\V1\CourseCategoryController;
use App\Http\Controllers\Api\V1\TrainingController;
use App\Http\Controllers\Api\V1\TrainingCourseController;
use Illuminate\Support\Facades\Route;

Route::prefix('training')->group(function () {
    Route::get('filters', [TrainingCourseController::class, 'filters']);

    Route::get('categories', [CourseCategoryController::class, 'index']);
    Route::get('categories/{category}', [CourseCategoryController::class, 'show']);

    Route::get('courses', [TrainingCourseController::class, 'index']);
    Route::get('courses/{course}/lessons', [TrainingCourseController::class, 'lessons']);
    Route::get('courses/{course}/related', [TrainingCourseController::class, 'related']);
    Route::get('courses/{course}', [TrainingCourseController::class, 'show']);

    Route::get('lessons/{lessonId}', [TrainingCourseController::class, 'showLesson']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('my-courses', [TrainingController::class, 'myCourses']);
        Route::post('courses/{courseId}/enroll', [TrainingController::class, 'enroll']);
        Route::get('courses/{courseId}/my-progress', [TrainingController::class, 'myProgress']);
        Route::post('courses/{courseId}/progress/{lessonId}', [TrainingController::class, 'markProgress']);
    });
});
