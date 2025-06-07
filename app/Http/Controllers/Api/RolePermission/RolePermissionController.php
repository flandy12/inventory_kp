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
        $request->validate([
            'role' => 'required|exists:roles,name',
        ]);
    
        // Optional: pastikan role sesuai guard, contoh 'sanctum' atau 'web'
        $role = Role::where('name', $request->role)
                    //->where('guard_name', 'sanctum') // uncomment jika pakai guard khusus
                    ->first();
    
        if (!$role) {
            return response()->json(['error' => 'Role not found.'], 404);
        }
    
        $user->assignRole($role); // atau $user->assignRole($request->role);
    
        return response()->json(['message' => 'Role assigned successfully.']);
      }
  
      // Assign permission to role
      public function givePermissionToRole(Request $request, Role $role)
      {
        $request->validate([
            'permission' => 'required|exists:permissions,name',
        ]);
    
        // Pastikan permission dan role pakai guard yang sama (web)
        $permission = Permission::where('name', $request->permission)
                                ->where('guard_name', $role->guard_name) // harus cocok
                                ->first();
    
        if (!$permission) {
            return response()->json(['error' => 'Permission not found for guard `' . $role->guard_name . '`'], 404);
        }
    
        // Assign permission to role
        $role->givePermissionTo($permission);
    
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
    public function updateRole(Request $request, User $user)
    {
        $request->validate([
            'role' => 'required|string|exists:roles,name',
        ]);
    
        $user->syncRoles([$request->role]);
    
        return response()->json([
            'message' => 'Role updated successfully.',
            'user' => $user->load('roles', 'permissions')
        ]);
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

    public function getUserRolesAndPermissions(User $user)
    {
        return response()->json([
            'id' => $user->id,
            'email' => $user->email,
            'name' => $user->name,

            // Semua role yang dimiliki user
            'roles' => $user->getRoleNames(), // ['admin', 'editor']

            // Semua permission yang dimiliki (langsung dan dari role)
            'permissions' => $user->getAllPermissions()->pluck('name'), // ['edit post', 'delete post']

            // Jika kamu ingin tahu permission yang langsung (tidak dari role)
            'direct_permissions' => $user->getDirectPermissions()->pluck('name'),
        ]);
    }


    public function getAllUsersWithRolesAndPermissions()
    {
        $users = User::all();

        if ($users->isEmpty()) {
            return response()->json([
                'message' => 'No users found.'
            ], 404);
        }
        
        $result = $users->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->getRoleNames(), // Role dari user
                'permissions' => $user->getAllPermissions()->pluck('name'), // Semua permission (langsung & dari role)
                'direct_permissions' => $user->getDirectPermissions()->pluck('name'), // Permission langsung
            ];
        });
    
        return response()->json($result);

    }
}
