<?php

use App\Helpers\RouteHelper;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    RouteHelper::includeRouteFiles(__DIR__ . '/v1');
});
