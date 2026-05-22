<?php

use App\Http\Controllers\Api\V1\InterviewController;
use Illuminate\Support\Facades\Route;

Route::prefix('interviews')->group(function () {
    Route::get('/',        [InterviewController::class, 'index']);
    Route::get('/{slug}',  [InterviewController::class, 'show']);
});
