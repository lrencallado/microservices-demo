<?php

use App\Http\Controllers\Api\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:60,1')->group(function () {
    Route::controller(ProductController::class)->prefix('catalog/products')->group(function () {
        Route::get('/', 'index');
        Route::get('/{id}', 'show');
        Route::post('/{id}/decrement-stock', 'decrementStock');
        Route::post('/{id}/increment-stock', 'incrementStock');
    });
});

