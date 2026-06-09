<?php

use App\Http\Controllers\Api\V1\CategoryController;
use Illuminate\Support\Facades\Route;

// Primary categories (parent_id IS NULL)
Route::prefix('primary-categories')->group(function () {
    Route::get('/',                                      [CategoryController::class, 'primaryIndex']);
    Route::get('/{categoryId}/trending-article',         [CategoryController::class, 'primaryTrending']);
    Route::get('/{categoryId}',                          [CategoryController::class, 'primaryShow']);
    Route::get('/{categoryId}/secondary-categories',     [CategoryController::class, 'primarySecondaryIndex']);
});

// Secondary categories / subcategories (parent_id IS NOT NULL)
Route::prefix('secondary-categories')->group(function () {
    Route::get('/',             [CategoryController::class, 'secondaryIndex']);
    Route::get('/{categoryId}', [CategoryController::class, 'secondaryShow']);
});
