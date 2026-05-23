<?php

use App\Http\Controllers\Api\V1\MediaItemController;
use Illuminate\Support\Facades\Route;

Route::prefix('media')->group(function () {
    Route::get('/',           [MediaItemController::class, 'index']);
    Route::get('/{mediaId}',  [MediaItemController::class, 'show']);
});
