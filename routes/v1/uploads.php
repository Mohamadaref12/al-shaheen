<?php

use App\Http\Controllers\Api\V1\ImageUploadController;
use Illuminate\Support\Facades\Route;

Route::post('uploads/images', [ImageUploadController::class, 'store']);
