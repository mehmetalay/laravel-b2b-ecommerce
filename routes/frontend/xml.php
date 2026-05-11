<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\XmlController;

Route::prefix('xml')->name('xml.')->group(function () {
    Route::get('product', [XmlController::class, 'product'])->name('product');
    Route::get('category', [XmlController::class, 'category'])->name('category');
    Route::get('brand', [XmlController::class, 'brand'])->name('brand');
});