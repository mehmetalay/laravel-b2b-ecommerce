<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AddressController;

Route::get('/addresses', [AddressController::class, 'index'])->name('addresses.index');
Route::get('/addresses/list', [AddressController::class, 'list']);
Route::get('/addresses/{id}', [AddressController::class, 'show']);
Route::post('/addresses/store', [AddressController::class, 'store']);
Route::delete('/addresses/{id}', [AddressController::class, 'destroy']);