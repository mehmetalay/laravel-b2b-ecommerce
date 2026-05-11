<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExcelController;

Route::middleware('auth:web,subdealer,admin')->prefix('excel')->name('excel.')->group(function () {
    Route::prefix('export')->name('export.')->group(function () {
        Route::get('cart', [ExcelController::class, 'cartExport'])->name('cart');
        Route::get('order/{order}', [ExcelController::class, 'orderExport'])->name('order');
        Route::get('payment', [ExcelController::class, 'paymentExport'])->name('payment');
        Route::get('no-image-products', [ExcelController::class, 'noImageProductsExport'])->name('no-image-products');
        Route::get('products-without-quantity', [ExcelController::class, 'productsWithoutQuantityExport'])->name('products-without-quantity');
        Route::get('inactive-products', [ExcelController::class, 'inactiveProducts'])->name('inactive-products');
    });
});