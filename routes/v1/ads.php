<?php

use App\Http\Controllers\Api\V1\AdController;
use Illuminate\Support\Facades\Route;

Route::get('ads', [AdController::class, 'index']);
Route::post('ads/{adId}/view', [AdController::class, 'trackView']);
Route::post('ads/{adId}/click', [AdController::class, 'trackClick']);
