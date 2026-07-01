<?php

use App\Http\Controllers\Api\V1\NewsController;
use Illuminate\Support\Facades\Route;

Route::prefix('news')->group(function () {
    Route::get('/', [NewsController::class, 'index']);
    Route::get('/{newsId}/pdf', [NewsController::class, 'downloadPdf']);
    Route::get('/{newsId}', [NewsController::class, 'show']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/', [NewsController::class, 'store']);
        Route::put('/{newsId}', [NewsController::class, 'update']);
        Route::delete('/{newsId}', [NewsController::class, 'destroy']);
    });
});
