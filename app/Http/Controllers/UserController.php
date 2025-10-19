<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of the users.
     */
    public function index()
    {
        $users = User::with('role')->latest()->get();

        return response()->json($users);
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'role_id' => 'nullable|exists:roles,id',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'phone' => 'nullable|string|max:20',
            'status' => 'required|boolean',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        // Handle profile picture upload
        if ($request->hasFile('profile_picture')) {
            $filename = time() . '_' . $request->file('profile_picture')->getClientOriginalName();
            $request->file('profile_picture')->move(public_path('uploads/users'), $filename);
            $data['profile_picture'] = 'uploads/users/' . $filename;
        }

        // Hash password
        $data['password'] = Hash::make($data['password']);

        $user = User::create($data);

        return response()->json([
            'message' => 'User created successfully!',
            'user' => $user->load('role'),
        ], 201);
    }

    /**
     * Display the specified user.
     */
    public function show(User $user)
    {
        return response()->json($user->load('role'));
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'role_id' => 'nullable|exists:roles,id',
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:6',
            'phone' => 'nullable|string|max:20',
            'status' => 'required|boolean',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        // If a new profile picture is uploaded
        if ($request->hasFile('profile_picture')) {
            // Delete old image
            if ($user->profile_picture && File::exists(public_path($user->profile_picture))) {
                File::delete(public_path($user->profile_picture));
            }

            $filename = time() . '_' . $request->file('profile_picture')->getClientOriginalName();
            $request->file('profile_picture')->move(public_path('uploads/users'), $filename);
            $data['profile_picture'] = 'uploads/users/' . $filename;
        } else {
            $data['profile_picture'] = $user->profile_picture;
        }

        // Only update password if provided
        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        return response()->json([
            'message' => 'User updated successfully!',
            'user' => $user->load('role'),
        ], 200);
    }

    /**
     * Remove the specified user.
     */
    public function destroy(User $user)
    {
        if ($user->profile_picture && File::exists(public_path($user->profile_picture))) {
            File::delete(public_path($user->profile_picture));
        }

        $user->delete();

        return response()->json(['message' => 'User deleted successfully!']);
    }
}
