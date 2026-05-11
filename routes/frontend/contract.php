<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ContractController;

Route::prefix('contract/{actor_type}/{actor_id}/{template}')->as('contract.')->group(function () {
    Route::get('/', [ContractController::class, 'show'])->name('show');
    Route::post('/store', [ContractController::class, 'store'])->name('store');
    Route::post('/accept-button', [ContractController::class, 'acceptButton'])->name('accept-button');
    Route::post('/approve', [ContractController::class, 'approve'])->name('approve');
    Route::post('/send-sms-code/{key}', [ContractController::class, 'sendSmsCode'])->name('send-sms-code');
});