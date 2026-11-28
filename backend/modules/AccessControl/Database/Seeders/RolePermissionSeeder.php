<?php

namespace Modules\AccessControl\Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'dashboard.view',
            'users.view','users.create','users.edit','users.delete',
            'roles.view','roles.create','roles.edit','roles.delete',
            'permissions.view','permissions.create','permissions.edit','permissions.delete',
            'categories.view','categories.create','categories.edit','categories.delete',
            'products.view','products.create','products.edit','products.delete',
            'sections.view','sections.create','sections.edit','sections.delete',
            'sliders.view','sliders.create','sliders.edit','sliders.delete',
            'orders.view','orders.create','orders.edit','orders.delete',
            'reviews.view','reviews.create','reviews.edit','reviews.delete',
            'settings.view','settings.edit',
            'reports.view','reports.export',
            'contacts.view','contacts.edit','contacts.delete',
        ];
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $roles = [
            'super_admin' => array_values($permissions),
            'admin' => array_values(array_filter($permissions, function ($p) {
                return !in_array($p, ['roles.delete','permissions.delete','users.delete']);
            })),
            'manager' => [
                'dashboard.view',
                'categories.view','categories.create','categories.edit',
                'products.view','products.create','products.edit',
                'sections.view','sections.create','sections.edit',
                'sliders.view','sliders.create','sliders.edit',
                'orders.view','orders.create','orders.edit',
                'reviews.view','reviews.create','reviews.edit',
                'settings.view', 'reports.view','users.view',
                'contacts.view','contacts.edit',
            ],
            'editor' => [
                'dashboard.view',
                'categories.view','categories.create','categories.edit',
                'products.view','products.create','products.edit',
                'sections.view','sections.create','sections.edit',
                'sliders.view','sliders.create','sliders.edit',
                'reviews.view','reviews.edit',
            ],
            'moderator' => [
                'dashboard.view',
                'reviews.view','reviews.create','reviews.edit',
                'products.view','categories.view','sections.view','sliders.view',
            ],
            'user' => [],
        ];

        foreach ($roles as $roleName => $perms) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            $role->syncPermissions($perms);
        }

        $this->assignRolesToUsers();
    }

    private function assignRolesToUsers(): void
    {
        $map = [
            'superadmin@example.com' => 'super_admin',
            'admin@example.com' => 'admin',
            'manager@example.com' => 'manager',
            'editor@example.com' => 'editor',
            'moderator@example.com' => 'moderator',
        ];
        foreach ($map as $email => $role) {
            $user = User::where('email', $email)->first();
            if ($user && !$user->hasRole($role)) {
                $user->assignRole($role);
            }
        }
        $otherUsers = User::whereNotIn('email', array_keys($map))->get();
        foreach ($otherUsers as $user) {
            if (!$user->hasAnyRole(['super_admin','admin','manager','editor','moderator'])) {
                $user->assignRole('user');
            }
        }
    }
}

