<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\Contract\ContractTemplateController;
use App\Http\Controllers\Admin\Contract\ContractSignatureController;

Route::prefix('contracts')->name('contracts.')->group(function () {
    Route::resource('templates', ContractTemplateController::class);
    Route::resource('signatures', ContractSignatureController::class)->only(['index', 'show']);
});