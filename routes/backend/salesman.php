<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\SalesmanController;

Route::resource('salesmans', SalesmanController::class)->middleware('permission:Plasiyerler');