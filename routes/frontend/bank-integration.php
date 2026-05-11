<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BankIntegrationController;

Route::prefix('bank-integrations')->group(function () {
    Route::prefix('payment')->name('payment.')->group(function () {
        Route::post('request', [BankIntegrationController::class, 'paymentRequest'])->name('request');
        Route::post('response', [BankIntegrationController::class, 'paymentResponse'])->name('response');
    });

    Route::prefix('payment-link')->name('payment-link.')->group(function () {
        Route::post('request/{token}', [BankIntegrationController::class, 'paymentLinkRequest'])->name('request');
        Route::post('response', [BankIntegrationController::class, 'paymentLinkResponse'])->name('response');
    });
});