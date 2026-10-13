<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Habit;
use App\Models\JournalEntry;
use App\Models\MoodEntry;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PersonalModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_a_journal_entry_without_assigning_it_to_another_account(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $response = $this->actingAs($user)->post(route('modules.store', 'journal'), [
            'entry_date' => '2026-10-13',
            'notes' => 'A clear and focused day.',
            'user_id' => $otherUser->id,
        ]);

        $response->assertRedirect(route('modules.index', 'journal'));
        $this->assertDatabaseHas('journal_entries', [
            'user_id' => $user->id,
            'notes' => 'A clear and focused day.',
        ]);
        $this->assertSame(
            '2026-10-13',
            JournalEntry::query()->where('user_id', $user->id)->value('entry_date')->toDateString(),
        );
        $this->assertDatabaseMissing('journal_entries', [
            'user_id' => $otherUser->id,
            'notes' => 'A clear and focused day.',
        ]);
    }

    public function test_user_can_create_update_and_delete_a_gratitude_item_for_an_owned_journal_day(): void
    {
        $user = User::factory()->create();
        $journalEntry = JournalEntry::query()->create([
            'user_id' => $user->id,
            'entry_date' => '2026-10-13',
            'notes' => 'A thoughtful day.',
        ]);

        $this->actingAs($user)
            ->post(route('modules.store', 'gratitude'), [
                'journal_entry_id' => $journalEntry->id,
                'content' => 'A kind conversation.',
            ])
            ->assertRedirect(route('modules.index', 'gratitude'));

        $gratitudeItem = $user->gratitudeItems()->firstOrFail();
        $this->assertSame('A kind conversation.', $gratitudeItem->content);

        $this->put(route('modules.update', ['gratitude', $gratitudeItem->id]), [
            'journal_entry_id' => $journalEntry->id,
            'content' => 'A kind and encouraging conversation.',
        ])->assertRedirect(route('modules.index', 'gratitude'));

        $this->assertSame('A kind and encouraging conversation.', $gratitudeItem->fresh()->content);
        $this->delete(route('modules.destroy', ['gratitude', $gratitudeItem->id]))
            ->assertRedirect(route('modules.index', 'gratitude'));
        $this->assertDatabaseMissing('gratitude_items', ['id' => $gratitudeItem->id]);
    }

    public function test_user_cannot_open_another_accounts_journal_entry(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $entry = JournalEntry::query()->create([
            'user_id' => $otherUser->id,
            'entry_date' => '2026-10-13',
            'notes' => 'Private reflection.',
        ]);

        $response = $this->actingAs($user)->get(route('modules.show', ['journal', $entry->id]));

        $response->assertNotFound();
    }

    public function test_mood_entries_reject_a_second_check_in_for_the_same_day(): void
    {
        $user = User::factory()->create();
        MoodEntry::query()->create([
            'user_id' => $user->id,
            'entry_date' => '2026-10-13',
            'mood_score' => 3,
        ]);

        $response = $this->actingAs($user)->post(route('modules.store', 'mood'), [
            'entry_date' => '2026-10-13',
            'mood_score' => 5,
            'notes' => 'A much better afternoon.',
        ]);

        $response->assertSessionHasErrors('entry_date');
        $this->assertDatabaseCount('mood_entries', 1);
    }

    public function test_schedule_rejects_an_end_time_before_its_start_time(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('modules.store', 'schedules'), [
            'title' => 'Focus session',
            'start_at' => '2026-10-14T17:00',
            'end_at' => '2026-10-14T16:00',
            'timezone' => 'Asia/Jakarta',
        ]);

        $response->assertSessionHasErrors('end_at');
        $this->assertDatabaseCount('schedules', 0);
    }

    public function test_schedule_times_are_saved_as_utc_and_edit_form_shows_the_local_time(): void
    {
        $user = User::factory()->create(['timezone' => 'Asia/Jakarta']);

        $this->actingAs($user)
            ->post(route('modules.store', 'schedules'), [
                'title' => 'Local evening session',
                'start_at' => '2026-10-14T17:00',
                'end_at' => '2026-10-14T18:00',
                'timezone' => 'Asia/Jakarta',
            ])
            ->assertRedirect(route('modules.index', 'schedules'));

        $schedule = Schedule::query()->where('user_id', $user->id)->firstOrFail();
        $this->assertSame('2026-10-14 10:00:00', $schedule->start_at->utc()->format('Y-m-d H:i:s'));
        $this->assertSame('2026-10-14 11:00:00', $schedule->end_at->utc()->format('Y-m-d H:i:s'));

        $this->get(route('modules.edit', ['schedules', $schedule->id]))
            ->assertOk()
            ->assertSee('value="2026-10-14T17:00"', false);
    }

    public function test_monthly_schedule_calendar_shows_only_the_users_selected_month(): void
    {
        $user = User::factory()->create(['timezone' => 'Asia/Jakarta']);
        $otherUser = User::factory()->create(['timezone' => 'Asia/Jakarta']);
        Schedule::query()->create([
            'user_id' => $user->id,
            'title' => 'October planning',
            'start_at' => '2026-10-15 02:00:00',
            'end_at' => '2026-10-15 03:00:00',
            'timezone' => 'Asia/Jakarta',
        ]);
        Schedule::query()->create([
            'user_id' => $user->id,
            'title' => 'September planning',
            'start_at' => '2026-09-15 02:00:00',
            'end_at' => '2026-09-15 03:00:00',
            'timezone' => 'Asia/Jakarta',
        ]);
        Schedule::query()->create([
            'user_id' => $otherUser->id,
            'title' => 'Another account event',
            'start_at' => '2026-10-15 02:00:00',
            'end_at' => '2026-10-15 03:00:00',
            'timezone' => 'Asia/Jakarta',
        ]);

        $this->actingAs($user)
            ->get(route('modules.index', ['module' => 'schedules', 'period' => '2026-10']))
            ->assertOk()
            ->assertSee('October 2026')
            ->assertSee('October planning')
            ->assertDontSee('September planning')
            ->assertDontSee('Another account event');
    }

    public function test_schedule_calendar_rejects_a_non_string_month_filter(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('modules.index', ['module' => 'schedules', 'period' => ['2026-10']]))
            ->assertRedirect()
            ->assertSessionHasErrors('period');
    }

    public function test_habit_check_in_can_be_added_and_undone_for_the_users_local_day(): void
    {
        $user = User::factory()->create(['timezone' => 'Asia/Jakarta']);
        $habit = Habit::query()->create([
            'user_id' => $user->id,
            'name' => 'Read for ten minutes',
            'frequency' => 'daily',
            'is_active' => true,
        ]);
        $this->travelTo('2026-10-13 18:00:00');

        $this->actingAs($user)
            ->post(route('modules.habits.toggle', $habit->id))
            ->assertRedirect();

        $this->assertDatabaseHas('habit_logs', [
            'user_id' => $user->id,
            'habit_id' => $habit->id,
            'log_date' => '2026-10-14',
        ]);

        $this->post(route('modules.habits.toggle', $habit->id))->assertRedirect();

        $this->assertDatabaseMissing('habit_logs', [
            'user_id' => $user->id,
            'habit_id' => $habit->id,
            'log_date' => '2026-10-14',
        ]);
    }

    public function test_book_progress_cannot_exceed_the_total_page_count(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('modules.store', 'books'), [
            'title' => 'A careful reader',
            'total_pages' => 100,
            'current_page' => 101,
            'status' => 'reading',
        ]);

        $response->assertSessionHasErrors('current_page');
        $this->assertDatabaseCount('books', 0);
    }

    public function test_user_can_manage_book_notes_for_an_owned_book(): void
    {
        $user = User::factory()->create();
        $book = Book::query()->create([
            'user_id' => $user->id,
            'title' => 'A thoughtful guide',
            'current_page' => 10,
            'status' => 'reading',
        ]);

        $this->actingAs($user)
            ->post(route('modules.store', 'book-notes'), [
                'book_id' => $book->id,
                'note_type' => 'quote',
                'title' => 'A useful thought',
                'page_number' => 10,
                'content' => 'Make room for careful attention.',
            ])
            ->assertRedirect(route('modules.index', 'book-notes'));

        $note = $user->bookNotes()->firstOrFail();
        $this->assertSame('A useful thought', $note->title);

        $this->put(route('modules.update', ['book-notes', $note->id]), [
            'book_id' => $book->id,
            'note_type' => 'summary',
            'title' => 'A revised thought',
            'page_number' => 11,
            'content' => 'A short summary of the chapter.',
        ])->assertRedirect(route('modules.index', 'book-notes'));

        $this->assertSame('summary', $note->fresh()->note_type);
        $this->delete(route('modules.destroy', ['book-notes', $note->id]))
            ->assertRedirect(route('modules.index', 'book-notes'));
        $this->assertDatabaseMissing('book_notes', ['id' => $note->id]);
    }

    public function test_book_can_be_viewed_updated_and_deleted_by_its_owner(): void
    {
        $user = User::factory()->create();
        $book = Book::query()->create([
            'user_id' => $user->id,
            'title' => 'Before the update',
            'current_page' => 4,
            'status' => 'reading',
        ]);

        $this->actingAs($user)
            ->get(route('modules.show', ['books', $book->id]))
            ->assertOk()
            ->assertSee('Before the update');

        $this->put(route('modules.update', ['books', $book->id]), [
            'title' => 'After the update',
            'current_page' => 9,
            'status' => 'reading',
        ])->assertRedirect(route('modules.index', 'books'));

        $this->assertSame('After the update', $book->fresh()->title);
        $this->delete(route('modules.destroy', ['books', $book->id]))
            ->assertRedirect(route('modules.index', 'books'));
        $this->assertDatabaseMissing('books', ['id' => $book->id]);
    }
}
