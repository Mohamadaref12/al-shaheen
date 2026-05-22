<?php

use App\Http\Controllers\Api\V1\TagController;
use Illuminate\Support\Facades\Route;

Route::prefix('tags')->group(function () {
    Route::get('/',        [TagController::class, 'index']);
    Route::get('/{slug}',  [TagController::class, 'show']);
});
