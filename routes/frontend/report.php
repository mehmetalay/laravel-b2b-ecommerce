<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReportController;

Route::prefix('rapor')->name('reports.')->group(function () {
    Route::get('musteri-ekstresi', [ReportController::class, 'customerStatement'])->name('customer-statement');
    Route::get('siparis-listesi', [ReportController::class, 'orderList'])->name('order-list');
    Route::get('odeme-listesi', [ReportController::class, 'paymentList'])->name('payment-list');
});
