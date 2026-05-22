<?php

use App\Http\Controllers\Api\V1\EventController;
use Illuminate\Support\Facades\Route;

Route::prefix('events')->group(function () {
    Route::get('/',        [EventController::class, 'index']);
    Route::get('/{slug}',  [EventController::class, 'show']);
});
