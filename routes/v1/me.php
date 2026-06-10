<?php

use App\Http\Controllers\Api\V1\SavedArticleController;
use App\Http\Controllers\Api\V1\SocialController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('me/social', [SocialController::class, 'index']);
    Route::post('articles/{articleId}/save', [SavedArticleController::class, 'toggle']);
});
