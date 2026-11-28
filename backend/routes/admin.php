<?php

use Illuminate\Support\Facades\Route;
use Modules\Core\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| Public Admin Authentication Routes
|--------------------------------------------------------------------------
|
| All authenticated Admin Dashboard routes are organized inside
| modular domains under `backend/modules/`:
|   - modules/Core/           (Platform migrations, settings, auth, base provider)
|   - modules/Dashboard/      (Overview, Analytics, Search, Uploads, Profile)
|   - modules/Catalog/        (Products, Categories, Sections, Sliders)
|   - modules/Sales/          (Orders, Carts, Tracking, Invoices)
|   - modules/Marketing/      (Coupons, Reviews, Contacts, Notifications)
|   - modules/Customers/      (Customer Management)
|   - modules/AccessControl/  (Roles & Permissions RBAC)
|
| Registered automatically via App\Providers\AdminModulesServiceProvider.
*/

// Admin Auth API (public)
Route::post('admin/login', [AuthController::class, 'loginAdmin']);
Route::post('admin/register', [AuthController::class, 'registerAdmin']);

// Admin Login Page (web)
Route::get('admin/login', [AuthController::class, 'loginPage'])->name('admin.login');
