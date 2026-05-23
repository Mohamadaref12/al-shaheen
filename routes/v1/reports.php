<?php

use App\Http\Controllers\Api\V1\ReportController;
use Illuminate\Support\Facades\Route;

Route::prefix('reports')->group(function () {
    Route::get('/',             [ReportController::class, 'index']);
    Route::get('/{reportId}',   [ReportController::class, 'show']);
});
