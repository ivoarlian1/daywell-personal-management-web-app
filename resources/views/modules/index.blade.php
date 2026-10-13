<x-app-layout>
    @section('page-title', __($definition['title']))
    <section class="flex flex-col justify-between gap-5 sm:flex-row sm:items-end">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.16em] text-indigo-600">🌿 {{ __('PERSONAL SPACE') }}</p>
            <h2 class="mt-2 font-[Manrope] text-3xl font-extrabold tracking-tight text-slate-950">{{ __($definition['title']) }}</h2>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">{{ __($definition['description']) }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            @if (in_array($module, ['journal', 'gratitude', 'mood', 'habits', 'schedules', 'books', 'book-notes'], true))
                @php($exportModule = $module === 'book-notes' ? 'books' : $module)
                <x-exports.form :module="$exportModule" :date-range="$exportModule !== 'books'" />
            @endif
            <a href="{{ route('modules.create', $module) }}" class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 py-3 text-sm font-bold text-white shadow-md shadow-indigo-200 transition hover:bg-indigo-700">✨ {{ __('Add') }} {{ __($definition['title']) }}</a>
        </div>
    </section>
    <section class="mt-7 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm sm:p-5">
        <form method="GET" action="{{ route('modules.index', $module) }}" class="flex flex-col gap-3 sm:flex-row">
            <label for="module-search" class="sr-only">{{ __('Search') }} {{ strtolower(__($definition['title'])) }}</label>
            <input id="module-search" type="search" name="search" value="{{ $search }}" placeholder="{{ __('Search') }} {{ strtolower(__($definition['title'])) }}..." class="min-w-0 flex-1 rounded-xl border-slate-200 px-4 py-3 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            @if ($module === 'books')
                <label for="status-filter" class="sr-only">{{ __('Filter books by status') }}</label>
                <select id="status-filter" name="status" class="rounded-xl border-slate-200 px-4 py-3 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">{{ __('All reading statuses') }}</option>
                    @foreach (['want_to_read' => 'Want to read', 'reading' => 'Currently reading', 'completed' => 'Completed'] as $value => $label)
                        <option value="{{ $value }}" @selected(request('status') === $value)>{{ __($label) }}</option>
                    @endforeach
                </select>
            @endif
            @if (in_array($module, ['journal', 'gratitude', 'mood', 'schedules'], true))
                <label for="period-filter" class="sr-only">{{ __('Filter by month') }}</label>
                <input id="period-filter" type="month" name="period" value="{{ request('period') }}" class="rounded-xl border-slate-200 px-4 py-3 text-sm focus:border-indigo-500 focus:ring-indigo-500">
            @endif
            <button type="submit" class="rounded-xl border border-slate-200 px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">🔎 {{ __('Search') }}</button>
            @if ($search !== '' || request()->filled('status') || request()->filled('period'))
                <a href="{{ route('modules.index', $module) }}" class="rounded-xl px-3 py-3 text-center text-sm font-semibold text-slate-500 hover:bg-slate-50">{{ __('Clear') }}</a>
            @endif
        </form>
    </section>
    @if ($module === 'schedules')
        <section class="mt-6 overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-5 py-4">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-indigo-600">🗓️ {{ __('MONTHLY CALENDAR') }}</p>
                    <h3 class="mt-1 font-[Manrope] text-lg font-extrabold text-slate-950">{{ $calendarMonth->translatedFormat('F Y') }}</h3>
                </div>
                @php($calendarQuery = array_merge(['module' => $module], request()->query()))
                <div class="flex gap-2">
                    <a href="{{ route('modules.index', array_merge($calendarQuery, ['period' => $calendarMonth->subMonth()->format('Y-m')])) }}" aria-label="Previous month" class="grid size-10 place-items-center rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50">‹</a>
                    <a href="{{ route('modules.index', array_merge($calendarQuery, ['period' => now(Auth::user()->timezone ?? config('app.timezone'))->format('Y-m')])) }}" class="inline-flex items-center rounded-xl border border-slate-200 px-3 text-xs font-semibold text-slate-600 hover:bg-slate-50">{{ __('Today') }}</a>
                    <a href="{{ route('modules.index', array_merge($calendarQuery, ['period' => $calendarMonth->addMonth()->format('Y-m')])) }}" aria-label="Next month" class="grid size-10 place-items-center rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50">›</a>
                </div>
            </div>
            <div class="overflow-x-auto p-3 sm:p-5">
                <div class="min-w-[700px]">
                    <div class="grid grid-cols-7 gap-px overflow-hidden rounded-t-xl bg-slate-200">
                        @foreach (['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'] as $weekday)
                            <div class="bg-slate-50 px-2 py-3 text-center text-[11px] font-bold uppercase tracking-wide text-slate-500">{{ __($weekday) }}</div>
                        @endforeach
                    </div>
                    <div class="grid grid-cols-7 gap-px overflow-hidden rounded-b-xl bg-slate-200">
                        @foreach ($calendarDays as $calendarDay)
                            @php($dayEvents = $calendarEvents->get($calendarDay->format('Y-m-d'), collect()))
                            <div class="{{ $calendarDay->month === $calendarMonth->month ? 'bg-white' : 'bg-slate-50/70' }} min-h-28 p-2 sm:p-2.5">
                                <div class="flex justify-end">
                                    <span class="{{ $calendarDay->toDateString() === $today ? 'bg-indigo-600 text-white' : 'text-slate-500' }} grid size-7 place-items-center rounded-full text-xs font-bold">{{ $calendarDay->day }}</span>
                                </div>
                                <div class="mt-1 space-y-1">
                                    @foreach ($dayEvents as $calendarEvent)
                                        <a href="{{ route('modules.show', [$module, $calendarEvent->id]) }}" class="block truncate rounded-md bg-indigo-50 px-1.5 py-1 text-[10px] font-semibold leading-4 text-indigo-800 hover:bg-indigo-100" title="{{ $calendarEvent->title }} · {{ $calendarEvent->start_at->timezone($calendarEvent->timezone)->format('g:i A') }}">
                                            {{ $calendarEvent->start_at->timezone($calendarEvent->timezone)->format('g:i A') }} {{ $calendarEvent->title }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>
    @endif
    @if ($module === 'habits')
        <section class="mt-5 rounded-2xl border border-indigo-100 bg-indigo-50/70 p-4 sm:p-5">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <div><p class="text-sm font-bold text-indigo-950">🌱 {{ __('Today’s check-in') }}</p><p class="mt-1 text-xs text-indigo-700">{{ \Illuminate\Support\Carbon::parse($today)->translatedFormat('l, j F Y') }} · {{ __('Dates follow your profile time zone') }}</p></div>
                <span class="rounded-full bg-white px-3 py-1.5 text-xs font-semibold text-indigo-700">{{ count($completedHabits) }} {{ __('checked in') }}</span>
            </div>
        </section>
    @endif
    @if ($module === 'mood' && $records->isNotEmpty())
        <section class="mt-5 rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between gap-3">
                <div><p class="text-xs font-bold uppercase tracking-widest text-indigo-600">{{ __('Recent check-ins') }}</p><h3 class="mt-1 font-[Manrope] font-extrabold">{{ __('A gentle mood trend') }}</h3></div>
                <span class="text-xs text-slate-400">{{ __('Scale of 1 to 5 · personal reflection only') }}</span>
            </div>
            <div class="mt-5 flex min-h-32 items-end gap-2 overflow-x-auto pb-1">
                @foreach ($records->getCollection()->sortBy('entry_date') as $mood)
                    <div class="flex min-w-10 flex-col items-center gap-2">
                        <span class="text-xs font-bold text-slate-600">{{ $mood->mood_score }}</span>
                        <span class="w-7 rounded-t-lg bg-indigo-400" style="height: {{ 18 + ($mood->mood_score * 17) }}px"></span>
                        <time class="text-[10px] text-slate-400">{{ $mood->entry_date->translatedFormat('j M') }}</time>
                    </div>
                @endforeach
            </div>
        </section>
    @endif
    @if ($records->isEmpty())
        <section class="mt-6 rounded-3xl border border-dashed border-slate-300 bg-white px-6 py-14 text-center shadow-sm">
            <span class="mx-auto grid size-14 place-items-center rounded-2xl bg-indigo-50 text-2xl text-indigo-700" aria-hidden="true">🌼</span>
            <h3 class="mt-4 font-[Manrope] text-lg font-extrabold text-slate-900">{{ __('Nothing here just yet') }}</h3>
            <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-slate-500">{{ __('Start with one entry. You can edit or remove it whenever you need.') }}</p>
        </section>
    @else
        <section class="mt-6 grid gap-4 md:grid-cols-2 2xl:grid-cols-3">
            @foreach ($records as $record)
                <article class="flex flex-col rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <a href="{{ route('modules.show', [$module, $record->id]) }}" class="font-[Manrope] text-base font-extrabold text-slate-900 hover:text-indigo-700">
                                @if ($module === 'journal')
                                    {{ \Illuminate\Support\Carbon::parse($record->entry_date)->translatedFormat('j F Y') }} {{ __('journal') }}
                                @elseif ($module === 'gratitude')
                                    {{ \Illuminate\Support\Str::limit($record->content, 48) }}
                                @elseif ($module === 'mood')
                                    {{ __('Mood :score of 5', ['score' => $record->mood_score]) }}
                                @elseif ($module === 'habits')
                                    {{ $record->name }}
                                @elseif ($module === 'schedules')
                                    {{ $record->title }}
                                @elseif ($module === 'books')
                                    {{ $record->title }}
                                @else
                                    {{ $record->title ?: $record->note_type }}
                                @endif
                            </a>
                            <p class="mt-1 text-xs font-medium text-slate-400">
                                @if ($module === 'journal' || $module === 'mood')
                                    {{ \Illuminate\Support\Carbon::parse($record->entry_date)->translatedFormat('j M Y') }}
                                @elseif ($module === 'gratitude')
                                    {{ $record->journalEntry?->entry_date?->translatedFormat('j M Y') }}
                                @elseif ($module === 'schedules')
                                    {{ $record->start_at?->timezone($record->timezone)->format('M j · g:i A') }} – {{ $record->end_at?->timezone($record->timezone)->format('g:i A') }}
                                @elseif ($module === 'books')
                                    {{ $record->author ?: __('Personal reading list') }} · {{ __(str_replace('_', ' ', ucfirst($record->status))) }}
                                @elseif ($module === 'habits')
                                    {{ __($record->frequency) }} · {{ $record->is_active ? __('Active') : __('Paused') }}
                                @else
                                    {{ ucfirst($record->note_type) }} · {{ $record->book?->title }}
                                @endif
                            </p>
                        </div>
                        @if ($module === 'habits')
                            <form method="POST" action="{{ route('modules.habits.toggle', $record->id) }}">
                                @csrf
                                <button type="submit" aria-label="{{ in_array($record->id, $completedHabits) ? __('Undo today’s habit check-in') : __('Check in this habit today') }}" class="{{ in_array($record->id, $completedHabits) ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500 hover:bg-indigo-100 hover:text-indigo-700' }} grid size-10 place-items-center rounded-xl text-lg transition">{{ in_array($record->id, $completedHabits) ? '✓' : '○' }}</button>
                            </form>
                        @endif
                    </div>
                    @if ($module === 'books' && $record->total_pages)
                        @php($progress = min(100, (int) round(($record->current_page / $record->total_pages) * 100)))
                        <div class="mt-4">
                            <div class="flex justify-between text-xs font-semibold text-slate-500"><span>{{ __('Reading progress') }}</span><span>{{ $progress }}%</span></div>
                            <div class="mt-2 h-2 overflow-hidden rounded-full bg-slate-100"><div class="h-full rounded-full bg-indigo-500" style="width: {{ $progress }}%"></div></div>
                            <p class="mt-2 text-xs text-slate-400">{{ $record->current_page }} {{ __('of') }} {{ $record->total_pages }} {{ __('pages') }}</p>
                        </div>
                    @else
                        @php($summary = $record->notes ?? $record->content ?? $record->description ?? $record->location ?? $record->author ?? null)
                        @if ($summary)
                            <p class="mt-4 line-clamp-3 flex-1 text-sm leading-6 text-slate-600">{{ $summary }}</p>
                        @else
                            <div class="flex-1"></div>
                        @endif
                    @endif
                    @if ($module === 'habits')
                        @php($weeklyProgress = min((int) ($weeklyHabitCounts[$record->id] ?? 0), $record->frequency === 'weekly' ? 1 : 7))
                        <div class="mt-4 rounded-xl bg-slate-50 px-3 py-2.5">
                            <div class="flex items-center justify-between text-xs font-semibold text-slate-500"><span>{{ __('Last 7 days') }}</span><span>{{ $weeklyProgress }} / {{ $record->frequency === 'weekly' ? 1 : 7 }}</span></div>
                            <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-slate-200"><div class="h-full rounded-full bg-emerald-500" style="width: {{ $weeklyProgress / ($record->frequency === 'weekly' ? 1 : 7) * 100 }}%"></div></div>
                        </div>
                    @endif
                    <div class="mt-5 flex items-center justify-between border-t border-slate-100 pt-4">
                        <a href="{{ route('modules.edit', [$module, $record->id]) }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">✏️ {{ __('Edit entry') }}</a>
                        <div class="flex items-center gap-4">
                            @if ($module === 'schedules')
                                <form method="POST" action="{{ route('schedules.google-sync', $record->id) }}">
                                    @csrf
                                    <button type="submit" class="text-sm font-semibold text-emerald-700 hover:text-emerald-900">{{ $record->sync_status === 'synced' ? __('Sync again') : __('Sync to Google') }}</button>
                                </form>
                            @endif
                            <form method="POST" action="{{ route('modules.destroy', [$module, $record->id]) }}" onsubmit="return confirm(@js(__('Delete this entry? This cannot be undone.')));">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-sm font-medium text-slate-400 transition hover:text-rose-600">🗑️ {{ __('Delete') }}</button>
                            </form>
                        </div>
                    </div>
                </article>
            @endforeach
        </section>
        <div class="mt-6">{{ $records->links() }}</div>
    @endif
</x-app-layout>
