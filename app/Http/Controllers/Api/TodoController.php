<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Todo;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class TodoController extends Controller
{
    /**
     * Display a listing of all todos (admin) or user's todos
     */
    public function index(Request $request)
    {
        if ($request->user()->role === 'admin') {
            // Admin dapat melihat semua todos
            $todos = Todo::with('user')->get();
        } else {
            // User hanya bisa melihat todos mereka sendiri
            $todos = $request->user()->todos()->get();
        }

        return response()->json([
            'message' => 'Todos retrieved successfully',
            'data' => $todos,
        ], 200);
    }

    /**
     * Store a newly created todo
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $todo = $request->user()->todos()->create($validated);

        return response()->json([
            'message' => 'Todo created successfully',
            'data' => $todo,
        ], 201);
    }

    /**
     * Display the specified todo
     */
    public function show(Request $request, string $id)
    {
        $todo = Todo::findOrFail($id);

        // Check authorization - hanya owner atau admin yang bisa lihat
        if ($request->user()->role !== 'admin' && $request->user()->id !== $todo->user_id) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        return response()->json([
            'message' => 'Todo retrieved successfully',
            'data' => $todo,
        ], 200);
    }

    /**
     * Update the specified todo
     */
    public function update(Request $request, string $id)
    {
        $todo = Todo::findOrFail($id);

        // Check authorization
        if ($request->user()->role !== 'admin' && $request->user()->id !== $todo->user_id) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'completed' => 'sometimes|boolean',
        ]);

        $todo->update($validated);

        return response()->json([
            'message' => 'Todo updated successfully',
            'data' => $todo,
        ], 200);
    }

    /**
     * Remove the specified todo
     */
    public function destroy(Request $request, string $id)
    {
        $todo = Todo::findOrFail($id);

        // Check authorization - hanya owner atau admin yang bisa delete
        if ($request->user()->role !== 'admin' && $request->user()->id !== $todo->user_id) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        $todo->delete();

        return response()->json([
            'message' => 'Todo deleted successfully',
        ], 200);
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
