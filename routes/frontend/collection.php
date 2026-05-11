<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Collection\CashCollectionController;
use App\Http\Controllers\Collection\ChequeCollectionController;
use App\Http\Controllers\Collection\PromissoryCollectionController;

Route::prefix('collections')->name('collections.')->group(function () {
    Route::resource('cashes', CashCollectionController::class);
    Route::resource('cheques', ChequeCollectionController::class);
    Route::resource('promissories', PromissoryCollectionController::class);
});