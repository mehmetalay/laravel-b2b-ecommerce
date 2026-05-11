<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\locationController;

Route::get('/locations/cities', [locationController::class, 'cities']);
Route::get('/locations/districts/{city}', [locationController::class, 'districts']);
Route::get('/locations/neighborhoods/{district}', [locationController::class, 'neighborhoods']);