<?php

namespace Modules\AccessControl\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\AccessControl\Services\RbacService;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionController extends Controller
{
    public function __construct(private RbacService $rbacService) {}

    public function index()
    {
        $permissions      = Permission::with('roles')->orderBy('name')->get();
        $permissionGroups = $permissions->groupBy(fn ($p) => explode('.', $p->name)[0]);
        return view('admin.permissions.index', compact('permissions', 'permissionGroups'));
    }

    public function create()
    {
        $roles = Role::orderBy('name')->get();
        return view('admin.permissions.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'    => 'required|string|max:255|unique:permissions,name',
            'roles'   => 'array',
            'roles.*' => 'exists:roles,id',
        ]);

        $permission = Permission::create([
            'name'       => $request->name,
            'guard_name' => 'web',
        ]);

        if ($request->has('roles')) {
            $permission->roles()->sync($request->roles);
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status'   => 'success',
                'message'  => 'Permission created successfully.',
                'redirect' => route('admin.permissions.index'),
            ], 201);
        }

        return redirect()->route('admin.permissions.index')->with('success', 'Permission created successfully.');
    }

    public function show(Permission $permission)
    {
        $permission->load('roles', 'users');
        $roles = Role::orderBy('name')->get();
        return view('admin.permissions.show', compact('permission', 'roles'));
    }

    public function edit(Permission $permission)
    {
        if ($this->rbacService->isSystemPermission($permission->name)) {
            return redirect()->route('admin.permissions.index')->with('error', 'System permissions cannot be edited.');
        }

        $permission->load('roles');
        $roles = Role::orderBy('name')->get();
        return view('admin.permissions.edit', compact('permission', 'roles'));
    }

    public function update(Request $request, Permission $permission)
    {
        if ($this->rbacService->isSystemPermission($permission->name)) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['message' => 'System permissions cannot be modified.'], 403);
            }
            return redirect()->route('admin.permissions.index')->with('error', 'System permissions cannot be modified.');
        }

        $request->validate([
            'name'    => 'required|string|max:255|unique:permissions,name,' . $permission->id,
            'roles'   => 'array',
            'roles.*' => 'exists:roles,id',
        ]);

        $permission->update(['name' => $request->name]);

        if ($request->has('roles')) {
            $permission->roles()->sync($request->roles);
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status'   => 'success',
                'message'  => 'Permission updated successfully.',
                'redirect' => route('admin.permissions.index'),
            ]);
        }

        return redirect()->route('admin.permissions.index')->with('success', 'Permission updated successfully.');
    }

    public function destroy(Permission $permission)
    {
        if ($this->rbacService->isSystemPermission($permission->name)) {
            return redirect()->route('admin.permissions.index')->with('error', 'System permissions cannot be deleted.');
        }

        if ($permission->roles()->count() > 0 || $permission->users()->count() > 0) {
            return redirect()->route('admin.permissions.index')->with('error', 'Cannot delete permission that is assigned to roles or users.');
        }

        $permission->delete();
        return redirect()->route('admin.permissions.index')->with('success', 'Permission deleted successfully.');
    }
}
