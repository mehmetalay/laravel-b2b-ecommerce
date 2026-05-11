<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CartController;

Route::prefix('sepet')->middleware(['sync.cart.campaign'])->name('cart.')->group(function () {
    Route::get('/', [CartController::class, 'index'])->name('index');
    Route::get('list', [CartController::class, 'list'])->name('list');
    Route::get('summary', [CartController::class, 'summary'])->name('summary');
    Route::get('header', [CartController::class, 'header'])->name('header');
    Route::get('count', [CartController::class, 'headerCount'])->name('header.count');
    Route::post('set/payment-type', [CartController::class, 'setPaymentType'])->name('set.paymentType');
    Route::post('sepete-ekle', [CartController::class, 'addToCart'])->name('add-to-cart');
    Route::post('delete/all', [CartController::class, 'deleteAll'])->name('delete.all');
    Route::delete('delete/{cart}', [CartController::class, 'destroy'])->name('delete.product');
    Route::post('update/quantity/{cart}', [CartController::class, 'updateQuantity'])->name('update.quantity');
    Route::post('update/explanation/{cart}', [CartController::class, 'updateExplanation'])->name('update.explanation');
    Route::post('update/discount/{cart}', [CartController::class, 'updateDiscount'])->name('update.discount');
    Route::post('update/price/{cart}', [CartController::class, 'updatePrice'])->name('update.price');
    Route::post('discount/general', [CartController::class, 'generalDiscount'])->name('discount.general');
    Route::post('discount/all-cancel', [CartController::class, 'cancelAllDiscounts'])->name('discount.all-cancel');
    Route::post('import', [CartController::class, 'import'])->name('import');
    Route::post('export', [CartController::class, 'export'])->name('export');
    Route::get('download/all-images', [CartController::class, 'downloadAllImages'])->name('download.all-images');
    
    // Campaign routes
    Route::post('campaign/apply', [CartController::class, 'applyCampaign']);
    Route::get('campaign/modal', [CartController::class, 'campaignModal']);
    Route::get('campaign/modal/body', [CartController::class, 'campaignModalBody']);
    Route::post('campaign/remove', [CartController::class, 'removeCampaign']);
    Route::post('campaign/remove-single', [CartController::class, 'removeSingleCampaign']);
    Route::get('campaign/free-product/modal', [CartController::class, 'freeProductGiftModal']);
    Route::post('campaign/free-product/select-gifts', [CartController::class, 'selectFreeProductGifts']);
    Route::post('campaign/free-product/add-same-product', [CartController::class, 'addSameProductGift']);
});