<?php

use App\Http\Controllers\Api\OrderController;
use Illuminate\Support\Facades\Route;

Route::post('/checkout/orders', [OrderController::class, 'store'])->middleware('throttle:10,1');
