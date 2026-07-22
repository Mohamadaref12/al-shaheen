<?php

use App\Http\Controllers\Admin\DownloadArticlePdfController;
use App\Http\Controllers\Admin\DownloadNewsPdfController;
use App\Http\Controllers\ComingSoonController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/coming-soon', [ComingSoonController::class, 'show'])->name('coming-soon');
Route::post('/unlock', [ComingSoonController::class, 'unlock'])->name('coming-soon.unlock');

Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/admin/articles/{article}/pdf', DownloadArticlePdfController::class)
        ->name('admin.articles.pdf');

    Route::get('/admin/news/{news}/pdf', DownloadNewsPdfController::class)
        ->name('admin.news.pdf');

    Route::post('/admin/fcm/register', [\App\Http\Controllers\Admin\FcmDeviceController::class, 'store'])
        ->name('admin.fcm.register');
});
