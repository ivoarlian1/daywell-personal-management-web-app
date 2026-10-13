<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#f8fafc">
        <link rel="manifest" href="{{ route('pwa.manifest') }}">
        <link rel="icon" href="{{ asset('pwa/icon.svg') }}" type="image/svg+xml">
        <title>{{ trim($__env->yieldContent('title', $__env->yieldContent('page-title', __('Overview')))) }} · {{ config('app.name', 'Daywell') }}</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@500;600;700;800&display=swap" rel="stylesheet">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-slate-50 font-sans text-slate-900 antialiased">
        <div class="min-h-screen" x-data="{ menuOpen: false }">
            <div class="fixed inset-y-0 left-0 z-40 hidden w-64 border-r border-slate-200 bg-white lg:flex lg:flex-col">
                <a href="{{ route('dashboard') }}" class="flex h-20 items-center gap-3 border-b border-slate-100 px-7">
                    <span class="grid size-10 place-items-center rounded-2xl bg-indigo-600 text-lg font-extrabold text-white">P</span>
                    <span>
                        <span class="block font-[Manrope] text-sm font-extrabold tracking-tight">Daywell</span>
                        <span class="block text-xs text-slate-400">{{ __('your personal space') }}</span>
                    </span>
                </a>
                @include('layouts.sidebar')
                <div class="mt-auto border-t border-slate-100 p-5">
                    <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 rounded-2xl p-2 transition hover:bg-slate-50">
                        <span class="grid size-10 place-items-center rounded-full bg-indigo-100 font-bold text-indigo-700">{{ mb_substr(Auth::user()->name, 0, 1) }}</span>
                        <span class="min-w-0 flex-1">
                            <span class="block truncate text-sm font-semibold">{{ Auth::user()->name }}</span>
                            <span class="block truncate text-xs text-slate-400">{{ __('Account settings') }}</span>
                        </span>
                        <span class="text-slate-400">↗</span>
                    </a>
                </div>
            </div>
            <div x-show="menuOpen" x-cloak class="fixed inset-0 z-50 bg-slate-950/30 lg:hidden" @click="menuOpen = false">
                <aside class="flex h-full w-72 flex-col bg-white shadow-2xl" @click.stop>
                    <div class="flex h-20 items-center justify-between border-b border-slate-100 px-6">
                        <a href="{{ route('dashboard') }}" class="font-[Manrope] font-extrabold">Daywell</a>
                        <button type="button" class="rounded-xl px-3 py-2 text-slate-500 hover:bg-slate-100" @click="menuOpen = false" aria-label="Close menu">✕</button>
                    </div>
                    @include('layouts.sidebar')
                </aside>
            </div>
            <div class="lg:pl-64">
                <header class="sticky top-0 z-30 border-b border-slate-200/80 bg-white/90 backdrop-blur">
                    <div class="mx-auto flex h-16 max-w-7xl items-center justify-between gap-4 px-4 sm:px-7">
                        <div class="flex items-center gap-3">
                            <button type="button" class="grid size-10 place-items-center rounded-xl border border-slate-200 text-slate-600 lg:hidden" @click="menuOpen = true" aria-label="Open menu">☰</button>
                            <div>
                                <p class="text-xs font-medium text-slate-400">{{ now()->timezone(Auth::user()->timezone ?? config('app.timezone'))->translatedFormat('l, j F Y') }}</p>
                                <h1 class="font-[Manrope] text-sm font-bold text-slate-800 sm:text-base">@yield('page-title', __('Your day, in balance'))</h1>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <form method="POST" action="{{ route('language.update') }}">
                                @csrf
                                <label class="sr-only" for="app-locale">{{ __('Language') }}</label>
                                <select id="app-locale" name="locale" onchange="this.form.submit()" class="rounded-xl border-slate-200 bg-white py-2 text-xs font-semibold text-slate-700 focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="en" @selected(app()->getLocale() === 'en')>🇺🇸 English</option>
                                    <option value="id" @selected(app()->getLocale() === 'id')>🇮🇩 Indonesia</option>
                                </select>
                            </form>
                            <span class="grid size-10 place-items-center rounded-full bg-indigo-100 font-bold text-indigo-700 lg:hidden">{{ mb_substr(Auth::user()->name, 0, 1) }}</span>
                        </div>
                    </div>
                </header>
                <main class="mx-auto max-w-7xl px-4 py-7 sm:px-7 sm:py-9">
                    @if (session('status'))
                        <div role="status" class="mb-6 flex items-center gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
                            <span class="grid size-6 place-items-center rounded-full bg-emerald-100">✓</span>{{ __(session('status')) }}
                        </div>
                    @endif
                    @if (session('error'))
                        <div role="alert" class="mb-6 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-800">{{ __(session('error')) }}</div>
                    @endif
                    {{ $slot }}
                </main>
                <footer class="px-4 pb-8 text-center text-xs text-slate-400 sm:px-7">{{ __('A little more intention, every day.') }}</footer>
            </div>
        </div>
    </body>
</html>
