<?php

use App\Http\Controllers\Api\V1\NewsletterController;
use Illuminate\Support\Facades\Route;

Route::prefix('newsletter')->group(function () {
    Route::post('subscribe',   [NewsletterController::class, 'subscribe']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::delete('unsubscribe', [NewsletterController::class, 'unsubscribe']);
    });
});
