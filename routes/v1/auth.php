<?php

use App\Http\Controllers\Api\V1\AuthController;
use Illuminate\Support\Facades\Route;


Route::post('register', [AuthController::class, 'register']);
Route::post('login',    [AuthController::class, 'login']);


// Protected auth routes
Route::middleware('auth:sanctum')->prefix('auth')->group(function () {
    Route::post('logout',          [AuthController::class, 'logout']);
    Route::get('me',               [AuthController::class, 'me']);
    Route::put('profile',          [AuthController::class, 'updateProfile']);
    Route::post('change-password', [AuthController::class, 'changePassword']);
});
