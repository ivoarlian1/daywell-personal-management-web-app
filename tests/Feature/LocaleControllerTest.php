<?php

namespace Tests\Feature;

use App\Models\DiaryEntry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocaleControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_choose_indonesian_and_keep_it_for_the_login_page(): void
    {
        $this->from('/login')
            ->post('/language', ['locale' => 'id'])
            ->assertRedirect('/login');

        $this->get('/login')
            ->assertOk()
            ->assertSee('Masuk ke ruang pribadimu');
    }

    public function test_authenticated_user_language_choice_is_saved_to_their_profile(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from('/dashboard')
            ->post('/language', ['locale' => 'id'])
            ->assertRedirect('/dashboard');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'locale' => 'id',
        ]);

        $this->get('/dashboard')
            ->assertOk()
            ->assertSee('RINGKASAN PRIBADIMU');
    }

    public function test_language_choice_rejects_unsupported_locale(): void
    {
        $this->from('/login')
            ->post('/language', ['locale' => 'fr'])
            ->assertSessionHasErrors('locale');
    }

    public function test_indonesian_preference_localizes_calendar_dates(): void
    {
        $user = User::factory()->create(['locale' => 'id']);
        DiaryEntry::factory()->for($user)->create([
            'entry_date' => '2025-01-06',
        ]);

        $this->actingAs($user)
            ->get('/diary-entries')
            ->assertOk()
            ->assertSee('Senin, 6 Januari 2025');
    }

    public function test_indonesian_diary_page_translates_write_and_pdf_actions(): void
    {
        $user = User::factory()->create(['locale' => 'id']);

        $this->actingAs($user)
            ->get('/diary-entries')
            ->assertOk()
            ->assertSee('Tulis catatan diari')
            ->assertSee('Ekspor PDF')
            ->assertSee('Pilih rentang tanggal jika perlu, atau kosongkan untuk mengekspor semua catatan.')
            ->assertDontSee('New task');
    }

    public function test_flash_messages_are_translated_for_indonesian_users(): void
    {
        $user = User::factory()->create(['locale' => 'id']);

        $this->actingAs($user)
            ->post('/diary-entries', [
                'entry_date' => '2025-01-06',
                'title' => 'Catatan pagi',
                'content' => 'Pagi yang tenang.',
            ])
            ->assertRedirect('/diary-entries');

        $this->get('/diary-entries')
            ->assertOk()
            ->assertSee('Catatan diari tersimpan.');
    }
}
