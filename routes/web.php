<?php

use App\Http\Controllers\Admin\DownloadArticlePdfController;
use App\Http\Controllers\Admin\DownloadNewsPdfController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/admin/articles/{article}/pdf', DownloadArticlePdfController::class)
        ->name('admin.articles.pdf');

    Route::get('/admin/news/{news}/pdf', DownloadNewsPdfController::class)
        ->name('admin.news.pdf');
});
