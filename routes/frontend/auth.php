<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\SubDealer\LoginController as SubDealerLoginController;
use App\Http\Controllers\Auth\SubDealer\PasswordController as SubDealerPasswordController;

Route::get('giris', [LoginController::class, 'loginForm'])->name('login.form');
Route::post('giris', [LoginController::class, 'login'])->name('login');
Route::post('logout', [LoginController::class, 'logout'])->name('logout');

Route::post('password/update/{user}', [PasswordController::class, 'updatePassword'])->name('password.update');
Route::get('/password/forgot', [PasswordController::class, 'forgotPasswordForm'])->name('password.forgot.form');
Route::post('/password/forgot', [PasswordController::class, 'forgotPassword'])->name('password.forgot');
Route::get('/password/reset/{code}', [PasswordController::class, 'resetPasswordForm'])->name('password.reset.form');
Route::post('/password/reset/{code}', [PasswordController::class, 'resetPassword'])->name('password.reset');

Route::prefix('sub-dealer')->name('sub-dealer.')->group(function () {
    Route::get('login', [SubDealerLoginController::class, 'loginForm'])->name('login.form');
    Route::post('login', [SubDealerLoginController::class, 'login'])->name('login');
    Route::post('logout', [SubDealerLoginController::class, 'logout'])->name('logout');

    Route::post('password/update/{subDealer}', [SubDealerPasswordController::class, 'updatePassword'])->name('password.update');
    Route::get('password/forgot', [SubDealerPasswordController::class, 'forgotPasswordForm'])->name('password.forgot.form');
    Route::post('password/forgot', [SubDealerPasswordController::class, 'forgotPassword'])->name('password.forgot');
    Route::get('password/reset/{code}', [SubDealerPasswordController::class, 'resetPasswordForm'])->name('password.reset.form');
    Route::post('password/reset/{code}', [SubDealerPasswordController::class, 'resetPassword'])->name('password.reset');
});