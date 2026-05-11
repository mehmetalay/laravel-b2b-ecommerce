<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PDFController;

Route::middleware('auth:web,subdealer,admin')
    ->prefix('pdf')
    ->name('pdf.')
    ->group(function () {
        Route::get('payment-receipt/payment/{payment}', [PDFController::class, 'paymentReceiptPayment'])->name('payment-receipt.payment');
        Route::get('payment-receipt/payment-link/{paymentLink}', [PDFController::class, 'paymentReceiptPaymentLink'])->name('payment-receipt.payment-link');
        Route::get('customer-statement', [PDFController::class, 'customerStatement'])->name('customer-statement');
});
