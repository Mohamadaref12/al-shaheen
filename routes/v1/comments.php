<?php

use App\Http\Controllers\Api\V1\CommentController;
use Illuminate\Support\Facades\Route;

Route::get('articles/{articleId}/comments',  [CommentController::class, 'index']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('articles/{articleId}/comments', [CommentController::class, 'store']);
    Route::delete('comments/{commentId}',        [CommentController::class, 'destroy']);
});
