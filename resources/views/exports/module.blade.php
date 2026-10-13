<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 92px 46px 70px; }
        body { color: #1e293b; font-family: DejaVu Sans, sans-serif; font-size: 11px; line-height: 1.55; }
        header { position: fixed; top: -66px; left: 0; right: 0; border-bottom: 2px solid #4f46e5; padding-bottom: 12px; }
        footer { position: fixed; bottom: -48px; left: 0; right: 0; border-top: 1px solid #cbd5e1; padding-top: 10px; color: #64748b; font-size: 9px; }
        h1 { margin: 0; color: #312e81; font-size: 22px; }
        .meta { margin-top: 5px; color: #64748b; }
        .entry { margin-bottom: 15px; padding: 14px 16px; border: 1px solid #e2e8f0; border-radius: 9px; page-break-inside: avoid; }
        .date { color: #4f46e5; font-size: 9px; font-weight: bold; text-transform: uppercase; letter-spacing: .7px; }
        h2 { margin: 4px 0 8px; color: #0f172a; font-size: 13px; }
        .details { white-space: pre-wrap; color: #475569; }
        .empty { margin-top: 30px; padding: 24px; border: 1px dashed #cbd5e1; border-radius: 9px; color: #64748b; text-align: center; }
        .page-number:after { content: counter(page); }
    </style>
</head>
<body>
    <header>
        <h1>{{ __($title) }}</h1>
        <div class="meta">{{ $user->name }} · {{ __('Generated') }} {{ $generatedAt->translatedFormat('j F Y g:i A') }} ({{ $generatedAt->timezoneName }})</div>
        @if (($filters['from'] ?? null) || ($filters['to'] ?? null))
            <div class="meta">{{ __('Period') }}: {{ $filters['from'] ?? __('Any date') }} {{ __('to') }} {{ $filters['to'] ?? __('Any date') }}</div>
        @endif
    </header>
    <footer>{{ __('Personal Management System') }} <span style="float:right">{{ __('Page') }} <span class="page-number"></span></span></footer>
    <main>
        @forelse ($rows as $row)
            <article class="entry">
                <div class="date">{{ $row['date'] }}</div>
                <h2>{{ $row['title'] }}</h2>
                <div class="details">{{ $row['details'] }}</div>
            </article>
        @empty
            <div class="empty">{{ __('There are no records for this export.') }}</div>
        @endforelse
    </main>
</body>
</html>
