<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LocalizationController;

Route::get('/language/{language_code}', [LocalizationController::class, 'switch'])->name('language');