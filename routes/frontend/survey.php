<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SurveyController;

Route::get('/surveys/{survey}', [SurveyController::class,'show'])->name('surveys.show');
Route::post('/surveys/{survey}/submit', [SurveyController::class,'submit'])->name('surveys.submit');