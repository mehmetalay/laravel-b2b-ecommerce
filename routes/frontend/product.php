<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;

Route::get('urun/{slug}', [ProductController::class, 'list'])->name('product.list');
Route::get('blok/{slug}', [ProductController::class, 'block'])->name('product.block');
Route::get('marka/{slug}', [ProductController::class, 'brand'])->name('product.brand');
Route::get('urunler', [ProductController::class, 'all'])->name('product.all');
Route::get('detay/{slug}', [ProductController::class, 'detail'])->name('product.detail');
Route::get('ara', [ProductController::class, 'search'])->name('product.search');
Route::get('filter', [ProductController::class, 'filter'])->name('product.filter');