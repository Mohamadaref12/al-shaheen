<?php

use App\Http\Controllers\Api\V1\SubscriptionController;
use Illuminate\Support\Facades\Route;

Route::get('packages', [SubscriptionController::class, 'packages']);

Route::middleware('auth:sanctum')->prefix('subscriptions')->group(function () {
    Route::get('/',  [SubscriptionController::class, 'index']);
    Route::post('/', [SubscriptionController::class, 'store']);
});
