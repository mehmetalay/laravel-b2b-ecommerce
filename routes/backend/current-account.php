<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\CurrentAccountController;

Route::post('/current-accounts/import', [CurrentAccountController::class, 'import']);
Route::resource('/current-accounts', CurrentAccountController::class)->middleware('permission:Cari Hesaplar');