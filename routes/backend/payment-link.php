<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\PaymentLinkController;

Route::resource('payment-links', PaymentLinkController::class)->middleware('permission:Ödeme Linkleri');
Route::post('payment-links/{paymentLink}/refund', [PaymentLinkController::class, 'updateRefundStatus'])->name('payment-links.refund');