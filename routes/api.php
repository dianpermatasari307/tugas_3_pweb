<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TodoController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('api')->group(function () {
    // Public routes
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    // Protected routes (authenticated users)
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
        
        // CRUD todo milik user login
        Route::apiResource('todos', TodoController::class);
        
        // ---------------
        // ADMIN ONLY ROUTES
        // ---------------

        Route::middleware('role:admin')->group(function () {

            // 1. List semua user
            Route::get('/admin/users', function () {
                return User::select('id','name','email','role','created_at')->get();
            });

            // 2. List semua todos
            Route::get('/admin/todos', function () {
                return \App\Models\Todo::with('user')->get();
            });
        });
    });
});
