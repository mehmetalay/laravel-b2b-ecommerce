<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Dealer\SubDealerController;

Route::prefix('dealers')->name('dealers.')->group(function () {
    Route::post('sub-dealers/cancel-selection', [SubDealerController::class, 'cancelSelection'])->name('sub-dealers.cancel-selection');
    Route::resource('sub-dealers', SubDealerController::class);
});