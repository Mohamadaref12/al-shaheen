<?php

use App\Http\Controllers\Api\V1\ArticleController;
use Illuminate\Support\Facades\Route;

Route::prefix('articles')->group(function () {
    Route::get('/',        [ArticleController::class, 'index']);
    Route::get('/{slug}',  [ArticleController::class, 'show']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/',       [ArticleController::class, 'store']);
        Route::put('/{id}',    [ArticleController::class, 'update']);
        Route::delete('/{id}', [ArticleController::class, 'destroy']);
    });
});
