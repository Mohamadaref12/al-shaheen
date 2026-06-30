<?php

use App\Http\Controllers\Admin\DownloadArticlePdfController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/admin/articles/{article}/pdf', DownloadArticlePdfController::class)
        ->name('admin.articles.pdf');
});
