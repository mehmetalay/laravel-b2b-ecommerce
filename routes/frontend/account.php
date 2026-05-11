<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Account\ProfileController;
use App\Http\Controllers\Account\DashboardController;

Route::prefix('account')->as('account.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
});