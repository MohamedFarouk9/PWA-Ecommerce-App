<?php

use App\Http\Controllers\Admin\AdminController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
// use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\CustomerController;
// use App\Http\Controllers\Admin\ReviewController;
use App\Http\Controllers\Admin\ContactController;
use App\Http\Controllers\Admin\SliderController;
use App\Http\Controllers\Admin\ProductSectionsController;
// use App\Http\Controllers\Admin\SettingController;
// use App\Http\Controllers\Admin\ContentController;
// use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\AuthController;


/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| Here is where you can register admin routes for your application.
| These routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "admin" middleware group. Make something great!
|
*/

// Admin Auth API (public)
Route::post('admin/login', [AuthController::class, 'loginAdmin']);
Route::post('admin/register', [AuthController::class, 'registerAdmin']);

// Admin Login Page (web)
Route::get('admin/login', [AuthController::class , 'loginPage'])->name('admin.login');

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

     // Admin Profile & Password
    Route::get('profile', [AdminController::class, 'AdminProfile'])->name('profile');
    Route::patch('profile/update', [AdminController::class, 'updateAdminProfile'])->name('profile.update');
    Route::get('password/change', [AdminController::class, 'changePassword'])->name('password.change');
    Route::post('password/update', [AdminController::class, 'updatePassword'])->name('password.update');
    Route::get('logout', [AdminController::class, 'logout'])->name('logout');

    // Admin Dashboard Stats
    Route::get('/stats', [DashboardController::class, 'stats'])->name('dashboard.stats');

    // RBAC
    Route::middleware(['permission:roles.view'])->get('roles', [RoleController::class, 'index'])->name('roles.index');
    Route::middleware(['permission:roles.create'])->get('roles/create', [RoleController::class, 'create'])->name('roles.create');
    Route::middleware(['permission:roles.create'])->post('roles', [RoleController::class, 'store'])->name('roles.store');
    Route::middleware(['permission:roles.view'])->get('roles/{role}', [RoleController::class, 'show'])->name('roles.show');
    Route::middleware(['permission:roles.edit'])->get('roles/{role}/edit', [RoleController::class, 'edit'])->name('roles.edit');
    Route::middleware(['permission:roles.edit'])->patch('roles/{role}', [RoleController::class, 'update'])->name('roles.update');
    Route::middleware(['permission:roles.delete'])->delete('roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');

    Route::middleware(['permission:permissions.view'])->get('permissions', [PermissionController::class, 'index'])->name('permissions.index');
    Route::middleware(['permission:permissions.create'])->get('permissions/create', [PermissionController::class, 'create'])->name('permissions.create');
    Route::middleware(['permission:permissions.create'])->post('permissions', [PermissionController::class, 'store'])->name('permissions.store');
    Route::middleware(['permission:permissions.view'])->get('permissions/{permission}', [PermissionController::class, 'show'])->name('permissions.show');
    Route::middleware(['permission:permissions.edit'])->get('permissions/{permission}/edit', [PermissionController::class, 'edit'])->name('permissions.edit');
    Route::middleware(['permission:permissions.edit'])->patch('permissions/{permission}', [PermissionController::class, 'update'])->name('permissions.update');
    Route::middleware(['permission:permissions.delete'])->delete('permissions/{permission}', [PermissionController::class, 'destroy'])->name('permissions.destroy');

    // Products
    Route::/*middleware('permission:products.view')->*/get('products', [ProductController::class, 'index'])->name('products.index');
    Route::/*middleware('permission:products.create')->*/get('products/create', [ProductController::class, 'create'])->name('products.create');
    Route::/*middleware('permission:products.create')->*/post('products', [ProductController::class, 'store'])->name('products.store');
    Route::/*middleware('permission:products.view')->*/get('products/{product}', [ProductController::class, 'show'])->name('products.show');
    Route::/*middleware('permission:products.edit')->*/get('products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
    Route::/*middleware('permission:products.edit')->*/patch('products/{product}', [ProductController::class, 'update'])->name('products.update');
    Route::/*middleware('permission:products.delete')->*/delete('products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
    Route::/*middleware('permission:products.edit')->*/post('products/{product}/toggle-status', [ProductController::class, 'toggleStatus'])->name('products.toggle-status');
    Route::/*middleware('permission:products.edit')->*/post('products/bulk-action', [ProductController::class, 'bulkAction'])->name('products.bulk-action');

    // Categories
    Route::get('categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::get('categories/create', [CategoryController::class, 'create'])->name('categories.create');
    Route::post('categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::post('categories/update-order', [CategoryController::class, 'updateOrder'])->name('categories.update-order');
    Route::get('categories/{category}', [CategoryController::class, 'show'])->name('categories.show');
    Route::get('categories/{category}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
    Route::patch('categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

    // Orders
    Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('orders/create', [OrderController::class, 'create'])->name('orders.create');
    Route::post('orders', [OrderController::class, 'store'])->name('orders.store');
    Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::get('orders/{order}/edit', [OrderController::class, 'edit'])->name('orders.edit');
    Route::patch('orders/{order}', [OrderController::class, 'update'])->name('orders.update');
    Route::delete('orders/{order}', [OrderController::class, 'destroy'])->name('orders.destroy');
    Route::post('orders/{order}/update-status', [OrderController::class, 'updateStatus'])->name('orders.update-status');
    Route::get('orders/{order}/tracking', [OrderController::class, 'tracking'])->name('orders.tracking');

    // Customers
    // Route::resource('customers', CustomerController::class);
    // Route::post('customers/{customer}/toggle-status', [CustomerController::class, 'toggleStatus'])->name('customers.toggle-status');
    // Route::get('customers/{customer}/orders', [CustomerController::class, 'orders'])->name('customers.orders');

    // Reviews
    // Route::resource('reviews', ReviewController::class);
    // Route::post('reviews/{review}/approve', [ReviewController::class, 'approve'])->name('reviews.approve');
    // Route::post('reviews/{review}/reject', [ReviewController::class, 'reject'])->name('reviews.reject');
    // Route::post('reviews/bulk-action', [ReviewController::class, 'bulkAction'])->name('reviews.bulk-action');

    // Contact Messages
    Route::resource('contacts', ContactController::class);
    Route::post('contacts/{contact}/mark-read', [ContactController::class, 'markRead'])->name('contacts.mark-read');
    Route::post('contacts/bulk-action', [ContactController::class, 'bulkAction'])->name('contacts.bulk-action');

    // Sliders
    Route::get('sliders', [SliderController::class, 'index'])->name('sliders.index');
    Route::get('sliders/create', [SliderController::class, 'create'])->name('sliders.create');
    Route::post('sliders', [SliderController::class, 'store'])->name('sliders.store');
    Route::get('sliders/{slider}', [SliderController::class, 'show'])->name('sliders.show');
    Route::get('sliders/{slider}/edit', [SliderController::class, 'edit'])->name('sliders.edit');
    Route::patch('sliders/{slider}', [SliderController::class, 'update'])->name('sliders.update');
    Route::delete('sliders/{slider}', [SliderController::class, 'destroy'])->name('sliders.destroy');
    Route::post('sliders/update-order', [SliderController::class, 'updateOrder'])->name('sliders.update-order');
    Route::post('sliders/{slider}/toggle-status', [SliderController::class, 'toggleStatus'])->name('sliders.toggle-status');

    // Sections
    Route::get('sections', [ProductSectionsController::class, 'index'])->name('sections.index');
    Route::get('sections/create', [ProductSectionsController::class, 'create'])->name('sections.create');
    Route::post('sections', [ProductSectionsController::class, 'store'])->name('sections.store');
    Route::get('sections/{section}', [ProductSectionsController::class, 'show'])->name('sections.show');
    Route::get('sections/{section}/edit', [ProductSectionsController::class, 'edit'])->name('sections.edit');
    Route::patch('sections/{section}', [ProductSectionsController::class, 'update'])->name('sections.update');
    Route::delete('sections/{section}', [ProductSectionsController::class, 'destroy'])->name('sections.destroy');
    Route::post('sections/{section}/assign-products', [ProductSectionsController::class, 'assignProducts'])->name('sections.assign-products');

    // Settings
    // Route::resource('settings', SettingController::class);
    // Route::post('settings/update', [SettingController::class, 'update'])->name('settings.update');
    // Route::post('settings/logo', [SettingController::class, 'updateLogo'])->name('settings.logo');
    // Route::post('settings/favicon', [SettingController::class, 'updateFavicon'])->name('settings.favicon');
    // Route::post('settings/social', [SettingController::class, 'updateSocial'])->name('settings.social');
    // Route::resource('contents', ContentController::class);
    // Route::post('contents/{content}/toggle-status', [ContentController::class, 'toggleStatus'])->name('contents.toggle-status');
    // Route::resource('reports', ReportController::class);
    // Route::get('reports/export/{type}', [ReportController::class, 'export'])->name('reports.export');
    // Route::post('reports/generate', [ReportController::class, 'generate'])->name('reports.generate');

    // Roles & Permissions Management
    Route::resource('roles', RoleController::class);
    Route::resource('permissions', PermissionController::class);

    // Notifications
    Route::resource('notifications', NotificationController::class);
    Route::post('notifications/mark-all-read', [NotificationController::class, 'markAllRead'])->name('notifications.mark-all-read');
    Route::post('notifications/{notification}/mark-read', [NotificationController::class, 'markRead'])->name('notifications.mark-read');

    // Search
    Route::get('search', [DashboardController::class, 'search'])->name('search');

    // File Upload
    Route::post('upload/image', [DashboardController::class, 'uploadImage'])->name('upload.image');
    Route::post('upload/file', [DashboardController::class, 'uploadFile'])->name('upload.file');

    // Backup
    Route::get('backup', [DashboardController::class, 'backup'])->name('backup');
    Route::post('backup/create', [DashboardController::class, 'createBackup'])->name('backup.create');
    Route::get('backup/download/{filename}', [DashboardController::class, 'downloadBackup'])->name('backup.download');

    // System
    Route::get('system', [DashboardController::class, 'system'])->name('system');
    Route::post('system/clear-cache', [DashboardController::class, 'clearCache'])->name('system.clear-cache');
    Route::post('system/optimize', [DashboardController::class, 'optimize'])->name('system.optimize');

});
