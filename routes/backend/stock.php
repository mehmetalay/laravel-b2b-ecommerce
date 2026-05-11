<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\Catalog\BrandController;
use App\Http\Controllers\Admin\Catalog\ProductController;
use App\Http\Controllers\Admin\Catalog\CategoryController;
use App\Http\Controllers\Admin\Catalog\HomepageBlockController;
use App\Http\Controllers\Admin\Catalog\ProductAttribute\AttributeController;
use App\Http\Controllers\Admin\Catalog\ProductAttribute\AttributeGroupController;
use App\Http\Controllers\Admin\Catalog\ProductAttribute\AttributeValueController;

Route::get('products/table-data', [ProductController::class, 'tableData'])->name('products.table-data');

Route::prefix('catalog')->name('catalog.')->group(function () {
    // Ürünler
    Route::get('products/search', [ProductController::class, 'search'])->name('products.search');
    Route::post('products/ajax/attributes', [ProductController::class, 'listAttributes'])->name('products.ajax.attributes');
    Route::resource('products', ProductController::class);

    // Kategoriler
    Route::post('categories/sort',[CategoryController::class, 'sort'])->name('categories.sort');
    Route::put('categories/{category}/sort-order', [CategoryController::class, 'updateSortOrder'])->name('categories.sort-order');
    Route::resource('categories', CategoryController::class);
    Route::get('categories/{category}/subcategories', [CategoryController::class, 'index'])->name('categories.subcategories');

    // Markalar
    Route::post('brands/sort', [BrandController::class, 'sort'])->name('brands.sort');
    Route::put('brands/{brand}/sort-order', [BrandController::class, 'updateSortOrder'])->name('brands.sort-order');
    Route::resource('brands', BrandController::class);

    // Ürün Özellikleri
    Route::prefix('product-attributes')->name('product-attributes.')->group(function () {
        Route::resource('attribute-groups', AttributeGroupController::class);
        Route::post('/attribute-groups/{id}/duplicate', [AttributeGroupController::class, 'duplicate'])->name('attribute-groups.duplicate');

        Route::prefix('attribute-groups/{attributeGroup}')->name('attribute-groups.')->group(function () {
            Route::post('attributes/sort', [AttributeController::class, 'sort'])->name('attributes.sort');
            Route::put('attributes/{attribute}/sort-order', [AttributeController::class, 'updateSortOrder'])->name('attributes.sort-order');
            Route::resource('attributes', AttributeController::class);
        });

        Route::prefix('attributes/{attribute}')->name('attributes.')->group(function () {
            Route::post('attribute-values/sort', [AttributeValueController::class, 'sort'])->name('attribute-values.sort');
            Route::put('attribute-values/{attributeValue}/sort-order', [AttributeValueController::class, 'updateSortOrder'])->name('attribute-values.sort-order');
            Route::resource('attribute-values', AttributeValueController::class);
        });
    });

    // Anasayfa Blokları
    Route::resource('homepage-blocks', HomepageBlockController::class)->except(['show']);
    Route::get('homepage-blocks/{homepage_block}/products', [HomepageBlockController::class, 'products'])->name('homepage-blocks.products');
    Route::post('homepage-blocks/{homepage_block}/products', [HomepageBlockController::class, 'addProducts'])->name('homepage-blocks.addProducts');
});
