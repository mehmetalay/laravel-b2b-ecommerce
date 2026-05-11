<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\PaymentReportController;

Route::resource('payments', PaymentReportController::class)->middleware('permission:Ödeme Raporları');
Route::post('payments/filter', [PaymentReportController::class, 'filter'])->name('payments.filter');
Route::post('payments/{payment}/refund', [PaymentReportController::class, 'updateRefundStatus'])->name('payments.refund');