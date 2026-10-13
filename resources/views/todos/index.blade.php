<x-app-layout>
    @section('page-title', __('Tasks'))
    <section class="relative z-10 flex flex-col justify-between gap-5 overflow-visible rounded-3xl bg-gradient-to-br from-emerald-700 via-teal-600 to-cyan-600 p-6 shadow-xl shadow-emerald-100 sm:flex-row sm:items-end sm:p-8">
        <div class="text-white">
            <p class="text-sm font-semibold text-emerald-100">✅ {{ __('MAKE ROOM FOR WHAT MATTERS') }}</p>
            <h2 class="mt-3 font-[Manrope] text-3xl font-extrabold tracking-tight sm:text-4xl">{{ __('Your tasks, one step at a time.') }}</h2>
            <p class="mt-3 max-w-xl text-sm leading-6 text-emerald-50">{{ __('Keep your priorities close and celebrate every bit of progress.') }}</p>
        </div>
        <div class="relative z-20 flex flex-wrap items-start justify-end gap-2">
            <x-exports.form module="todos" />
            <a href="{{ route('todos.create') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-white px-5 py-3 text-sm font-bold text-emerald-800 shadow-sm transition hover:-translate-y-0.5 hover:bg-emerald-50">➕ {{ __('New task') }}</a>
        </div>
    </section>

    <section class="mt-7">
        <div class="mb-4 flex items-end justify-between gap-3">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.16em] text-emerald-700">🎯 {{ __('YOUR PRIORITIES') }}</p>
                <h3 class="mt-1 font-[Manrope] text-xl font-extrabold text-slate-950">{{ __('Task list') }}</h3>
            </div>
            <span class="rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700">{{ $todos->count() }} {{ __('tasks') }}</span>
        </div>

        @if ($todos->isEmpty())
            <div class="rounded-3xl border border-dashed border-emerald-200 bg-white px-6 py-14 text-center shadow-sm">
                <span class="mx-auto grid size-16 place-items-center rounded-2xl bg-emerald-50 text-3xl" aria-hidden="true">🌱</span>
                <h3 class="mt-4 font-[Manrope] text-lg font-extrabold text-slate-900">{{ __('A fresh start!') }}</h3>
                <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-slate-500">{{ __('Add one priority and give yourself a clear next step.') }}</p>
            </div>
        @else
            <div class="grid gap-3">
                @foreach ($todos as $todo)
                    <article class="flex flex-col gap-4 rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm transition hover:border-emerald-200 hover:shadow-md sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex min-w-0 items-start gap-4">
                            <span class="{{ $todo->status === 'completed' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-50 text-amber-700' }} grid size-11 shrink-0 place-items-center rounded-2xl text-lg">{{ $todo->status === 'completed' ? '🎉' : '📝' }}</span>
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="font-[Manrope] font-extrabold text-slate-900">{{ $todo->title }}</h3>
                                    <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">{{ __(ucfirst(str_replace('_', ' ', $todo->status))) }}</span>
                                </div>
                                @if ($todo->description)
                                    <p class="mt-1 text-sm leading-6 text-slate-600">{{ $todo->description }}</p>
                                @endif
                                <div class="mt-2 flex flex-wrap items-center gap-3 text-xs text-slate-500">
                                    <span>⚡ {{ __('Priority: :priority', ['priority' => __(ucfirst($todo->priority))]) }}</span>
                                    @if ($todo->due_at)
                                        <span>📅 {{ __('Due: :date', ['date' => $todo->due_at->translatedFormat('j M Y')]) }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="flex shrink-0 items-center gap-4 border-t border-slate-100 pt-3 sm:border-0 sm:pt-0">
                            <a href="{{ route('todos.edit', $todo) }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">✏️ {{ __('Edit') }}</a>
                            <form method="POST" action="{{ route('todos.destroy', $todo) }}" onsubmit="return confirm(@js(__('Delete this task? This cannot be undone.')));">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-sm font-semibold text-slate-400 hover:text-rose-600">🗑️ {{ __('Delete') }}</button>
                            </form>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </section>
</x-app-layout>
