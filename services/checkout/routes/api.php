<?php

use App\Http\Controllers\Api\OrderController;
use Illuminate\Support\Facades\Route;

Route::controller(OrderController::class)->prefix('orders')->group(function () {
    Route::post('/', 'store');
});
