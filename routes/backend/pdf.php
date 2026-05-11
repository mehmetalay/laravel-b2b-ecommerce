<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\PDFController;

Route::prefix('pdf')->name('pdf.')->group(function () {
    Route::get('export/payments', [PDFController::class, 'payments'])->name('export.payments');
});