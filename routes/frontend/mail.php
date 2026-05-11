<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MailController;

Route::prefix('mail')->name('mail.')->group(function () {
    Route::middleware(['auth:web,subdealer', 'throttle:20,1'])
        ->post('send', [MailController::class, 'send'])
        ->name('send');

    Route::middleware('auth:admin')->group(function () {
        Route::get('payment/{payment}', [MailController::class, 'payment'])->name('payment');
        Route::get('payment-link/{payment_link}', [MailController::class, 'paymentLink'])->name('payment-link');
        Route::get('payment-link/payment-success/{payment_link}', [MailController::class, 'paymentLinkPaymentSuccess'])->name('payment-link.payment-success');
        Route::get('order/{order}', [MailController::class, 'order'])->name('order');
        Route::get('dealer-application/{dealer_application}', [MailController::class, 'dealerApplication'])->name('dealer-application');
    });
});
