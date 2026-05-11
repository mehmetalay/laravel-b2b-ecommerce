<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DealerApplicationController;

Route::resource('dealer-application', DealerApplicationController::class)->middleware('permission:Bayi Başvuruları');
Route::get('dealer-application/download/document', [DealerApplicationController::class, 'download'])->name('dealer-application.download.document');