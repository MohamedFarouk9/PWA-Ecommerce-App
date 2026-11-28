<?php

use Illuminate\Support\Facades\Route;
use Modules\Catalog\Http\Controllers\CategoryController;
use Modules\Catalog\Http\Controllers\ProductController;
use Modules\Catalog\Http\Controllers\ProductSectionsController;
use Modules\Catalog\Http\Controllers\SliderController;

// Products
Route::get('products', [ProductController::class, 'index'])->name('products.index');
Route::get('products/create', [ProductController::class, 'create'])->name('products.create');
Route::post('products', [ProductController::class, 'store'])->name('products.store');
Route::get('products/{product}', [ProductController::class, 'show'])->name('products.show');
Route::get('products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
Route::patch('products/{product}', [ProductController::class, 'update'])->name('products.update');
Route::delete('products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
Route::post('products/{product}/toggle-status', [ProductController::class, 'toggleStatus'])->name('products.toggle-status');
Route::post('products/bulk-action', [ProductController::class, 'bulkAction'])->name('products.bulk-action');

// Categories
Route::get('categories', [CategoryController::class, 'index'])->name('categories.index');
Route::get('categories/create', [CategoryController::class, 'create'])->name('categories.create');
Route::post('categories', [CategoryController::class, 'store'])->name('categories.store');
Route::post('categories/update-order', [CategoryController::class, 'updateOrder'])->name('categories.update-order');
Route::get('categories/{category}', [CategoryController::class, 'show'])->name('categories.show');
Route::get('categories/{category}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
Route::patch('categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
Route::delete('categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

// Homepage Sections
Route::get('sections', [ProductSectionsController::class, 'index'])->name('sections.index');
Route::get('sections/create', [ProductSectionsController::class, 'create'])->name('sections.create');
Route::post('sections', [ProductSectionsController::class, 'store'])->name('sections.store');
Route::get('sections/{section}', [ProductSectionsController::class, 'show'])->name('sections.show');
Route::get('sections/{section}/edit', [ProductSectionsController::class, 'edit'])->name('sections.edit');
Route::patch('sections/{section}', [ProductSectionsController::class, 'update'])->name('sections.update');
Route::delete('sections/{section}', [ProductSectionsController::class, 'destroy'])->name('sections.destroy');
Route::post('sections/{section}/assign-products', [ProductSectionsController::class, 'assignProducts'])->name('sections.assign-products');

// Hero Sliders
Route::get('sliders', [SliderController::class, 'index'])->name('sliders.index');
Route::get('sliders/create', [SliderController::class, 'create'])->name('sliders.create');
Route::post('sliders', [SliderController::class, 'store'])->name('sliders.store');
Route::get('sliders/{slider}', [SliderController::class, 'show'])->name('sliders.show');
Route::get('sliders/{slider}/edit', [SliderController::class, 'edit'])->name('sliders.edit');
Route::patch('sliders/{slider}', [SliderController::class, 'update'])->name('sliders.update');
Route::delete('sliders/{slider}', [SliderController::class, 'destroy'])->name('sliders.destroy');
Route::post('sliders/update-order', [SliderController::class, 'updateOrder'])->name('sliders.update-order');
Route::post('sliders/{slider}/toggle-status', [SliderController::class, 'toggleStatus'])->name('sliders.toggle-status');
