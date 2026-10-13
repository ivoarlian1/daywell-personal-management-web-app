<x-app-layout>
    @section('page-title', __('A fresh look at your day'))
    <section class="overflow-hidden rounded-3xl bg-gradient-to-br from-indigo-700 via-indigo-600 to-violet-600 p-6 text-white shadow-xl shadow-indigo-200/60 sm:p-9">
        <div class="grid items-center gap-7 md:grid-cols-[1fr_auto]">
            <div>
                <p class="text-sm font-semibold text-indigo-100">☀️ {{ __('YOUR PERSONAL OVERVIEW') }}</p>
                <h2 class="mt-3 max-w-xl font-[Manrope] text-3xl font-extrabold tracking-tight sm:text-4xl">{{ __('Good to see you, :name.', ['name' => Auth::user()->name]) }}</h2>
                <p class="mt-3 max-w-lg text-sm leading-6 text-indigo-100 sm:text-base">{{ __('A little reflection, one clear priority, and a routine you can keep. Make today yours.') }}</p>
                <div class="mt-6 flex flex-wrap gap-3">
                    <a href="{{ route('diary-entries.create') }}" class="rounded-xl bg-white px-4 py-3 text-sm font-bold text-indigo-700 shadow-sm transition hover:bg-indigo-50">✍️ {{ __('Write in your diary') }} <span aria-hidden="true">→</span></a>
                </div>
            </div>
            <div class="hidden size-40 place-items-center rounded-full border border-white/20 bg-white/10 text-7xl md:grid" aria-hidden="true">☀</div>
        </div>
    </section>
    <section class="mt-7 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ([
            [__('Diary entries'), $diaryCount, __('Your reflections, collected'), '✎', 'bg-violet-50 text-violet-700'],
            [__('All tasks'), $todoCount, __('Ideas into action'), '✓', 'bg-indigo-50 text-indigo-700'],
            [__('To do'), $pendingTodos, __('One step at a time'), '◷', 'bg-amber-50 text-amber-700'],
            [__('Completed'), $completedTodos, __('Progress worth celebrating'), '↗', 'bg-emerald-50 text-emerald-700'],
        ] as [$label, $value, $caption, $icon, $tone])
            <article class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm">
                <div class="flex items-start justify-between">
                    <div><p class="text-sm font-medium text-slate-500">{{ $label }}</p><p class="mt-3 font-[Manrope] text-3xl font-extrabold tracking-tight">{{ $value }}</p></div>
                    <span class="grid size-11 place-items-center rounded-2xl {{ $tone }} text-lg font-bold">{{ $icon }}</span>
                </div>
                <p class="mt-3 text-xs text-slate-400">{{ $caption }}</p>
            </article>
        @endforeach
    </section>
    <section class="mt-7 grid gap-5 xl:grid-cols-[1.3fr_0.7fr]">
        <article class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6">
            <div class="flex items-center justify-between gap-3">
                <div><p class="text-xs font-bold uppercase tracking-widest text-indigo-600">{{ __('Look back') }}</p><h2 class="mt-1 font-[Manrope] text-lg font-extrabold">📔 {{ __('Recent diary entries') }}</h2></div>
                <a href="{{ route('diary-entries.index') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-700">{{ __('See all') }} →</a>
            </div>
            @if ($recentEntries->isEmpty())
                <div class="mt-6 rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-5 py-8 text-center">
                    <span class="text-3xl" aria-hidden="true">✎</span><p class="mt-3 font-semibold text-slate-800">{{ __('Your first page is waiting') }}</p><p class="mt-1 text-sm text-slate-500">{{ __('Capture a moment, a thought, or something you learned.') }}</p>
                    <a href="{{ route('diary-entries.create') }}" class="mt-4 inline-flex rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">✍️ {{ __('Write an entry') }}</a>
                </div>
            @else
                <ul class="mt-5 divide-y divide-slate-100">
                    @foreach ($recentEntries as $entry)
                        <li class="flex gap-4 py-4 first:pt-0 last:pb-0">
                            <span class="grid size-11 shrink-0 place-items-center rounded-2xl bg-violet-50 text-lg text-violet-700">✎</span>
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center justify-between gap-2"><a href="{{ route('diary-entries.show', $entry) }}" class="truncate font-semibold text-slate-800 hover:text-indigo-700">{{ $entry->title }}</a><time class="text-xs text-slate-400">{{ $entry->entry_date->translatedFormat('j M Y') }}</time></div>
                                <p class="mt-1 line-clamp-2 text-sm leading-6 text-slate-500">{{ Str::limit($entry->content, 180) }}</p>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </article>
        <article class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6">
            <p class="text-xs font-bold uppercase tracking-widest text-indigo-600">{{ __('Make a little progress') }}</p>
            <h2 class="mt-1 font-[Manrope] text-lg font-extrabold">✨ {{ __('Quick actions') }}</h2>
            <div class="mt-5 grid gap-3">
                <a href="{{ route('todos.index') }}" class="group flex items-center gap-3 rounded-2xl border border-slate-100 p-3 transition hover:border-indigo-200 hover:bg-indigo-50/50">
                    <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-indigo-50 text-indigo-700">✓</span>
                    <span class="min-w-0 flex-1"><span class="block text-sm font-semibold text-slate-800">{{ __('Plan your next task') }}</span><span class="mt-0.5 block text-xs text-slate-500">{{ __('Keep the important things moving') }}</span></span><span class="text-slate-300 transition group-hover:text-indigo-600">→</span>
                </a>
                <a href="{{ route('diary-entries.create') }}" class="group flex items-center gap-3 rounded-2xl border border-slate-100 p-3 transition hover:border-violet-200 hover:bg-violet-50/50">
                    <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-violet-50 text-violet-700">✎</span>
                    <span class="min-w-0 flex-1"><span class="block text-sm font-semibold text-slate-800">{{ __('Take a moment to reflect') }}</span><span class="mt-0.5 block text-xs text-slate-500">{{ __('A thought worth remembering') }}</span></span><span class="text-slate-300 transition group-hover:text-violet-600">→</span>
                </a>
                <a href="{{ route('profile.edit') }}" class="group flex items-center gap-3 rounded-2xl border border-slate-100 p-3 transition hover:border-slate-200 hover:bg-slate-50">
                    <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-slate-100 text-slate-700">⚙</span>
                    <span class="min-w-0 flex-1"><span class="block text-sm font-semibold text-slate-800">{{ __('Personalize your space') }}</span><span class="mt-0.5 block text-xs text-slate-500">{{ __('Profile and preferences') }}</span></span><span class="text-slate-300 transition group-hover:text-slate-600">→</span>
                </a>
            </div>
        </article>
    </section>
    <section class="mt-7 grid gap-5 xl:grid-cols-2">
        <article class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6">
            <div class="flex items-center justify-between gap-3">
                <div><p class="text-xs font-bold uppercase tracking-widest text-indigo-600">{{ __('Plan ahead') }}</p><h2 class="mt-1 font-[Manrope] text-lg font-extrabold">🗓️ {{ __('Today’s schedule') }}</h2></div>
                <a href="{{ route('modules.index', 'schedules') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-700">{{ __('Open calendar') }} →</a>
            </div>
            @if ($todaySchedules->isEmpty())
                <p class="mt-5 rounded-xl bg-slate-50 px-4 py-5 text-sm text-slate-500">{{ __('No activities planned for today. Add a little structure when you’re ready.') }}</p>
            @else
                <ul class="mt-4 divide-y divide-slate-100">
                    @foreach ($todaySchedules as $schedule)
                        <li class="flex gap-3 py-3 first:pt-0">
                            <span class="mt-1 h-10 w-1 shrink-0 rounded-full bg-indigo-400"></span>
                            <div class="min-w-0 flex-1"><a href="{{ route('modules.show', ['schedules', $schedule->id]) }}" class="font-semibold text-slate-800 hover:text-indigo-700">{{ $schedule->title }}</a><p class="mt-1 text-xs text-slate-500">{{ $schedule->start_at->timezone($schedule->timezone)->format('g:i A') }} – {{ $schedule->end_at->timezone($schedule->timezone)->format('g:i A') }} · {{ $schedule->timezone }}</p></div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </article>
        <article class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6">
            <div class="flex items-center justify-between gap-3">
                <div><p class="text-xs font-bold uppercase tracking-widest text-indigo-600">{{ __('Steady steps') }}</p><h2 class="mt-1 font-[Manrope] text-lg font-extrabold">🌱 {{ __('Habits for today') }}</h2></div>
                <a href="{{ route('modules.index', 'habits') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-700">{{ __('See habits') }} →</a>
            </div>
            @if ($activeHabits->isEmpty())
                <p class="mt-5 rounded-xl bg-slate-50 px-4 py-5 text-sm text-slate-500">{{ __('Start with one habit that helps you feel like yourself.') }}</p>
            @else
                <ul class="mt-4 grid gap-2">
                    @foreach ($activeHabits as $habit)
                        <li class="flex items-center gap-3 rounded-xl border border-slate-100 px-3 py-2.5">
                            <form method="POST" action="{{ route('modules.habits.toggle', $habit->id) }}">
                                @csrf
                                <button type="submit" aria-label="{{ $completedHabitIds->contains($habit->id) ? __('Undo habit check-in for :name', ['name' => $habit->name]) : __('Check off :name', ['name' => $habit->name]) }}" class="{{ $completedHabitIds->contains($habit->id) ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500 hover:bg-indigo-100 hover:text-indigo-700' }} grid size-8 place-items-center rounded-lg font-bold">{{ $completedHabitIds->contains($habit->id) ? '✓' : '○' }}</button>
                            </form>
                            <span class="min-w-0 flex-1 truncate text-sm font-semibold text-slate-700">{{ $habit->name }}</span>
                            <span class="text-xs text-slate-400">{{ __($habit->frequency) }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </article>
    </section>
    <section class="mt-5 grid gap-5 md:grid-cols-2">
        <article class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6">
            <p class="text-xs font-bold uppercase tracking-widest text-indigo-600">{{ __('Check in with yourself') }}</p>
            <h2 class="mt-1 font-[Manrope] text-lg font-extrabold">🌈 {{ __('Latest mood') }}</h2>
            @if ($latestMood)
                <div class="mt-4 flex items-center gap-4"><span class="grid size-14 place-items-center rounded-2xl bg-amber-50 text-2xl">{{ ['1' => '☁', '2' => '◔', '3' => '◉', '4' => '☀', '5' => '✦'][$latestMood->mood_score] ?? '◉' }}</span><div><p class="font-semibold text-slate-800">{{ __(['1' => 'Low', '2' => 'A little low', '3' => 'Okay', '4' => 'Good', '5' => 'Great'][$latestMood->mood_score] ?? 'Mood check-in') }}</p><p class="mt-1 text-xs text-slate-400">{{ $latestMood->entry_date->translatedFormat('j F Y') }} · {{ __('personal reflection, not a diagnosis') }}</p></div></div>
            @else
                <p class="mt-4 text-sm text-slate-500">{{ __('No mood check-in yet. There’s no right or wrong way to begin.') }}</p>
            @endif
            <a href="{{ route('modules.index', 'mood') }}" class="mt-4 inline-flex text-sm font-semibold text-indigo-600 hover:text-indigo-700">{{ __('Open mood tracker') }} →</a>
        </article>
        <article class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6">
            <div class="flex items-center justify-between gap-3"><div><p class="text-xs font-bold uppercase tracking-widest text-indigo-600">{{ __('Keep turning pages') }}</p><h2 class="mt-1 font-[Manrope] text-lg font-extrabold">📖 {{ __('Currently reading') }}</h2></div><a href="{{ route('modules.index', 'books') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-700">{{ __('Reading list') }} →</a></div>
            @if ($readingBooks->isEmpty())
                <p class="mt-4 text-sm text-slate-500">{{ __('Add a book when you’re ready for your next chapter.') }}</p>
            @else
                <ul class="mt-4 grid gap-3">
                    @foreach ($readingBooks as $book)
                        <li><div class="flex justify-between gap-3 text-sm"><span class="truncate font-semibold text-slate-800">{{ $book->title }}</span><span class="shrink-0 text-xs text-slate-400">{{ $book->total_pages ? round($book->current_page / $book->total_pages * 100) : $book->current_page.' pages' }}{{ $book->total_pages ? '%' : '' }}</span></div>@if ($book->total_pages)<div class="mt-2 h-1.5 overflow-hidden rounded-full bg-slate-100"><div class="h-full rounded-full bg-violet-500" style="width: {{ min(100, round($book->current_page / $book->total_pages * 100)) }}%"></div></div>@endif</li>
                    @endforeach
                </ul>
            @endif
        </article>
    </section>
</x-app-layout>
