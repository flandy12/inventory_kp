<?php

namespace App\Http\Controllers\Api\RolePermission;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionController extends Controller
{
      // Get all roles
      public function roles()
      {
          return response()->json(Role::all());
      }
  
      // Create a role
      public function createRole(Request $request)
      {
          $request->validate(['name' => 'required|unique:roles']);
          $role = Role::create(['name' => $request->name]);
  
          return response()->json($role);
      }
  
      // Get all permissions
      public function permissions()
      {
          return response()->json(Permission::all());
      }
  
      // Create a permission
      public function createPermission(Request $request)
      {
          $request->validate(['name' => 'required|unique:permissions']);
          $permission = Permission::create(['name' => $request->name]);
  
          return response()->json($permission);
      }
  
      // Assign role to user
      public function assignRole(Request $request, User $user)
      {
          $request->validate(['role' => 'required|exists:roles,name']);
          $user->assignRole($request->role);
  
          return response()->json(['message' => 'Role assigned.']);
      }
  
      // Assign permission to role
      public function givePermissionToRole(Request $request, Role $role)
      {
          $request->validate(['permission' => 'required|exists:permissions,name']);
          $role->givePermissionTo($request->permission);
  
          return response()->json(['message' => 'Permission assigned to role.']);
      }
  
      // Check if user has permission
      public function checkPermission(Request $request, User $user)
      {
          $request->validate(['permission' => 'required']);
          $has = $user->hasPermissionTo($request->permission);
  
          return response()->json(['has_permission' => $has]);
      }

      // --- ROLE ---

    // Update a role
    public function updateRole(Request $request, Role $role)
    {
        $request->validate(['name' => 'required|unique:roles,name,' . $role->id]);
        $role->name = $request->name;
        $role->save();

        return response()->json(['message' => 'Role updated.', 'role' => $role]);
    }

    // Delete a role
    public function deleteRole(Role $role)
    {
        $role->delete();
        return response()->json(['message' => 'Role deleted.']);
    }


    // --- PERMISSION ---

    // Update a permission
    public function updatePermission(Request $request, Permission $permission)
    {
        $request->validate(['name' => 'required|unique:permissions,name,' . $permission->id]);
        $permission->name = $request->name;
        $permission->save();

        return response()->json(['message' => 'Permission updated.', 'permission' => $permission]);
    }

    // Delete a permission
    public function deletePermission(Permission $permission)
    {
        $permission->delete();
        return response()->json(['message' => 'Permission deleted.']);
    }

}
