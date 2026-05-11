<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\Auth\LoginController;

Route::get('giris', [LoginController::class, 'loginPage'])->name('login.page');
Route::post('giris/form', [LoginController::class, 'login'])->name('login.form');
Route::get('cikis', [LoginController::class, 'logout'])->name('logout');