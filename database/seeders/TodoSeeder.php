<?php

namespace Database\Seeders;

use App\Models\Todo;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TodoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get admin user
        $admin = User::where('email', 'admin@example.com')->first();
        
        // Get test user
        $testUser = User::where('email', 'test@example.com')->first();
        
        // Create todos for admin
        if ($admin) {
            Todo::create([
                'user_id' => $admin->id,
                'title' => 'Setup project',
                'description' => 'Initialize the Laravel API project',
                'completed' => true,
            ]);
            
            Todo::create([
                'user_id' => $admin->id,
                'title' => 'Create database schema',
                'description' => 'Design and create all necessary tables',
                'completed' => true,
            ]);
            
            Todo::create([
                'user_id' => $admin->id,
                'title' => 'Implement authentication',
                'description' => 'Setup user registration and login',
                'completed' => false,
            ]);
        }
        
        // Create todos for test user
        if ($testUser) {
            Todo::create([
                'user_id' => $testUser->id,
                'title' => 'Learn Laravel basics',
                'description' => 'Study Laravel fundamentals',
                'completed' => false,
            ]);
            
            Todo::create([
                'user_id' => $testUser->id,
                'title' => 'Build a simple project',
                'description' => 'Create a simple todo app',
                'completed' => false,
            ]);
            
            Todo::create([
                'user_id' => $testUser->id,
                'title' => 'Complete tutorial',
                'description' => 'Finish the Laravel API role tutorial',
                'completed' => false,
            ]);
        }
    }
}
