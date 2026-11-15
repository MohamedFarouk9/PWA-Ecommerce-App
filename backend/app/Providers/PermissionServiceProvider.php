<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class PermissionServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // If the permission tables don't exist yet or DB is offline, skip boot logic to avoid SQL errors
        try {
            $tables = config('permission.table_names');
            $requiredTables = [
                $tables['permissions'] ?? null,
                $tables['roles'] ?? null,
                $tables['model_has_permissions'] ?? null,
                $tables['model_has_roles'] ?? null,
                $tables['role_has_permissions'] ?? null,
            ];
            foreach ($requiredTables as $tbl) {
                if (! $tbl || ! Schema::hasTable($tbl)) {
                    return;
                }
            }
        } catch (\Throwable $e) {
            return;
        }

        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions if they don't exist
        $this->createPermissions();

        // Create roles if they don't exist
        $this->createRoles();

        // Assign permissions to roles
        $this->assignPermissionsToRoles();
    }

    private function createPermissions(): void
    {
        $permissions = [
            'dashboard.view',
            'users.view', 'users.create', 'users.edit', 'users.delete',
            'roles.view', 'roles.create', 'roles.edit', 'roles.delete',
            'permissions.view', 'permissions.create', 'permissions.edit', 'permissions.delete',
            'categories.view', 'categories.create', 'categories.edit', 'categories.delete',
            'products.view', 'products.create', 'products.edit', 'products.delete',
            'sections.view', 'sections.create', 'sections.edit', 'sections.delete',
            'sliders.view', 'sliders.create', 'sliders.edit', 'sliders.delete',
            'orders.view', 'orders.create', 'orders.edit', 'orders.delete',
            'reviews.view', 'reviews.create', 'reviews.edit', 'reviews.delete',
            'settings.view', 'settings.edit',
            'reports.view', 'reports.export',
            'contacts.view', 'contacts.edit', 'contacts.delete',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }
    }

    private function createRoles(): void
    {
        $roles = [
            'super_admin', 'admin', 'manager', 'editor', 'moderator', 'user',
        ];

        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
        }
    }

    private function assignPermissionsToRoles(): void
    {
        $allPermissions = Permission::all();
        $dashboardPermissions = Permission::where('name', 'dashboard.view')->get();
        $userPermissions = Permission::where('name', 'like', 'users.%')->get();
        $rolePermissions = Permission::where('name', 'like', 'roles.%')->get();
        $permissionPermissions = Permission::where('name', 'like', 'permissions.%')->get();
        $categoryPermissions = Permission::where('name', 'like', 'categories.%')->get();
        $productPermissions = Permission::where('name', 'like', 'products.%')->get();
        $sectionPermissions = Permission::where('name', 'like', 'sections.%')->get();
        $sliderPermissions = Permission::where('name', 'like', 'sliders.%')->get();
        $orderPermissions = Permission::where('name', 'like', 'orders.%')->get();
        $reviewPermissions = Permission::where('name', 'like', 'reviews.%')->get();
        $settingPermissions = Permission::where('name', 'like', 'settings.%')->get();
        $reportPermissions = Permission::where('name', 'like', 'reports.%')->get();
        $contactPermissions = Permission::where('name', 'like', 'contacts.%')->get();

        $superAdmin = Role::where('name', 'super_admin')->first();
        if ($superAdmin) {
            $superAdmin->syncPermissions($allPermissions);
        }

        $admin = Role::where('name', 'admin')->first();
        if ($admin) {
            $adminPermissions = $allPermissions->filter(function ($permission) {
                return !in_array($permission->name, [
                    'roles.delete',
                    'permissions.delete',
                    'users.delete',
                ]);
            });
            $admin->syncPermissions($adminPermissions);
        }

        // Manager - Product, category, order, and basic management permissions
        $manager = Role::where('name', 'manager')->first();
        if ($manager) {
            $managerPermissions = collect([
                $dashboardPermissions,
                $categoryPermissions->whereIn('name', ['categories.view', 'categories.create', 'categories.edit']),
                $productPermissions->whereIn('name', ['products.view', 'products.create', 'products.edit']),
                $sectionPermissions->whereIn('name', ['sections.view', 'sections.create', 'sections.edit']),
                $sliderPermissions->whereIn('name', ['sliders.view', 'sliders.create', 'sliders.edit']),
                $orderPermissions->whereIn('name', ['orders.view', 'orders.create', 'orders.edit']),
                $reviewPermissions->whereIn('name', ['reviews.view', 'reviews.create', 'reviews.edit']),
                $settingPermissions->where('name', 'settings.view'),
                $reportPermissions->where('name', 'reports.view'),
                $userPermissions->where('name', 'users.view'),
                $contactPermissions->whereIn('name', ['contacts.view', 'contacts.edit']),
            ])->flatten();
            $manager->syncPermissions($managerPermissions);
        }

        // Editor - Content management permissions
        $editor = Role::where('name', 'editor')->first();
        if ($editor) {
            $editorPermissions = collect([
                $dashboardPermissions,
                $categoryPermissions->whereIn('name', ['categories.view', 'categories.create', 'categories.edit']),
                $productPermissions->whereIn('name', ['products.view', 'products.create', 'products.edit']),
                $sectionPermissions->whereIn('name', ['sections.view', 'sections.create', 'sections.edit']),
                $sliderPermissions->whereIn('name', ['sliders.view', 'sliders.create', 'sliders.edit']),
                $reviewPermissions->whereIn('name', ['reviews.view', 'reviews.edit']),
            ])->flatten();
            $editor->syncPermissions($editorPermissions);
        }

        // Moderator - Review and content moderation permissions
        $moderator = Role::where('name', 'moderator')->first();
        if ($moderator) {
            $moderatorPermissions = collect([
                $dashboardPermissions,
                $reviewPermissions,
                $productPermissions->where('name', 'products.view'),
                $categoryPermissions->where('name', 'categories.view'),
                $sectionPermissions->where('name', 'sections.view'),
                $sliderPermissions->where('name', 'sliders.view'),
            ])->flatten();
            $moderator->syncPermissions($moderatorPermissions);
        }

        // User - No admin permissions (regular customer)
        $user = Role::where('name', 'user')->first();
        if ($user) {
            $user->syncPermissions([]);
        }
    }
}

