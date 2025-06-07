<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Http\Resources\User\UserResource;
use Illuminate\Contracts\Encryption\DecryptException;

class UserController extends Controller
{
    // Ambil semua user
    public function index()
    {
        return UserResource::collection(User::all());
    }

    // Tambah user baru
    public function store(Request $request)
    {
        
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
        ]);

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        return response()->json($user, 201);
    }

    // Ambil detail user
    public function show($id)
    {
        $decryptedId = decrypt($id);
        $user = User::findOrFail($decryptedId);
    
        return response()->json(new UserResource($user), 200); 
    }

    // Update user
    public function update(Request $request, $id)
    {
        try {
            $user = User::findOrFail(decrypt($id));
        } catch (DecryptException $e) {
            return response()->json(['message' => 'Invalid ID provided'], 400);
        } catch (\Exception $e) {
            return response()->json(['message' => 'User not found'], 404);
        }
    
        $validated = $request->validate([
            'name'     => 'sometimes|string|max:255',
            'email'    => ['sometimes', 'email', Rule::unique('users')->ignore($user->id)],
            'password' => 'sometimes|string|min:6',
        ]);
    
        if (array_key_exists('name', $validated)) {
            $user->name = $validated['name'];
        }
    
        if (array_key_exists('email', $validated)) {
            $user->email = $validated['email'];
        }
    
        if (array_key_exists('password', $validated)) {
            $user->password = Hash::make($validated['password']);
        }
    
        $user->save();
    
        return response()->json([
            'message' => 'User updated successfully',
            'data'    => $user
        ], 200);
    }    

    // Hapus user
    public function destroy($id)
    {
        $user = User::findOrFail(decrypt($id)); // pastikan user ditemukan
        $user->delete();
        return response()->json(['message' => 'User deleted']);
    }
}
