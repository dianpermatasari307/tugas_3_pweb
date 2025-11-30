<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function admin_can_access_admin_routes()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin, 'sanctum')
            ->getJson('/api/admin/users')
            ->assertStatus(200);
    }

    /** @test */
    public function admin_can_access_admin_todos()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        // create some todos belonging to users
        $user = User::factory()->create();
        \App\Models\Todo::factory()->count(2)->create(['user_id' => $user->id]);

        $this->actingAs($admin, 'sanctum')
            ->getJson('/api/admin/todos')
            ->assertStatus(200)
            ->assertJsonStructure([['id','title','description','completed','user']]);
    }

    /** @test */
    public function regular_user_cannot_access_admin_routes()
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/admin/users')
            ->assertStatus(403)
            ->assertJsonStructure(['message']);
    }
}
