<x-app-layout>
    @section('page-title', __('Write in your diary'))
    <div class="mx-auto max-w-3xl">
        <a href="{{ route('diary-entries.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-500 hover:text-indigo-700">← {{ __('Back to my diary') }}</a>
        <div class="mt-5 overflow-hidden rounded-3xl border border-violet-100 bg-white shadow-sm">
            <div class="bg-gradient-to-r from-violet-50 to-indigo-50 px-6 py-6 sm:px-8">
                <p class="text-xs font-bold uppercase tracking-[0.16em] text-violet-700">🌷 {{ __('A NEW PAGE') }}</p>
                <h2 class="mt-2 font-[Manrope] text-2xl font-extrabold text-slate-950">{{ __('What would you like to remember?') }}</h2>
                <p class="mt-2 text-sm text-slate-600">{{ __('Take a breath. Your thoughts are safe here.') }}</p>
            </div>
            <form method="POST" action="{{ route('diary-entries.store') }}" class="grid gap-5 p-6 sm:p-8">
                @csrf
                @if ($errors->any())
                    <div role="alert" class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">{{ __('Please review the highlighted fields.') }}</div>
                @endif
                <div>
                    <label for="entry_date" class="mb-2 block text-sm font-semibold text-slate-700">🗓️ {{ __('Entry date') }}</label>
                    <input id="entry_date" name="entry_date" type="date" value="{{ old('entry_date', now()->timezone(auth()->user()->timezone ?? config('app.timezone'))->toDateString()) }}" class="block w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                    @error('entry_date') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="title" class="mb-2 block text-sm font-semibold text-slate-700">✨ {{ __('Title') }}</label>
                    <input id="title" name="title" type="text" value="{{ old('title') }}" maxlength="255" placeholder="{{ __('A small moment, a big day...') }}" class="block w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-800 shadow-sm placeholder:text-slate-400 focus:border-indigo-500 focus:ring-indigo-500" required>
                    @error('title') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="content" class="mb-2 block text-sm font-semibold text-slate-700">💭 {{ __('Your story') }}</label>
                    <textarea id="content" name="content" rows="8" placeholder="{{ __('Write freely. What happened? How did it feel?') }}" class="block w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm leading-6 text-slate-800 shadow-sm placeholder:text-slate-400 focus:border-indigo-500 focus:ring-indigo-500" required>{{ old('content') }}</textarea>
                    @error('content') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="insights" class="mb-2 block text-sm font-semibold text-slate-700">💡 {{ __('A thought to keep (optional)') }}</label>
                    <textarea id="insights" name="insights" rows="3" placeholder="{{ __('What did you learn or notice?') }}" class="block w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm leading-6 text-slate-800 shadow-sm placeholder:text-slate-400 focus:border-indigo-500 focus:ring-indigo-500">{{ old('insights') }}</textarea>
                    @error('insights') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:justify-end">
                    <a href="{{ route('diary-entries.index') }}" class="rounded-xl border border-slate-200 px-5 py-3 text-center text-sm font-semibold text-slate-600 hover:bg-slate-50">{{ __('Cancel') }}</a>
                    <button type="submit" class="rounded-xl bg-indigo-600 px-6 py-3 text-sm font-bold text-white shadow-md shadow-indigo-200 hover:bg-indigo-700">💜 {{ __('Save diary entry') }}</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
