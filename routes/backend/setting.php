<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\Setting\UserController;
use App\Http\Controllers\Admin\Setting\CurrencyController;
use App\Http\Controllers\Admin\Setting\GeneralInfoController;
use App\Http\Controllers\Admin\Setting\PosManagementController;
use App\Http\Controllers\Admin\Setting\AdditionalSettingController;
use App\Http\Controllers\Admin\Setting\DesignSetting\SliderController;
use App\Http\Controllers\Admin\Setting\Definition\PaymentPlanController;
use App\Http\Controllers\Admin\Setting\Definition\PaymentTypeController;
use App\Http\Controllers\Admin\Setting\DesignSetting\ThemeSettingController;

Route::prefix('settings')->name('settings.')->middleware('permission:Ayarlar')->group(function () {
    
    Route::resource('additional-settings', AdditionalSettingController::class);

    Route::resource('general-infos', GeneralInfoController::class);

    Route::resource('users', UserController::class);

    Route::resource('currencies', CurrencyController::class);

    Route::prefix('definitions')->name('definitions.')->group(function () {
        Route::resource('payment-plans', PaymentPlanController::class);
        Route::resource('payment-types', PaymentTypeController::class);
    });

    Route::prefix('design-settings')->name('design-settings.')->group(function () {
        Route::resource('theme-settings', ThemeSettingController::class);
        Route::post('sliders/sort',[SliderController::class, 'sort'])->name('sliders.sort');
        Route::resource('sliders', SliderController::class);
    });

    Route::resource('pos-managements', PosManagementController::class);
});