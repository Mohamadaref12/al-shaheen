<?php

use App\Http\Controllers\Api\V1\ArticleAiSuggestionController;
use App\Http\Controllers\Api\V1\ArticleController;
use Illuminate\Support\Facades\Route;

Route::prefix('articles')->group(function () {
    Route::get('/',                              [ArticleController::class, 'index']);
    Route::get('/{articleId}/pdf',               [ArticleController::class, 'downloadPdf']);
    Route::get('/{articleId}/featured-image/download', [ArticleController::class, 'downloadFeaturedImage']);
    Route::get('/{articleId}/featured-image',    [ArticleController::class, 'downloadFeaturedImage']);
    Route::get('/{articleId}/related',           [ArticleController::class, 'relatedStories']);
    Route::get('/{articleId}/trending-topics',   [ArticleController::class, 'trendingTopics']);
    Route::get('/{articleId}/next-read',         [ArticleController::class, 'nextRead']);
    Route::get('/{articleId}',                   [ArticleController::class, 'show']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('ai/status',                    [ArticleAiSuggestionController::class, 'status']);
        Route::post('ai/suggest',                  [ArticleAiSuggestionController::class, 'suggestFromDraft']);
        Route::post('{articleId}/ai/suggest',      [ArticleAiSuggestionController::class, 'suggestForArticle']);
        Route::get('{articleId}/ai/suggestions',   [ArticleAiSuggestionController::class, 'index']);
        Route::post('/',               [ArticleController::class, 'store']);
        Route::put('/{articleId}',     [ArticleController::class, 'update']);
        Route::delete('/{articleId}',  [ArticleController::class, 'destroy']);
    });
});
