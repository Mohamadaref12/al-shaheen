<?php

use App\Http\Controllers\Api\V1\WriterController;
use Illuminate\Support\Facades\Route;

Route::prefix('writers')->group(function () {
    Route::get('/',             [WriterController::class, 'index']);
    Route::get('/{writerId}',   [WriterController::class, 'show']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::put('/profile', [WriterController::class, 'updateProfile']);
    });
});
