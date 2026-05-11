<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PageController;

Route::get('hakkimizda', [PageController::class, 'aboutUs'])->name('page.about-us');
Route::get('iletisim', [PageController::class, 'contactUs'])->name('page.contact-us');
Route::get('gizlilik-taahhudu', [PageController::class, 'privacyCommitment'])->name('page.privacy-commitment');
Route::get('banka-bilgilerimiz', [PageController::class, 'ourBankInformation'])->name('page.ourBankInformation');