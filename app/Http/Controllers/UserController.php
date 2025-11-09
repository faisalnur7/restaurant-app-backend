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
        $users = User::with('roles')->latest()->get();

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

        if (isset($data['role_id'])) {
            $user->roles()->sync([$data['role_id']]);
        }

        return response()->json([
            'message' => 'User created successfully!',
            'user' => $user->load('roles'),
        ], 201);
    }

    /**
     * Display the specified user.
     */
    public function show(User $user)
    {
        return response()->json($user->load('roles'));
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'role_id' => 'nullable|exists:roles,id', // single role ID
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:6',
            'phone' => 'nullable|string|max:20',
            'status' => 'required|boolean',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        // Handle profile picture
        if ($request->hasFile('profile_picture')) {
            if ($user->profile_picture && File::exists(public_path($user->profile_picture))) {
                File::delete(public_path($user->profile_picture));
            }
            $filename = time() . '_' . $request->file('profile_picture')->getClientOriginalName();
            $request->file('profile_picture')->move(public_path('uploads/users'), $filename);
            $data['profile_picture'] = 'uploads/users/' . $filename;
        } else {
            $data['profile_picture'] = $user->profile_picture;
        }

        // Hash password if provided
        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        // Update basic user info
        $user->update($data);

        // Sync single role in pivot table
        if (isset($data['role_id'])) {
            $user->roles()->sync([$data['role_id']]);
        } else {
            $user->roles()->sync([]); // remove all roles if null
        }

        return response()->json([
            'message' => 'User updated successfully!',
            'user' => $user->load('roles'), // eager load roles for frontend
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

    public function waiters()
    {
        $waiters = User::whereHas('roles', function ($q) {
            $q->where('name', 'Waiter');
        })->get();

        return response()->json($waiters);
    }

    public function update_profile(Request $request)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Validate data
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:6',
            'phone' => 'nullable|string|max:20',
            'full_address' => 'nullable|string|max:500',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        // Handle profile picture
        if ($request->hasFile('profile_picture')) {
            if ($user->profile_picture && File::exists(public_path($user->profile_picture))) {
                File::delete(public_path($user->profile_picture));
            }

            $filename = time() . '_' . $request->file('profile_picture')->getClientOriginalName();
            $request->file('profile_picture')->move(public_path('uploads/users'), $filename);
            $data['profile_picture'] = 'uploads/users/' . $filename;
        } else {
            $data['profile_picture'] = $user->profile_picture;
        }

        // Hash password if provided
        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        // Update user info
        $user->update($data);


        return response()->json([
            'message' => 'User updated successfully!',
            'user' => $user,
        ], 200);
    }
}
