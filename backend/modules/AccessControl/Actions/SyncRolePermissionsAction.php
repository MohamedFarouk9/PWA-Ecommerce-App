<?php

namespace Modules\AccessControl\Actions;

use Spatie\Permission\Models\Role;

class SyncRolePermissionsAction
{
    public function execute(Role $role, array $permissionIds): Role
    {
        $role->syncPermissions($permissionIds);
        return $role;
    }
}
