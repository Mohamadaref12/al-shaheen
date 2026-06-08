<?php

use App\Http\Controllers\Api\V1\WriterController;
use App\Http\Controllers\Api\V1\WriterDashboardController;
use Illuminate\Support\Facades\Route;

Route::prefix('writers')->group(function () {
    Route::get('/', [WriterController::class, 'index']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me/overview',                        [WriterDashboardController::class, 'overview']);
        Route::get('/me/articles',                        [WriterDashboardController::class, 'articles']);
        Route::get('/me/articles/{articleId}/preview',    [WriterDashboardController::class, 'preview']);
        Route::get('/me/drafts',                          [WriterDashboardController::class, 'drafts']);
        Route::get('/me/analytics',                       [WriterDashboardController::class, 'analytics']);
        Route::put('/profile',                            [WriterController::class, 'updateProfile']);
    });

    Route::get('/{writerId}', [WriterController::class, 'show']);
});
