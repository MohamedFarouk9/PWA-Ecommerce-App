<?php

use Illuminate\Support\Facades\Route;
use Modules\Marketing\Http\Controllers\ContactController;
use Modules\Marketing\Http\Controllers\CouponController;
use Modules\Marketing\Http\Controllers\NotificationController;
use Modules\Marketing\Http\Controllers\ReviewController;

// Coupons & Discounts
Route::resource('coupons', CouponController::class);
Route::post('coupons/{coupon}/toggle-status', [CouponController::class, 'toggleStatus'])->name('coupons.toggle-status');

// Product Reviews Moderation
Route::resource('reviews', ReviewController::class);
Route::post('reviews/{review}/approve', [ReviewController::class, 'approve'])->name('reviews.approve');
Route::post('reviews/{review}/reject', [ReviewController::class, 'reject'])->name('reviews.reject');

// Contact Messages / Support Inbox
Route::resource('contacts', ContactController::class);
Route::post('contacts/mark-all-read', [ContactController::class, 'markAllRead'])->name('contacts.mark-all-read');
Route::post('contacts/{contact}/mark-read', [ContactController::class, 'markRead'])->name('contacts.mark-read');
Route::post('contacts/{contact}/reply', [ContactController::class, 'reply'])->name('contacts.reply');

// Notifications
Route::resource('notifications', NotificationController::class);
Route::post('notifications/mark-all-read', [NotificationController::class, 'markAllRead'])->name('notifications.mark-all-read');
Route::post('notifications/{notification}/mark-read', [NotificationController::class, 'markRead'])->name('notifications.mark-read');
