<?php

namespace Tests\Feature;

use App\Models\DiaryEntry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DiaryEntryTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_diary_entry(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/diary-entries', [
            'entry_date' => '2026-10-13',
            'title' => 'Morning reflections',
            'content' => 'Today I plan to be focused and calm.',
            'insights' => 'Need more rest.',
        ]);

        $response->assertRedirect('/diary-entries');
        $this->assertDatabaseHas('diary_entries', [
            'user_id' => $user->id,
            'title' => 'Morning reflections',
        ]);
    }

    public function test_user_cannot_view_another_users_diary_entry(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $entry = DiaryEntry::factory()->create([
            'user_id' => $otherUser->id,
            'title' => 'Private note',
        ]);

        $response = $this->actingAs($user)->get('/diary-entries/'.$entry->id);

        $response->assertForbidden();
    }

    public function test_owner_can_read_diary_entry_details(): void
    {
        $user = User::factory()->create();
        $entry = DiaryEntry::factory()->for($user)->create([
            'title' => 'A quiet morning',
            'content' => 'I took time to enjoy the sunrise.',
        ]);

        $this->actingAs($user)
            ->get('/diary-entries/'.$entry->id)
            ->assertOk()
            ->assertSee('A quiet morning')
            ->assertSee('I took time to enjoy the sunrise.');
    }

    public function test_diary_list_shows_write_and_pdf_actions_without_task_creation_action(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/diary-entries')
            ->assertOk()
            ->assertSee('Write a diary entry')
            ->assertSee('Export PDF')
            ->assertSee('Choose an optional date range, or leave it empty to export all records.')
            ->assertDontSee('New task');
    }
}
