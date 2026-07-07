<?php

use App\Http\Controllers\Api\V1\OpinionController;
use Illuminate\Support\Facades\Route;

Route::prefix('opinions')->group(function () {
    Route::get('/', [OpinionController::class, 'index']);
    Route::get('/{opinionId}', [OpinionController::class, 'show']);
});
