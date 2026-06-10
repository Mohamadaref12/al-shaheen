<?php

use App\Http\Controllers\Api\V1\HomeController;
use Illuminate\Support\Facades\Route;

Route::prefix('home')->group(function () {
    Route::get('/breaking-news',    [HomeController::class, 'breakingNews']);
    Route::get('/top-articles',     [HomeController::class, 'topArticles']);
    Route::get('/trending-article', [HomeController::class, 'trendingArticle']);
    Route::get('/editor-picks',     [HomeController::class, 'editorPicks']);
    Route::get('/filters',          [HomeController::class, 'filters']);
    Route::get('/writers',          [HomeController::class, 'writers']);
});
