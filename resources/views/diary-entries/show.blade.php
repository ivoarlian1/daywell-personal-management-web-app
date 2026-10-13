<x-app-layout>
    @section('page-title', $diaryEntry->title)
    <article class="mx-auto max-w-3xl overflow-hidden rounded-3xl border border-violet-100 bg-white shadow-sm">
        <header class="bg-gradient-to-r from-violet-50 to-indigo-50 px-6 py-6 sm:px-8">
            <a href="{{ route('diary-entries.index') }}" class="text-sm font-semibold text-indigo-700 hover:text-indigo-900">← {{ __('Back to my diary') }}</a>
            <p class="mt-6 text-xs font-bold uppercase tracking-[0.16em] text-violet-700">📔 {{ __('A moment to remember') }}</p>
            <h2 class="mt-2 font-[Manrope] text-3xl font-extrabold text-slate-950">{{ $diaryEntry->title }}</h2>
            <time class="mt-3 block text-sm text-slate-600">{{ $diaryEntry->entry_date->translatedFormat('l, j F Y') }}</time>
        </header>
        <div class="space-y-6 px-6 py-7 sm:px-8">
            <p class="whitespace-pre-line text-base leading-8 text-slate-700">{{ $diaryEntry->content }}</p>
            @if ($diaryEntry->insights)
                <aside class="rounded-2xl bg-amber-50 p-4 text-sm leading-6 text-amber-950">
                    <h3 class="font-bold">💡 {{ __('A thought to keep:') }}</h3>
                    <p class="mt-1 whitespace-pre-line">{{ $diaryEntry->insights }}</p>
                </aside>
            @endif
            <footer class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-5">
                <a href="{{ route('diary-entries.index') }}" class="text-sm font-semibold text-slate-600 hover:text-indigo-700">← {{ __('All diary entries') }}</a>
                <div class="flex items-center gap-4">
                    <a href="{{ route('diary-entries.edit', $diaryEntry) }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">✏️ {{ __('Edit') }}</a>
                </div>
            </footer>
        </div>
    </article>
</x-app-layout>
