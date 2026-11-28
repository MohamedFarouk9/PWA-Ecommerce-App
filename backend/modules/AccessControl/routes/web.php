<?php

use Illuminate\Support\Facades\Route;
use Modules\AccessControl\Http\Controllers\PermissionController;
use Modules\AccessControl\Http\Controllers\RoleController;

// Roles Management (RBAC)
Route::middleware(['permission:roles.view'])->get('roles', [RoleController::class, 'index'])->name('roles.index');
Route::middleware(['permission:roles.create'])->get('roles/create', [RoleController::class, 'create'])->name('roles.create');
Route::middleware(['permission:roles.create'])->post('roles', [RoleController::class, 'store'])->name('roles.store');
Route::middleware(['permission:roles.view'])->get('roles/{role}', [RoleController::class, 'show'])->name('roles.show');
Route::middleware(['permission:roles.edit'])->get('roles/{role}/edit', [RoleController::class, 'edit'])->name('roles.edit');
Route::middleware(['permission:roles.edit'])->patch('roles/{role}', [RoleController::class, 'update'])->name('roles.update');
Route::middleware(['permission:roles.delete'])->delete('roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');

// Permissions Management (RBAC)
Route::middleware(['permission:permissions.view'])->get('permissions', [PermissionController::class, 'index'])->name('permissions.index');
Route::middleware(['permission:permissions.create'])->get('permissions/create', [PermissionController::class, 'create'])->name('permissions.create');
Route::middleware(['permission:permissions.create'])->post('permissions', [PermissionController::class, 'store'])->name('permissions.store');
Route::middleware(['permission:permissions.view'])->get('permissions/{permission}', [PermissionController::class, 'show'])->name('permissions.show');
Route::middleware(['permission:permissions.edit'])->get('permissions/{permission}/edit', [PermissionController::class, 'edit'])->name('permissions.edit');
Route::middleware(['permission:permissions.edit'])->patch('permissions/{permission}', [PermissionController::class, 'update'])->name('permissions.update');
Route::middleware(['permission:permissions.delete'])->delete('permissions/{permission}', [PermissionController::class, 'destroy'])->name('permissions.destroy');
