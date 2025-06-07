<?php

namespace App\Http\Controllers\Api\Role;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index()
    {
        $role = Role::with('permissions')->get();

        return response()->json($role);
    }

    public function store(Request $request)
    {   
        // Validate the request
        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
        ]);
        // Check if the role name is already in use
        if (Role::where('name', $request->name)->exists()) {
            return response()->json(['error' => 'Role name already exists.'], 422);
        }
        $role = Role::create(['name' => $request->name, 'guard_name' => 'web']);
        return response()->json($role,200);
    }

    public function show(Role $role)
    {
        return $role->load('permissions');
    }

    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
        ]);

        // Check if the role name is already in use
        if (Role::where('name', $request->name)->exists()) {
            return response()->json(['error' => 'Role name already exists.'], 422);
        }
        
        $role->update(['name' => $request->name]);
        return response()->json($role);
    }

    public function destroy(Role $role)
    {
        $role->delete();
        return response()->json(null, 200);
    }

    // ✅ GET: Lihat permissions yang dimiliki role
    public function syncPermissions(Role $role)
    {
        $permissions = $role->permissions->pluck('name');
        return response()->json([
            'role' => $role->name,
            'permissions' => $permissions,
        ]);
    }


     public function assignPermissions(Request $request, Role $role)
     {
         $request->validate([
             'permissions' => 'required|array',
             'permissions.*' => 'string|exists:permissions,name',
         ]);
 
         $role->syncPermissions($request->permissions);
 
         return response()->json([
             'message' => 'Permissions updated successfully',
             'permissions' => $role->permissions->pluck('name')
         ]);
     }
    
}
