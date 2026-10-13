<?php

namespace App\Http\Controllers;

use App\Models\CalendarSyncLog;
use App\Models\GoogleIntegration;
use App\Models\Schedule;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class GoogleCalendarController extends Controller
{
    public function show(Request $request): View
    {
        $integration = $request->user()->googleIntegrations()
            ->where('provider', 'google')
            ->first();

        return view('integrations.google', [
            'integration' => $integration,
            'isConfigured' => filled(config('services.google.client_id'))
                && filled(config('services.google.client_secret'))
                && filled(config('services.google.calendar_redirect')),
            'schedules' => $request->user()->schedules()->latest('start_at')->limit(10)->get(),
        ]);
    }

    public function disconnect(Request $request): RedirectResponse
    {
        $integration = $request->user()->googleIntegrations()->where('provider', 'google')->first();
        $revocationWarning = null;

        if ($integration !== null) {
            $token = $integration->encrypted_refresh_token ?: $integration->encrypted_access_token;

            if ($token) {
                try {
                    $response = Http::asForm()->timeout(10)->post('https://oauth2.googleapis.com/revoke', [
                        'token' => $token,
                    ]);

                    if (! $response->successful() && $response->status() !== 400) {
                        $revocationWarning = 'Google could not confirm that access was revoked. The connection was removed locally; revoke Daywell access in your Google account settings.';
                        Log::warning('Google Calendar token revocation was rejected.', [
                            'user_id' => $request->user()->id,
                            'http_status' => $response->status(),
                        ]);
                    }
                } catch (ConnectionException $exception) {
                    $revocationWarning = 'Google could not be reached to revoke access. The connection was removed locally; revoke Daywell access in your Google account settings.';
                    Log::warning('Google Calendar token revocation could not reach Google.', [
                        'user_id' => $request->user()->id,
                    ]);
                }
            }

            $integration->delete();
        }

        $request->user()->schedules()->whereNotNull('google_event_id')->update(['sync_status' => 'pending']);

        if ($revocationWarning !== null) {
            return redirect()->route('integrations.google')->with('error', $revocationWarning);
        }

        return redirect()->route('integrations.google')->with('status', 'Google Calendar was disconnected. Existing events in Google Calendar were not deleted.');
    }

    public function sync(Request $request, Schedule $schedule): RedirectResponse
    {
        abort_unless($schedule->user_id === $request->user()->id, 404);

        $integration = $request->user()->googleIntegrations()
            ->where('provider', 'google')
            ->first();

        if ($integration === null) {
            return redirect()->route('integrations.google')->with('error', 'Connect Google Calendar before syncing a schedule.');
        }

        $accessToken = $this->accessToken($integration);

        if ($accessToken === null) {
            return redirect()->route('integrations.google')->with('error', 'Google authorization needs to be renewed. Reconnect Google Calendar and try again.');
        }

        $operation = $schedule->google_event_id === null ? 'create' : 'update';
        $calendarId = rawurlencode($integration->calendar_id ?: 'primary');
        $url = "https://www.googleapis.com/calendar/v3/calendars/{$calendarId}/events";
        $start = $schedule->start_at->copy()->timezone($schedule->timezone);
        $end = $schedule->end_at->copy()->timezone($schedule->timezone);
        $event = [
            'summary' => $schedule->title,
            'description' => $schedule->description,
            'location' => $schedule->location,
            'start' => ['dateTime' => $start->toIso8601String(), 'timeZone' => $schedule->timezone],
            'end' => ['dateTime' => $end->toIso8601String(), 'timeZone' => $schedule->timezone],
        ];

        if ($operation === 'update') {
            $url .= '/'.rawurlencode($schedule->google_event_id);
        }

        try {
            $response = Http::timeout(15)
                ->withToken($accessToken)
                ->acceptJson()
                ->send($operation === 'update' ? 'PUT' : 'POST', $url, ['json' => $event]);
        } catch (ConnectionException $exception) {
            Log::warning('Google Calendar event sync could not reach the API.', [
                'user_id' => $request->user()->id,
                'schedule_id' => $schedule->id,
            ]);
            $this->recordSync($schedule, $operation, 'failed', 'calendar_api_unavailable');

            return back()->with('error', 'Google Calendar is temporarily unavailable. Your schedule was not changed.');
        }

        if (! $response->successful()) {
            $this->handleApiFailure($integration, $schedule, $operation, $response);

            return back()->with('error', 'Google Calendar rejected the sync request. Review the connection settings and try again.');
        }

        $eventId = $response->json('id') ?: $schedule->google_event_id;

        if (! is_string($eventId) || $eventId === '') {
            $this->recordSync($schedule, $operation, 'failed', 'calendar_event_id_missing');

            return back()->with('error', 'Google Calendar did not return an event ID, so the schedule was not marked as synced.');
        }

        $schedule->update(['google_event_id' => $eventId, 'sync_status' => 'synced']);
        $this->recordSync($schedule, $operation, 'success');

        return back()->with('status', 'Schedule synced to Google Calendar.');
    }

    private function accessToken(GoogleIntegration $integration): ?string
    {
        if ($integration->encrypted_access_token && $integration->token_expires_at?->isFuture()) {
            return $integration->encrypted_access_token;
        }

        if (! $integration->encrypted_refresh_token) {
            $integration->update(['status' => 'reauthorization_required']);

            return null;
        }

        try {
            $response = Http::asForm()->timeout(15)->post('https://oauth2.googleapis.com/token', [
                'client_id' => config('services.google.client_id'),
                'client_secret' => config('services.google.client_secret'),
                'refresh_token' => $integration->encrypted_refresh_token,
                'grant_type' => 'refresh_token',
            ]);
        } catch (ConnectionException $exception) {
            Log::warning('Google Calendar access token refresh could not reach Google.', [
                'user_id' => $integration->user_id,
            ]);
            $integration->update(['status' => 'temporarily_unavailable']);

            return null;
        }

        if (! $response->successful() || ! is_string($response->json('access_token'))) {
            Log::warning('Google Calendar access token refresh failed.', [
                'user_id' => $integration->user_id,
                'http_status' => $response->status(),
            ]);
            $integration->update(['status' => 'reauthorization_required']);

            return null;
        }

        $integration->update([
            'encrypted_access_token' => $response->json('access_token'),
            'encrypted_refresh_token' => $response->json('refresh_token') ?: $integration->encrypted_refresh_token,
            'token_expires_at' => now()->addSeconds((int) $response->json('expires_in', 3600)),
            'status' => 'connected',
        ]);

        return $integration->fresh()->encrypted_access_token;
    }

    private function handleApiFailure(
        GoogleIntegration $integration,
        Schedule $schedule,
        string $operation,
        Response $response,
    ): void {
        $errorCode = 'calendar_api_http_'.$response->status();
        $this->recordSync($schedule, $operation, 'failed', $errorCode);

        if (in_array($response->status(), [401, 403], true)) {
            $integration->update(['status' => 'reauthorization_required']);
        }

        Log::warning('Google Calendar rejected an event sync.', [
            'user_id' => $schedule->user_id,
            'schedule_id' => $schedule->id,
            'http_status' => $response->status(),
        ]);
    }

    private function recordSync(
        Schedule $schedule,
        string $operation,
        string $status,
        ?string $errorCode = null,
    ): void {
        CalendarSyncLog::query()->create([
            'user_id' => $schedule->user_id,
            'schedule_id' => $schedule->id,
            'provider_event_id' => $schedule->google_event_id,
            'operation' => $operation,
            'sync_status' => $status,
            'error_code' => $errorCode,
            'synced_at' => now(),
        ]);
    }
}
