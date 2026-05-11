<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\OrderController;

Route::resource('orders', OrderController::class)->middleware('permission:Siparişler');
Route::post('orders/filter', [OrderController::class, 'filter'])->name('orders.filter');