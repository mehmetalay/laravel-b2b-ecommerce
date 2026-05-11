<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\CampaignController;

Route::get('/campaigns/partials/{subType}', [CampaignController::class, 'loadPartial'])->name('campaigns.partial');
Route::resource('campaigns', CampaignController::class)->middleware('permission:Kampanyalar');