<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ReportController;

Route::prefix('report')->name('report.')->middleware('permission:Raporlar')->group(function () {
    Route::get('no-image-products', [ReportController::class, 'noImageProducts'])->name('no-image-products');
    Route::get('products-without-quantity', [ReportController::class, 'productsWithoutQuantity'])->name('products-without-quantity');
    Route::get('inactive-products', [ReportController::class, 'inactiveProducts'])->name('inactive-products');
    Route::get('non-splittable-packages', [ReportController::class, 'nonSplittablePackages'])->name('non-splittable-packages');
});