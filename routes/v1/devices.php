<?php

use App\Http\Controllers\Api\V1\FcmDeviceController;
use Illuminate\Support\Facades\Route;

Route::post('devices/fcm-token', [FcmDeviceController::class, 'store']);
Route::delete('devices/fcm-token', [FcmDeviceController::class, 'destroy']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('me/devices/fcm-token', [FcmDeviceController::class, 'store']);
    Route::delete('me/devices/fcm-token', [FcmDeviceController::class, 'destroy']);
});
