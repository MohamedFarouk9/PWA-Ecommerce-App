<?php

namespace Modules\AccessControl\Services;

use Spatie\Permission\Models\Permission;

class RbacService
{
    public function getGroupedPermissions()
    {
        return Permission::orderBy('name')
            ->get()
            ->groupBy(fn ($permission) => explode('.', $permission->name)[0]);
    }

    public function isSystemRole(string $name): bool
    {
        return in_array($name, ['super_admin', 'admin', 'user'], true);
    }

    public function isSystemPermission(string $name): bool
    {
        return in_array($name, [
            'dashboard.view',
            'users.view', 'users.create', 'users.edit', 'users.delete',
            'roles.view', 'roles.create', 'roles.edit', 'roles.delete',
            'permissions.view', 'permissions.create', 'permissions.edit', 'permissions.delete',
        ], true);
    }
}
