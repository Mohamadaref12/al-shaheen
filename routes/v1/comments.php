<?php

use App\Http\Controllers\Api\V1\CommentController;
use Illuminate\Support\Facades\Route;

Route::get('articles/{article}/comments',  [CommentController::class, 'index']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('articles/{article}/comments', [CommentController::class, 'store']);
    Route::delete('comments/{id}',             [CommentController::class, 'destroy']);
});
