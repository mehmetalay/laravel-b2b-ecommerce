<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\SurveyController;

Route::get('surveys/results/{survey}', [SurveyController::class, 'results'])->name('surveys.results');
Route::resource('surveys', SurveyController::class);