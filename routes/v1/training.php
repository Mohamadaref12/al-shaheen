<?php

use App\Http\Controllers\Api\V1\TrainingController;
use Illuminate\Support\Facades\Route;

Route::prefix('training')->group(function () {
    Route::get('courses',         [TrainingController::class, 'index']);
    Route::get('courses/{slug}',  [TrainingController::class, 'show']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('courses/{course}/progress/{lesson}', [TrainingController::class, 'markProgress']);
    });
});
