<x-app-layout>
    @section('page-title', __('My diary'))
    <section class="relative z-10 overflow-visible rounded-3xl bg-gradient-to-br from-violet-700 via-indigo-600 to-fuchsia-600 p-6 shadow-xl shadow-indigo-200/60 sm:p-8">
        <div class="flex flex-col justify-between gap-6 sm:flex-row sm:items-end">
            <div class="max-w-2xl text-white">
                <p class="text-sm font-semibold text-violet-100">📔 {{ __('A LITTLE SPACE FOR YOU') }}</p>
                <h2 class="mt-3 font-[Manrope] text-3xl font-extrabold tracking-tight sm:text-4xl">{{ __('Your diary, your story.') }}</h2>
                <p class="mt-3 text-sm leading-6 text-violet-100">{{ __('Capture the little moments, big thoughts, and lessons you want to remember.') }}</p>
            </div>
            <div class="relative z-20 flex flex-wrap items-start justify-end gap-2">
                <x-exports.form module="diary" />
                <a href="{{ route('diary-entries.create') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-white px-5 py-3 text-sm font-bold text-indigo-700 shadow-sm transition hover:-translate-y-0.5 hover:bg-violet-50">✍️ {{ __('Write a diary entry') }}</a>
            </div>
        </div>
    </section>

    <section class="mt-7">
        <div class="mb-4 flex items-end justify-between gap-3">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.16em] text-indigo-600">✨ {{ __('YOUR MEMORIES') }}</p>
                <h3 class="mt-1 font-[Manrope] text-xl font-extrabold text-slate-950">{{ __('Diary entries') }}</h3>
            </div>
            <span class="rounded-full bg-violet-50 px-3 py-1.5 text-xs font-semibold text-violet-700">{{ $entries->count() }} {{ __('entries') }}</span>
        </div>

        @if ($entries->isEmpty())
            <div class="rounded-3xl border border-dashed border-violet-200 bg-white px-6 py-14 text-center shadow-sm">
                <span class="mx-auto grid size-16 place-items-center rounded-2xl bg-violet-50 text-3xl" aria-hidden="true">🌷</span>
                <h3 class="mt-4 font-[Manrope] text-lg font-extrabold text-slate-900">{{ __('Your first page is waiting') }}</h3>
                <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-slate-500">{{ __('Start with one small moment from today. There is no perfect way to journal.') }}</p>
            </div>
        @else
            <div class="grid gap-4 md:grid-cols-2 2xl:grid-cols-3">
                @foreach ($entries as $entry)
                    <article class="group flex flex-col rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-violet-200 hover:shadow-md">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="text-xs font-semibold text-violet-600">🗓️ {{ $entry->entry_date->translatedFormat('l, j F Y') }}</p>
                                <h3 class="mt-2 truncate font-[Manrope] text-lg font-extrabold text-slate-900">{{ $entry->title }}</h3>
                            </div>
                            <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-violet-50 text-lg">💭</span>
                        </div>
                        <p class="mt-4 line-clamp-4 flex-1 whitespace-pre-line text-sm leading-6 text-slate-600">{{ $entry->content }}</p>
                        @if ($entry->insights)
                            <p class="mt-4 rounded-xl bg-amber-50 px-3 py-2 text-xs leading-5 text-amber-900">💡 <span class="font-bold">{{ __('A thought to keep:') }}</span> {{ Str::limit($entry->insights, 120) }}</p>
                        @endif
                        <div class="mt-5 flex items-center justify-between border-t border-slate-100 pt-4">
                            <a href="{{ route('diary-entries.show', $entry) }}" class="text-sm font-bold text-indigo-600 hover:text-indigo-800">{{ __('Read entry') }} →</a>
                            <div class="flex items-center gap-3">
                                <a href="{{ route('diary-entries.edit', $entry) }}" class="text-xs font-semibold text-slate-500 hover:text-indigo-700">✏️ {{ __('Edit') }}</a>
                                <form method="POST" action="{{ route('diary-entries.destroy', $entry) }}" onsubmit="return confirm(@js(__('Delete this diary entry? This cannot be undone.')));">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs font-semibold text-slate-400 hover:text-rose-600">🗑️ {{ __('Delete') }}</button>
                                </form>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </section>
</x-app-layout>
