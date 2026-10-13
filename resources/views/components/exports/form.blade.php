@props(['module', 'dateRange' => true])

<details class="relative z-20">
    <summary class="inline-flex cursor-pointer list-none items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">📄 {{ __('Export PDF') }}</summary>
    <form method="GET" action="{{ route('exports.download', $module) }}" class="absolute right-0 z-30 mt-2 w-80 max-w-[calc(100vw-2rem)] rounded-2xl border border-slate-200 bg-white p-4 text-left text-slate-800 shadow-xl sm:w-96">
        @if ($dateRange)
            <p class="mb-3 text-xs leading-5 text-slate-600">{{ __('Choose an optional date range, or leave it empty to export all records.') }}</p>
            <label class="block text-xs font-semibold text-slate-700" for="export-from-{{ $module }}">{{ __('From') }}</label>
            <input id="export-from-{{ $module }}" type="date" name="from" value="{{ request('from') }}" class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 focus:border-indigo-500 focus:ring-indigo-500">
            <label class="mt-3 block text-xs font-semibold text-slate-700" for="export-to-{{ $module }}">{{ __('To') }}</label>
            <input id="export-to-{{ $module }}" type="date" name="to" value="{{ request('to') }}" class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 focus:border-indigo-500 focus:ring-indigo-500">
        @endif
        <button type="submit" class="mt-4 w-full rounded-lg bg-indigo-600 px-3 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">{{ __('Download PDF') }}</button>
    </form>
</details>
