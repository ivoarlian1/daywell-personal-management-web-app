<x-app-layout>
    @section('page-title', ($record ? __('Edit') : __('New')).' '.__($definition['title']))
    <div class="mx-auto max-w-3xl">
        <a href="{{ route('modules.index', $module) }}" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-500 hover:text-indigo-700">← {{ __('Back to') }} {{ strtolower(__($definition['title'])) }}</a>
        <div class="mt-5 rounded-3xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-8">
            <div class="mb-7">
                <p class="text-xs font-bold uppercase tracking-widest text-indigo-600">{{ $record ? __('MAKE AN UPDATE') : __('A NEW PAGE') }}</p>
                <h2 class="mt-2 font-[Manrope] text-2xl font-extrabold text-slate-950">{{ $record ? __('Edit entry') : __('Add to :module', ['module' => __($definition['title'])]) }}</h2>
                <p class="mt-2 text-sm text-slate-500">{{ __($definition['description']) }}</p>
            </div>
            @if ($errors->any())
                <div role="alert" class="mb-6 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                    <p class="font-bold">{{ __('Please review the highlighted fields.') }}</p>
                    <ul class="mt-2 list-inside list-disc">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif
            <form method="POST" action="{{ $record ? route('modules.update', [$module, $record->id]) : route('modules.store', $module) }}" class="grid gap-5">
                @csrf
                @if ($record)
                    @method('PUT')
                @endif
                @foreach ($definition['fields'] as $name => $field)
                    @php
                        $defaultValue = match ($name) {
                            'timezone' => auth()->user()->timezone ?: config('app.timezone'),
                            'entry_date' => now()->timezone(auth()->user()->timezone ?: config('app.timezone'))->toDateString(),
                            default => null,
                        };
                        $value = old($name, $record?->getAttribute($name) ?? $defaultValue);
                        if ($field['type'] === 'datetime-local' && $value) {
                            $timezone = old('timezone', $record?->timezone ?? (auth()->user()->timezone ?: config('app.timezone')));
                            $value = \Illuminate\Support\Carbon::parse($value)->timezone($timezone)->format('Y-m-d\TH:i');
                        } elseif ($field['type'] === 'date' && $value) {
                            $value = \Illuminate\Support\Carbon::parse($value)->format('Y-m-d');
                        }
                    @endphp
                    @if ($field['type'] === 'checkbox')
                        <label class="flex items-center gap-3 rounded-xl border border-slate-200 p-4">
                            <input type="checkbox" name="{{ $name }}" value="1" @checked(old($name, $record?->getAttribute($name) ?? true)) class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="text-sm font-semibold text-slate-700">{{ __($field['label']) }}</span>
                        </label>
                    @else
                        <div>
                            <label for="{{ $name }}" class="mb-2 block text-sm font-semibold text-slate-700">{{ __($field['label']) }}</label>
                            @if ($field['type'] === 'textarea')
                                <textarea id="{{ $name }}" name="{{ $name }}" rows="5" class="block w-full rounded-xl border-slate-200 px-4 py-3 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ $value }}</textarea>
                            @elseif ($field['type'] === 'select')
                                <select id="{{ $name }}" name="{{ $name }}" @required(! ($field['nullable'] ?? false)) class="block w-full rounded-xl border-slate-200 px-4 py-3 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @if ($field['nullable'] ?? false)
                                        <option value="">{{ __('No linked task') }}</option>
                                    @endif
                                    @foreach ($options[$name] ?? [] as $optionValue => $optionLabel)
                                        <option value="{{ $optionValue }}" @selected((string) $value === (string) $optionValue)>{{ __($optionLabel) }}</option>
                                    @endforeach
                                </select>
                            @else
                                <input id="{{ $name }}" type="{{ $field['type'] }}" name="{{ $name }}" value="{{ $value }}" @required(! ($field['nullable'] ?? false)) @if ($field['type'] === 'number') min="0" @endif class="block w-full rounded-xl border-slate-200 px-4 py-3 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @endif
                            @error($name)<p class="mt-2 text-sm font-medium text-rose-600">{{ $message }}</p>@enderror
                        </div>
                    @endif
                @endforeach
                <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:justify-end">
                    <a href="{{ route('modules.index', $module) }}" class="rounded-xl border border-slate-200 px-5 py-3 text-center text-sm font-semibold text-slate-600 hover:bg-slate-50">{{ __('Cancel') }}</a>
                    <button type="submit" class="rounded-xl bg-indigo-600 px-6 py-3 text-sm font-bold text-white shadow-md shadow-indigo-200 hover:bg-indigo-700">{{ $record ? __('Save changes') : __('Save entry') }}</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
