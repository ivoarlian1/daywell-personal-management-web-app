<x-app-layout>
    @section('page-title', __($definition['title']))
    <div class="mx-auto max-w-3xl">
        <a href="{{ route('modules.index', $module) }}" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-500 hover:text-indigo-700">← {{ __('Back to') }} {{ strtolower(__($definition['title'])) }}</a>
        <article class="mt-5 rounded-3xl border border-slate-200/80 bg-white p-6 shadow-sm sm:p-9">
            <div class="flex flex-wrap items-start justify-between gap-4 border-b border-slate-100 pb-6">
                <div>
                    <p class="text-xs font-bold uppercase tracking-widest text-indigo-600">{{ __($definition['title']) }}</p>
                    <h2 class="mt-2 font-[Manrope] text-2xl font-extrabold text-slate-950">
                        {{ $record->title ?? ($record->name ?? ($record->entry_date?->translatedFormat('j F Y') ?? __($record->note_type ?? 'Entry'))) }}
                    </h2>
                    @if ($module === 'books')
                        <p class="mt-2 text-sm text-slate-500">{{ $record->author }}</p>
                    @endif
                </div>
                <a href="{{ route('modules.edit', [$module, $record->id]) }}" class="rounded-xl bg-indigo-50 px-4 py-2.5 text-sm font-bold text-indigo-700 hover:bg-indigo-100">✏️ {{ __('Edit entry') }}</a>
            </div>
            <dl class="mt-6 grid gap-5 sm:grid-cols-2">
                @foreach ($definition['fields'] as $name => $field)
                    @continue(in_array($name, ['notes', 'content', 'description']))
                    <div>
                        <dt class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ __($field['label']) }}</dt>
                        <dd class="mt-1 text-sm font-medium text-slate-800">
                            @if (isset($field['options'][$record->getAttribute($name)]))
                                {{ __($field['options'][$record->getAttribute($name)]) }}
                            @elseif (isset($field['relationship']))
                                {{ $record->{$field['relationship']}?->{$field['option_label']} ?? '—' }}
                            @else
                                {{ $record->getAttribute($name) ?: '—' }}
                            @endif
                        </dd>
                    </div>
                @endforeach
            </dl>
            @foreach (['description' => 'Description', 'notes' => 'Reflection', 'content' => 'Entry'] as $attribute => $label)
                @if ($record->getAttribute($attribute))
                    <section class="mt-7 rounded-2xl bg-slate-50 p-5">
                        <h3 class="text-sm font-bold text-slate-700">{{ __($label) }}</h3>
                        <p class="mt-3 whitespace-pre-wrap text-sm leading-7 text-slate-700">{{ $record->getAttribute($attribute) }}</p>
                    </section>
                @endif
            @endforeach
            @if ($module === 'books' && $record->notes->isNotEmpty())
                <section class="mt-8">
                    <h3 class="font-[Manrope] text-lg font-extrabold">{{ __('Notes and quotes') }}</h3>
                    <div class="mt-4 grid gap-3">
                        @foreach ($record->notes as $note)
                            <div class="rounded-2xl border border-slate-200 p-4">
                                <p class="text-xs font-bold uppercase text-indigo-600">{{ __($note->note_type) }}@if ($note->page_number) · {{ __('page') }} {{ $note->page_number }}@endif</p>
                                @if ($note->title)<h4 class="mt-2 font-semibold">{{ $note->title }}</h4>@endif
                                <p class="mt-2 whitespace-pre-wrap text-sm leading-6 text-slate-600">{{ $note->content }}</p>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif
        </article>
    </div>
</x-app-layout>
