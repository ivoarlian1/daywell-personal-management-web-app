<?php

namespace Tests\Feature;

use App\Models\CalendarSyncLog;
use App\Models\GoogleIntegration;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Tests\TestCase;

class GoogleCalendarIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_user_can_sync_a_schedule_and_another_users_schedule_is_hidden(): void
    {
        config([
            'services.google.client_id' => 'test-client',
            'services.google.client_secret' => 'test-secret',
            'services.google.calendar_redirect' => 'http://localhost/integrations/google/callback',
        ]);

        $user = User::factory()->create();
        $anotherUser = User::factory()->create();
        $integration = GoogleIntegration::query()->create([
            'user_id' => $user->id,
            'provider' => 'google',
            'google_account_email' => 'person@example.com',
            'encrypted_access_token' => 'plain-access-token',
            'encrypted_refresh_token' => 'plain-refresh-token',
            'token_expires_at' => now()->addHour(),
            'scopes' => ['https://www.googleapis.com/auth/calendar.events'],
            'calendar_id' => 'primary',
            'status' => 'connected',
        ]);
        $schedule = Schedule::query()->create([
            'user_id' => $user->id,
            'title' => 'Project planning',
            'start_at' => now()->addDay(),
            'end_at' => now()->addDay()->addHour(),
            'timezone' => 'UTC',
        ]);
        $otherSchedule = Schedule::query()->create([
            'user_id' => $anotherUser->id,
            'title' => 'Private event',
            'start_at' => now()->addDay(),
            'end_at' => now()->addDay()->addHour(),
            'timezone' => 'UTC',
        ]);

        Http::fake([
            'www.googleapis.com/calendar/v3/*' => Http::response(['id' => 'google-event-123'], 200),
        ]);

        $response = $this->actingAs($user)->post(route('schedules.google-sync', $schedule));

        $response->assertRedirect();
        $response->assertSessionHas('status', 'Schedule synced to Google Calendar.');
        $this->assertSame('google-event-123', $schedule->fresh()->google_event_id);
        $this->assertSame('synced', $schedule->fresh()->sync_status);
        $this->assertNull($otherSchedule->fresh()->google_event_id);
        $this->assertSame('plain-access-token', $integration->fresh()->encrypted_access_token);
        $this->assertDatabaseMissing('google_integrations', ['encrypted_access_token' => 'plain-access-token']);
        Http::assertSent(fn ($request): bool => $request->method() === 'POST'
            && $request->hasHeader('Authorization', 'Bearer plain-access-token')
            && $request['summary'] === 'Project planning');

        $this->actingAs($user)->post(route('schedules.google-sync', $schedule))->assertRedirect();
        Http::assertSent(fn ($request): bool => $request->method() === 'PUT'
            && str_ends_with($request->url(), '/events/google-event-123'));
        $this->assertDatabaseCount('calendar_sync_logs', 2);
    }

    public function test_service_worker_and_manifest_are_available_to_guests(): void
    {
        $this->get(route('pwa.manifest'))
            ->assertOk()
            ->assertHeader('content-type', 'application/manifest+json');

        $this->get(route('pwa.service-worker'))
            ->assertOk()
            ->assertHeader('service-worker-allowed', '/')
            ->assertSee("event.request.mode === 'navigate'", false);
    }

    public function test_calendar_sync_does_not_expose_another_users_schedule(): void
    {
        $user = User::factory()->create();
        $anotherUser = User::factory()->create();
        $schedule = Schedule::query()->create([
            'user_id' => $anotherUser->id,
            'title' => 'Private event',
            'start_at' => now()->addDay(),
            'end_at' => now()->addDay()->addHour(),
            'timezone' => 'UTC',
        ]);

        $this->actingAs($user)
            ->post(route('schedules.google-sync', $schedule))
            ->assertNotFound();
    }

    public function test_verified_google_profile_can_create_and_sign_in_to_an_account(): void
    {
        config([
            'services.google.client_id' => 'test-client',
            'services.google.client_secret' => 'test-secret',
            'services.google.redirect' => 'http://localhost/auth/google/callback',
        ]);

        Socialite::fake('google', SocialiteUser::fake([
            'id' => 'google-account-314',
            'name' => 'Google Person',
            'email' => 'person@example.com',
            'verified_email' => true,
        ]));

        $this->get(route('google.login.callback'))
            ->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('users', [
            'google_id' => 'google-account-314',
            'email' => 'person@example.com',
            'name' => 'Google Person',
        ]);
        $this->assertAuthenticated();
    }

    public function test_expired_access_token_is_refreshed_before_syncing(): void
    {
        config([
            'services.google.client_id' => 'test-client',
            'services.google.client_secret' => 'test-secret',
        ]);

        $user = User::factory()->create();
        GoogleIntegration::query()->create([
            'user_id' => $user->id,
            'provider' => 'google',
            'google_account_email' => 'person@example.com',
            'encrypted_access_token' => 'expired-access-token',
            'encrypted_refresh_token' => 'refresh-token',
            'token_expires_at' => now()->subMinute(),
            'scopes' => ['https://www.googleapis.com/auth/calendar.events'],
            'calendar_id' => 'primary',
            'status' => 'connected',
        ]);
        $schedule = Schedule::query()->create([
            'user_id' => $user->id,
            'title' => 'Focus session',
            'start_at' => now()->addDay(),
            'end_at' => now()->addDay()->addHour(),
            'timezone' => 'UTC',
        ]);

        Http::fake([
            'oauth2.googleapis.com/token' => Http::response([
                'access_token' => 'renewed-access-token',
                'expires_in' => 3600,
            ], 200),
            'www.googleapis.com/calendar/v3/*' => Http::response(['id' => 'refreshed-event'], 200),
        ]);

        $this->actingAs($user)
            ->post(route('schedules.google-sync', $schedule))
            ->assertSessionHas('status', 'Schedule synced to Google Calendar.');

        $this->assertSame('renewed-access-token', $user->googleIntegrations()->first()->encrypted_access_token);
        Http::assertSent(fn ($request): bool => str_contains($request->url(), 'oauth2.googleapis.com/token')
            && $request['grant_type'] === 'refresh_token');
        Http::assertSent(fn ($request): bool => $request->hasHeader('Authorization', 'Bearer renewed-access-token'));
    }

    public function test_disconnect_preserves_google_events_and_marks_local_schedules_pending(): void
    {
        $user = User::factory()->create();
        GoogleIntegration::query()->create([
            'user_id' => $user->id,
            'provider' => 'google',
            'google_account_email' => 'person@example.com',
            'encrypted_access_token' => 'secret-token',
            'encrypted_refresh_token' => 'refresh-token',
            'token_expires_at' => now()->addHour(),
            'scopes' => [],
            'calendar_id' => 'primary',
            'status' => 'connected',
        ]);
        $schedule = Schedule::query()->create([
            'user_id' => $user->id,
            'title' => 'Keep this event',
            'start_at' => now()->addDay(),
            'end_at' => now()->addDay()->addHour(),
            'timezone' => 'UTC',
            'google_event_id' => 'do-not-delete',
            'sync_status' => 'synced',
        ]);

        Http::fake([
            'oauth2.googleapis.com/revoke' => Http::response([], 200),
        ]);

        $this->actingAs($user)
            ->delete(route('integrations.google.disconnect'))
            ->assertSessionHas('status', 'Google Calendar was disconnected. Existing events in Google Calendar were not deleted.');

        $this->assertDatabaseMissing('google_integrations', ['user_id' => $user->id]);
        $this->assertSame('do-not-delete', $schedule->fresh()->google_event_id);
        $this->assertSame('pending', $schedule->fresh()->sync_status);
        $this->assertSame(0, CalendarSyncLog::query()->count());
        Http::assertSent(fn ($request): bool => str_contains($request->url(), 'oauth2.googleapis.com/revoke')
            && $request['token'] === 'refresh-token');
    }

    public function test_calendar_rate_limits_are_reported_and_logged_without_secrets(): void
    {
        $user = User::factory()->create();
        GoogleIntegration::query()->create([
            'user_id' => $user->id,
            'provider' => 'google',
            'google_account_email' => 'person@example.com',
            'encrypted_access_token' => 'secret-access-token',
            'encrypted_refresh_token' => 'secret-refresh-token',
            'token_expires_at' => now()->addHour(),
            'scopes' => ['https://www.googleapis.com/auth/calendar.events'],
            'calendar_id' => 'primary',
            'status' => 'connected',
        ]);
        $schedule = Schedule::query()->create([
            'user_id' => $user->id,
            'title' => 'Focus session',
            'start_at' => now()->addDay(),
            'end_at' => now()->addDay()->addHour(),
            'timezone' => 'UTC',
        ]);

        Http::fake([
            'www.googleapis.com/calendar/v3/*' => Http::response(['error' => 'rateLimitExceeded'], 429),
        ]);

        $this->actingAs($user)
            ->post(route('schedules.google-sync', $schedule))
            ->assertSessionHas('error', 'Google Calendar rejected the sync request. Review the connection settings and try again.');

        $this->assertDatabaseHas('calendar_sync_logs', [
            'user_id' => $user->id,
            'schedule_id' => $schedule->id,
            'sync_status' => 'failed',
            'error_code' => 'calendar_api_http_429',
        ]);
        $this->assertSame('pending', $schedule->fresh()->sync_status);
        $this->assertStringNotContainsString('secret-access-token', CalendarSyncLog::query()->firstOrFail()->toJson());
    }

    public function test_disconnect_removes_local_tokens_and_warns_if_google_cannot_revoke(): void
    {
        $user = User::factory()->create();
        GoogleIntegration::query()->create([
            'user_id' => $user->id,
            'provider' => 'google',
            'google_account_email' => 'person@example.com',
            'encrypted_access_token' => 'secret-access-token',
            'encrypted_refresh_token' => 'secret-refresh-token',
            'token_expires_at' => now()->addHour(),
            'scopes' => [],
            'calendar_id' => 'primary',
            'status' => 'connected',
        ]);
        Http::fake([
            'oauth2.googleapis.com/revoke' => Http::failedConnection(),
        ]);

        $this->actingAs($user)
            ->delete(route('integrations.google.disconnect'))
            ->assertSessionHas('error', 'Google could not be reached to revoke access. The connection was removed locally; revoke Daywell access in your Google account settings.');

        $this->assertDatabaseMissing('google_integrations', ['user_id' => $user->id]);
    }
}
