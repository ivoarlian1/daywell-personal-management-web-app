<?php

namespace Tests\Feature;

use App\Models\Todo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TodoTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_task(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/todos', [
            'title' => 'Plan sprint tasks',
            'description' => 'Outline work for this week.',
            'priority' => 'high',
            'status' => 'pending',
            'due_at' => '2026-10-20',
        ]);

        $response->assertRedirect('/todos');
        $this->assertDatabaseHas('todos', [
            'user_id' => $user->id,
            'title' => 'Plan sprint tasks',
        ]);
    }

    public function test_user_cannot_update_another_users_task(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $todo = Todo::factory()->create([
            'user_id' => $otherUser->id,
            'title' => 'Private task',
        ]);

        $response = $this->actingAs($user)->put('/todos/'.$todo->id, [
            'title' => 'Changed title',
            'priority' => 'low',
            'status' => 'completed',
        ]);

        $response->assertForbidden();
    }

    public function test_task_creation_action_is_limited_to_the_task_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/todos')
            ->assertOk()
            ->assertSee('New task');

        $this->get('/dashboard')
            ->assertOk()
            ->assertDontSee('New task');
    }
}
