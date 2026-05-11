<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderController;

Route::post('orders/{order}/dealer-approve', [OrderController::class, 'dealerApprove'])->name('orders.dealer.approve');
Route::get('orders/download/all-images/{order}', [OrderController::class, 'downloadAllImages'])->name('orders.download.all-images');
Route::post('/orders/preview', [OrderController::class, 'preview'])->name('orders.preview');
Route::resource('orders', OrderController::class);