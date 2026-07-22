<?php

use App\Http\Controllers\Api\V1\ComingSoonController;
use Illuminate\Support\Facades\Route;

Route::prefix('coming-soon')->group(function (): void {
    Route::get('/status', [ComingSoonController::class, 'status']);
    Route::post('/unlock', [ComingSoonController::class, 'unlock']);
});
