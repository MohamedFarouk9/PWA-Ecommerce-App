<?php

use Illuminate\Support\Facades\Route;
use Modules\Dashboard\Http\Controllers\AdminProfileController;
use Modules\Dashboard\Http\Controllers\DashboardController;

// Executive Dashboard & Business Growth Actions
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/stats', [DashboardController::class, 'stats'])->name('dashboard.stats');
Route::get('/dashboard/export-sales', [DashboardController::class, 'exportSalesCsv'])->name('dashboard.export-sales');
Route::post('/dashboard/quick-flash-sale', [DashboardController::class, 'createQuickFlashSale'])->name('dashboard.quick-flash-sale');

// Global Admin Search & Media Upload Utilities
Route::get('/search', [DashboardController::class, 'search'])->name('search');
Route::post('/upload/image', [DashboardController::class, 'uploadImage'])->name('upload.image');
Route::post('/upload/file', [DashboardController::class, 'uploadFile'])->name('upload.file');

// Admin Profile, Password & Session
Route::get('/profile', [AdminProfileController::class, 'AdminProfile'])->name('profile');
Route::patch('/profile/update', [AdminProfileController::class, 'updateAdminProfile'])->name('profile.update');
Route::get('/password/change', [AdminProfileController::class, 'changePassword'])->name('password.change');
Route::post('/password/update', [AdminProfileController::class, 'updatePassword'])->name('password.update');
Route::get('/logout', [AdminProfileController::class, 'logout'])->name('logout');
