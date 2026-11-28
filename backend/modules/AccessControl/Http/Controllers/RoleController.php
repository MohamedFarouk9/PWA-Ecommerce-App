<?php

namespace Modules\AccessControl\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\AccessControl\Actions\SyncRolePermissionsAction;
use Modules\AccessControl\Http\Requests\Roles\StoreRequest;
use Modules\AccessControl\Services\RbacService;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function __construct(private RbacService $rbacService) {}

    public function index()
    {
        $roles = Role::with('permissions')->orderBy('name')->get();
        return view('admin.roles.index', compact('roles'));
    }

    public function create()
    {
        $permissions      = Permission::orderBy('name')->get();
        $permissionGroups = $this->rbacService->getGroupedPermissions();
        return view('admin.roles.create', compact('permissions', 'permissionGroups'));
    }

    public function store(StoreRequest $request, SyncRolePermissionsAction $syncAction)
    {
        try {
            $data = $request->validated();

            $role = Role::create([
                ...$data,
                'guard_name' => 'web',
            ]);

            if ($request->has('permissions') && !empty($request->permissions)) {
                $syncAction->execute($role, $request->permissions);
            }

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'status'   => 'success',
                    'message'  => 'Role created successfully.',
                    'redirect' => route('admin.roles.index'),
                ], 201);
            }

            return redirect()->route('admin.roles.index')->with('success', 'Role created successfully.');
        } catch (\Exception $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['message' => 'Failed to create role: ' . $e->getMessage()], 400);
            }
            return redirect()->back()->withInput()->with('error', 'Failed to create role: ' . $e->getMessage());
        }
    }

    public function show(Role $role)
    {
        $role->load('permissions', 'users');
        $permissions      = Permission::orderBy('name')->get();
        $permissionGroups = $this->rbacService->getGroupedPermissions();
        return view('admin.roles.show', compact('role', 'permissions', 'permissionGroups'));
    }

    public function edit(Role $role)
    {
        if ($this->rbacService->isSystemRole($role->name)) {
            return redirect()->route('admin.roles.index')->with('error', 'System roles cannot be edited.');
        }

        $role->load('permissions');
        $permissions      = Permission::orderBy('name')->get();
        $permissionGroups = $this->rbacService->getGroupedPermissions();
        return view('admin.roles.edit', compact('role', 'permissions', 'permissionGroups'));
    }

    public function update(Request $request, Role $role, SyncRolePermissionsAction $syncAction)
    {
        if ($this->rbacService->isSystemRole($role->name)) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['message' => 'System roles cannot be modified.'], 403);
            }
            return redirect()->route('admin.roles.index')->with('error', 'System roles cannot be modified.');
        }

        $request->validate([
            'name'          => 'required|string|max:255|unique:roles,name,' . $role->id,
            'permissions'   => 'array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role->update(['name' => $request->name]);

        if ($request->has('permissions')) {
            $syncAction->execute($role, $request->permissions);
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status'   => 'success',
                'message'  => 'Role updated successfully.',
                'redirect' => route('admin.roles.index'),
            ]);
        }

        return redirect()->route('admin.roles.index')->with('success', 'Role updated successfully.');
    }

    public function destroy(Role $role)
    {
        if ($this->rbacService->isSystemRole($role->name)) {
            return redirect()->route('admin.roles.index')->with('error', 'System roles cannot be deleted.');
        }

        if ($role->users()->count() > 0) {
            return redirect()->route('admin.roles.index')->with('error', 'Cannot delete role that has assigned users.');
        }

        $role->delete();
        return redirect()->route('admin.roles.index')->with('success', 'Role deleted successfully.');
    }
}
