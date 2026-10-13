<nav class="flex flex-1 flex-col gap-8 overflow-y-auto px-4 py-7">
    <div>
        <p class="px-3 text-[11px] font-bold uppercase tracking-[0.16em] text-slate-400">{{ __('Workspace') }}</p>
        <div class="mt-3 grid gap-1">
            <a href="{{ route('dashboard') }}" @click="menuOpen = false" class="{{ request()->routeIs('dashboard') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-950' }} flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold transition">
                <span class="grid size-7 place-items-center text-base">🏡</span>{{ __('Overview') }}
            </a>
            <a href="{{ route('diary-entries.index') }}" @click="menuOpen = false" class="{{ request()->routeIs('diary-entries.*') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-950' }} flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold transition">
                <span class="grid size-7 place-items-center text-base">📔</span>{{ __('My diary') }}
            </a>
            <details class="group rounded-xl" @if (request()->routeIs('modules.*') && in_array(request()->route('module'), ['journal', 'gratitude', 'mood', 'habits'], true)) open @endif>
                <summary class="{{ request()->routeIs('modules.*') && in_array(request()->route('module'), ['journal', 'gratitude', 'mood', 'habits'], true) ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-950' }} flex cursor-pointer list-none items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold transition">
                    <span class="grid size-7 place-items-center text-base">📚</span><span class="flex-1">{{ __('Journal') }}</span><span class="text-xs text-slate-400 transition group-open:rotate-90">›</span>
                </summary>
                <div class="ml-6 mt-1 grid gap-1 border-l border-indigo-100 pl-3">
                    @foreach ([
                        ['journal', '✍️', 'Daily journal'],
                        ['habits', '🌱', 'Habits'],
                        ['gratitude', '💛', 'Gratitude'],
                        ['mood', '🌈', 'Mood tracker'],
                    ] as [$journalModule, $journalIcon, $journalLabel])
                        <a href="{{ route('modules.index', $journalModule) }}" @click="menuOpen = false" class="{{ request()->route('module') === $journalModule ? 'bg-indigo-50 text-indigo-700' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }} flex items-center gap-2 rounded-lg px-3 py-2 text-xs font-semibold transition">
                            <span>{{ $journalIcon }}</span>{{ __($journalLabel) }}
                        </a>
                    @endforeach
                </div>
            </details>
            <a href="{{ route('todos.index') }}" @click="menuOpen = false" class="{{ request()->routeIs('todos.*') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-950' }} flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold transition">
                <span class="grid size-7 place-items-center text-base">✅</span>{{ __('Tasks') }}
            </a>
            <a href="{{ route('modules.index', 'schedules') }}" @click="menuOpen = false" class="{{ request()->route('module') === 'schedules' ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-950' }} flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold transition">
                <span class="grid size-7 place-items-center text-base">🗓️</span>{{ __('My schedule') }}
            </a>
            <a href="{{ route('integrations.google') }}" @click="menuOpen = false" class="{{ request()->routeIs('integrations.google*') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-950' }} flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold transition">
                <span class="grid size-7 place-items-center text-base">🔗</span>{{ __('Calendar sync') }}
            </a>
            <a href="{{ route('modules.index', 'books') }}" @click="menuOpen = false" class="{{ in_array(request()->route('module'), ['books', 'book-notes'], true) ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-950' }} flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold transition">
                <span class="grid size-7 place-items-center text-base">📖</span>{{ __('Reading list') }}
            </a>
        </div>
    </div>
    <div class="mt-auto">
        <div class="rounded-2xl bg-gradient-to-br from-indigo-600 to-violet-600 p-4 text-white">
            <p class="text-sm font-bold">{{ __('Make space for what matters.') }}</p>
            <p class="mt-1 text-xs leading-5 text-indigo-100">{{ __('Small steps turn into meaningful routines.') }}</p>
            <a href="{{ route('profile.edit') }}" class="mt-3 inline-flex rounded-lg bg-white/15 px-3 py-2 text-xs font-semibold hover:bg-white/25">⚙️ {{ __('Personalize your space') }} →</a>
            <form method="POST" action="{{ route('logout') }}" class="mt-4 border-t border-white/15 pt-3">
                @csrf
                <button class="text-xs font-semibold text-indigo-100 hover:text-white">👋 {{ __('Sign out') }}</button>
            </form>
        </div>
    </div>
</nav>
