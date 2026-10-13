<x-app-layout>
    @section('page-title', __('Edit diary entry'))
    <div class="mx-auto max-w-3xl">
        <a href="{{ route('diary-entries.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-500 hover:text-indigo-700">← {{ __('Back to my diary') }}</a>
        <div class="mt-5 overflow-hidden rounded-3xl border border-violet-100 bg-white shadow-sm">
            <div class="bg-gradient-to-r from-violet-50 to-indigo-50 px-6 py-6 sm:px-8">
                <p class="text-xs font-bold uppercase tracking-[0.16em] text-violet-700">✏️ {{ __('MAKE AN UPDATE') }}</p>
                <h2 class="mt-2 font-[Manrope] text-2xl font-extrabold text-slate-950">{{ __('Edit diary entry') }}</h2>
            </div>
            <form method="POST" action="{{ route('diary-entries.update', $diaryEntry) }}" class="grid gap-5 p-6 sm:p-8">
                @csrf
                @method('PUT')
                <div>
                    <label for="entry_date" class="mb-2 block text-sm font-semibold text-slate-700">🗓️ {{ __('Entry date') }}</label>
                    <input id="entry_date" name="entry_date" type="date" value="{{ old('entry_date', $diaryEntry->entry_date->format('Y-m-d')) }}" class="block w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                    @error('entry_date') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="title" class="mb-2 block text-sm font-semibold text-slate-700">✨ {{ __('Title') }}</label>
                    <input id="title" name="title" type="text" value="{{ old('title', $diaryEntry->title) }}" maxlength="255" class="block w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                    @error('title') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="content" class="mb-2 block text-sm font-semibold text-slate-700">💭 {{ __('Your story') }}</label>
                    <textarea id="content" name="content" rows="8" class="block w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm leading-6 text-slate-800 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>{{ old('content', $diaryEntry->content) }}</textarea>
                    @error('content') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="insights" class="mb-2 block text-sm font-semibold text-slate-700">💡 {{ __('A thought to keep (optional)') }}</label>
                    <textarea id="insights" name="insights" rows="3" class="block w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm leading-6 text-slate-800 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('insights', $diaryEntry->insights) }}</textarea>
                    @error('insights') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:justify-end">
                    <a href="{{ route('diary-entries.index') }}" class="rounded-xl border border-slate-200 px-5 py-3 text-center text-sm font-semibold text-slate-600 hover:bg-slate-50">{{ __('Cancel') }}</a>
                    <button type="submit" class="rounded-xl bg-indigo-600 px-6 py-3 text-sm font-bold text-white hover:bg-indigo-700">✨ {{ __('Save changes') }}</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
