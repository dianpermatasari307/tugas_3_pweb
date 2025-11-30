<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display current authenticated user profile
     */
    public function index(Request $request)
    {
        return response()->json([
            'message' => 'User profile retrieved successfully',
            'user' => $request->user(),
        ], 200);
    }

    /**
     * Update current authenticated user
     */
    public function update(Request $request, string $id)
    {
        // User can only update their own profile
        if ((int) $id !== $request->user()->id) {
            return response()->json([
                'message' => 'Unauthorized - You can only update your own profile',
            ], 403);
        }

        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => [
                'sometimes',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
            'password' => 'sometimes|string|min:8',
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = bcrypt($validated['password']);
        }

        $user->update($validated);

        return response()->json([
            'message' => 'User updated successfully',
            'user' => $user,
        ], 200);
    }

    /**
     * Delete current authenticated user's account
     */
    public function destroy(Request $request, string $id)
    {
        // User can only delete their own account
        if ((int) $id !== $request->user()->id) {
            return response()->json([
                'message' => 'Unauthorized - You can only delete your own account',
            ], 403);
        }

        $user = User::findOrFail($id);
        $user->delete();

        return response()->json([
            'message' => 'User account deleted successfully',
        ], 200);
    }

    /**
     * Admin: Get all users
     */
    public function indexAll(Request $request)
    {
        $users = User::with('todos')->get();

        return response()->json([
            'message' => 'All users retrieved successfully',
            'data' => $users,
        ], 200);
    }

    /**
     * Admin: Get specific user with todos
     */
    public function show(Request $request, string $id)
    {
        $user = User::with('todos')->findOrFail($id);

        return response()->json([
            'message' => 'User retrieved successfully',
            'user' => $user,
        ], 200);
    }

    /**
     * Admin: Assign role to user
     */
    public function assignRole(Request $request, string $id)
    {
        $validated = $request->validate([
            'role' => 'required|in:user,admin',
        ]);

        $user = User::findOrFail($id);
        $user->update($validated);

        return response()->json([
            'message' => 'Role assigned successfully',
            'user' => $user,
        ], 200);
    }

    /**
     * Store method tidak digunakan (registration ada di AuthController)
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Create method tidak diperlukan untuk API
     */
    public function create()
    {
        //
    }

    /**
     * Edit method tidak diperlukan untuk API
     */
    public function edit(string $id)
    {
        //
    }
}
