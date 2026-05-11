<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Ajax\CustomerController;
use App\Http\Controllers\Ajax\HomepageController;
use App\Http\Controllers\Ajax\BankIntegrationController;
use App\Http\Controllers\Ajax\ProductController;

Route::prefix('ajax')->name('ajax.')->group(function () {

    Route::post('/homepage', [HomepageController::class, 'index']);

    Route::get(
        'bank-integrations/{bankIntegration}/installments',
        [BankIntegrationController::class, 'installments']
    );

    Route::get(
        'customers',
        [CustomerController::class, 'index']
    );

    Route::get(
        'product-suggestions',
        [ProductController::class, 'suggestions']
    );

});