<?php

use App\Http\Controllers\Api\V1\NewsAiSuggestionController;
use App\Http\Controllers\Api\V1\NewsController;
use Illuminate\Support\Facades\Route;

Route::prefix('news')->group(function () {
    Route::get('/', [NewsController::class, 'index']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('ai/status', [NewsAiSuggestionController::class, 'status']);
        Route::post('ai/translate', [NewsAiSuggestionController::class, 'translateFromDraft']);
        Route::get('my-news', [NewsController::class, 'myNews']);
        Route::get('my-drafts', [NewsController::class, 'myDrafts']);
        Route::post('/', [NewsController::class, 'store']);
        Route::get('{newsId}/preview', [NewsController::class, 'preview']);
        Route::get('{newsId}/workspace', [NewsController::class, 'showWorkspace']);
        Route::post('{newsId}/ai/translate', [NewsAiSuggestionController::class, 'translateForNews']);
        Route::get('{newsId}/ai/suggestions', [NewsAiSuggestionController::class, 'index']);
        Route::put('/{newsId}', [NewsController::class, 'update']);
        Route::delete('/{newsId}', [NewsController::class, 'destroy']);
    });

    Route::get('/{newsId}/pdf', [NewsController::class, 'downloadPdf']);
    Route::get('/{newsId}/related', [NewsController::class, 'related']);
    Route::get('/{newsId}/trending-topics', [NewsController::class, 'trendingTopics']);
    Route::get('/{newsId}', [NewsController::class, 'show']);
});
