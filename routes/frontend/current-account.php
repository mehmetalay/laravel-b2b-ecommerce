<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CurrentAccountController;

Route::get('/current-accounts', [CurrentAccountController::class, 'index']);
Route::post('/current-accounts/{id}/select', [CurrentAccountController::class, 'select']);

Route::get('switch-account/{user}', [CurrentAccountController::class, 'switchAccount'])->name('switch.account');