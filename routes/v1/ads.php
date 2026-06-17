<?php

use App\Http\Controllers\Api\V1\AdController;
use Illuminate\Support\Facades\Route;

Route::get('ads', [AdController::class, 'index']);
