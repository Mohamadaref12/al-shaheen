<?php

use App\Http\Controllers\Api\V1\TagController;
use Illuminate\Support\Facades\Route;

Route::prefix('tags')->group(function () {
    Route::get('/',          [TagController::class, 'index']);
    Route::get('/{tagId}',   [TagController::class, 'show']);
});
