<x-app-layout>
    @section('page-title', __('Google Calendar'))
    <section class="flex flex-col justify-between gap-5 sm:flex-row sm:items-end">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.16em] text-indigo-600">{{ __('CONNECTED SERVICES') }}</p>
            <h2 class="mt-2 font-[Manrope] text-3xl font-extrabold tracking-tight text-slate-950">Google Calendar</h2>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">{{ __('Send your personal schedule to your Google Calendar when you choose. Existing Google events are never deleted by disconnecting.') }}</p>
        </div>
        @if ($integration)
            <form method="POST" action="{{ route('integrations.google.disconnect') }}" onsubmit="return confirm(@js(__('Disconnect Google Calendar? Existing events will remain in your Google account.')));">
                @csrf
                @method('DELETE')
                <button type="submit" class="rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50">{{ __('Disconnect account') }}</button>
            </form>
        @endif
    </section>

    <section class="mt-7 rounded-3xl border border-slate-200/80 bg-white p-6 shadow-sm sm:p-8">
        @if ($integration)
            <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-4">
                    <span class="grid size-12 place-items-center rounded-2xl bg-emerald-50 text-xl font-bold text-emerald-700">G</span>
                    <div>
                        <p class="font-bold text-slate-900">{{ $integration->google_account_email }}</p>
                        <p class="mt-1 text-sm text-slate-500">{{ __('Connection') }}: {{ __(str_replace('_', ' ', ucfirst($integration->status))) }}</p>
                    </div>
                </div>
                <a href="{{ route('integrations.google.redirect') }}" class="inline-flex justify-center rounded-xl bg-indigo-600 px-4 py-3 text-sm font-bold text-white hover:bg-indigo-700">{{ __('Reconnect Google Calendar') }}</a>
            </div>
            <div class="mt-6 rounded-2xl bg-slate-50 p-4 text-sm leading-6 text-slate-600">
                <p>{{ __('Schedules are only sent to Google when you press “Sync to Google” from the schedule list. If you edit an already-synced schedule, use “Sync again” to update the existing Google event.') }}</p>
            </div>
        @elseif ($isConfigured)
            <div class="flex flex-col items-start gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="font-[Manrope] text-lg font-extrabold text-slate-900">{{ __('Connect your calendar') }}</h3>
                    <p class="mt-1 max-w-xl text-sm leading-6 text-slate-500">{{ __('Grant Daywell permission to create and update calendar events. Access tokens are encrypted before being stored.') }}</p>
                </div>
                <a href="{{ route('integrations.google.redirect') }}" class="shrink-0 rounded-xl bg-indigo-600 px-5 py-3 text-sm font-bold text-white hover:bg-indigo-700">{{ __('Connect Google Calendar') }}</a>
            </div>
        @else
            <h3 class="font-[Manrope] text-lg font-extrabold text-slate-900">{{ __('Google Calendar is not configured') }}</h3>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">{{ __('Add the Google OAuth client ID, client secret, login redirect URI, and calendar redirect URI to your local environment, then restart the application.') }}</p>
            <p class="mt-4 rounded-xl bg-slate-50 p-4 font-mono text-xs leading-6 text-slate-600">GOOGLE_CLIENT_ID<br>GOOGLE_CLIENT_SECRET<br>GOOGLE_REDIRECT_URI<br>GOOGLE_CALENDAR_REDIRECT_URI</p>
        @endif
    </section>

    <section class="mt-7">
        <div class="flex items-end justify-between gap-4">
            <div><p class="text-xs font-bold uppercase tracking-[0.16em] text-indigo-600">{{ __('YOUR SCHEDULES') }}</p><h3 class="mt-2 font-[Manrope] text-xl font-extrabold text-slate-950">{{ __('Recent items') }}</h3></div>
            <a href="{{ route('modules.index', 'schedules') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">{{ __('View all') }}</a>
        </div>
        <div class="mt-4 divide-y divide-slate-100 rounded-2xl border border-slate-200/80 bg-white px-5 shadow-sm">
            @forelse ($schedules as $schedule)
                <div class="flex flex-col gap-3 py-4 sm:flex-row sm:items-center sm:justify-between">
                    <div><p class="font-semibold text-slate-900">{{ $schedule->title }}</p><p class="mt-1 text-xs text-slate-500">{{ $schedule->start_at->timezone($schedule->timezone)->translatedFormat('j M Y · g:i A') }} · {{ __(ucfirst($schedule->sync_status)) }}</p></div>
                    @if ($integration)
                        <form method="POST" action="{{ route('schedules.google-sync', $schedule->id) }}">
                            @csrf
                            <button type="submit" class="rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">{{ $schedule->google_event_id ? __('Sync again') : __('Sync to Google') }}</button>
                        </form>
                    @endif
                </div>
            @empty
                <p class="py-8 text-center text-sm text-slate-500">{{ __('No schedules yet. Add a schedule before syncing events.') }}</p>
            @endforelse
        </div>
    </section>
</x-app-layout>
