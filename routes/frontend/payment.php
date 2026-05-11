<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PaymentController;

Route::prefix('payments')->name('payments.')->group(function () {
    Route::get('/', [PaymentController::class, 'index'])->name('index');
    Route::get('page', [PaymentController::class, 'page'])->name('page');
    Route::post('list-installment', [PaymentController::class, 'listInstallment'])->name('list-installment');
    Route::get('payment-link/{token}', [PaymentController::class, 'paymentLink'])->name('payment-link');
    Route::post('payment-link/list-installment', [PaymentController::class, 'paymentLinklistInstallment'])->name('payment-link.list-installment');
});