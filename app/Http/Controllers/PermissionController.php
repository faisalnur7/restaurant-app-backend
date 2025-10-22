<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    // List all permissions
    public function index()
    {
        $permissions = Permission::all();
        return response()->json($permissions);
    }

    // Optional: Create a permission
    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|unique:permissions,name']);
        $permission = Permission::create(['name' => $request->name]);
        return response()->json($permission, 201);
    }

    // Optional: Delete a permission
    public function destroy(Permission $permission)
    {
        $permission->delete();
        return response()->json(['message' => 'Permission deleted']);
    }
}
