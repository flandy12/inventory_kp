<?php

namespace App\Http\Controllers\Api\Role;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index()
    {
        return Role::with('permissions')->get();
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
        return response()->json($role, 201);
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
        return response()->json(null, 204);
    }
}
