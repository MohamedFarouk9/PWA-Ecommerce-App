<?php

use Illuminate\Support\Facades\Route;
use Modules\Sales\Http\Controllers\OrderController;

// Orders, Fulfillment Tracking & Invoices
Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
Route::get('orders/create', [OrderController::class, 'create'])->name('orders.create');
Route::post('orders', [OrderController::class, 'store'])->name('orders.store');
Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show');
Route::get('orders/{order}/edit', [OrderController::class, 'edit'])->name('orders.edit');
Route::patch('orders/{order}', [OrderController::class, 'update'])->name('orders.update');
Route::delete('orders/{order}', [OrderController::class, 'destroy'])->name('orders.destroy');
Route::post('orders/{order}/update-status', [OrderController::class, 'updateStatus'])->name('orders.update-status');
Route::get('orders/{order}/tracking', [OrderController::class, 'tracking'])->name('orders.tracking');
Route::get('orders/{order}/invoice', [OrderController::class, 'invoice'])->name('orders.invoice');
