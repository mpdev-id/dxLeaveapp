<?php

namespace App\Http\Controllers\Admin\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionController extends Controller
{
    public function index()
    {
        return view('admin.master.role-permission.index');
    }

    public function getRoles()
    {
        $roles = Role::with('permissions')->get();
        return response()->json($roles);
    }

    public function getPermissions()
    {
        $permissions = Permission::all()->groupBy(function($permission) {
            // Group permissions by module (first part before hyphen)
            $parts = explode('-', $permission->name);
            return count($parts) > 1 ? $parts[0] : 'other';
        });

        return response()->json($permissions);
    }

    public function updateRolePermissions(Request $request, $roleId)
    {
        $request->validate([
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,id'
        ]);

        $role = Role::findOrFail($roleId);
        
        // Get permission objects by IDs
        $permissions = Permission::whereIn('id', $request->permissions)->get();
        
        // Sync permissions using permission objects
        $role->syncPermissions($permissions);

        // Clear permission cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        return response()->json([
            'success' => true,
            'message' => "Permissions for role '{$role->name}' updated successfully"
        ]);
    }

    public function createRole(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:roles,name|max:255',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id'
        ]);

        $role = Role::create(['name' => $request->name]);
        
        if ($request->has('permissions') && count($request->permissions) > 0) {
            // Get permission objects by IDs
            $permissions = Permission::whereIn('id', $request->permissions)->get();
            $role->givePermissionTo($permissions);
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        return response()->json([
            'success' => true,
            'message' => "Role '{$role->name}' created successfully",
            'role' => $role->load('permissions')
        ]);
    }

    public function deleteRole($roleId)
    {
        $role = Role::findOrFail($roleId);
        
        // Prevent deletion of Super Admin role
        if ($role->name === 'Super Admin') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete Super Admin role'
            ], 403);
        }

        // Check if role is assigned to users
        if ($role->users()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => "Cannot delete role '{$role->name}' because it is assigned to users"
            ], 400);
        }

        $roleName = $role->name;
        $role->delete();

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        return response()->json([
            'success' => true,
            'message' => "Role '{$roleName}' deleted successfully"
        ]);
    }
}
