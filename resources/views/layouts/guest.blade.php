<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#f8fafc">
        <link rel="manifest" href="{{ route('pwa.manifest') }}">
        <link rel="icon" href="{{ asset('pwa/icon.svg') }}" type="image/svg+xml">
        <title>@yield('title', __('Welcome')) · {{ config('app.name', 'Daywell') }}</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@500;600;700;800&display=swap" rel="stylesheet">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-slate-50 font-sans text-slate-900 antialiased">
        <div class="grid min-h-screen lg:grid-cols-2">
            <aside class="relative hidden overflow-hidden bg-indigo-950 p-12 text-white lg:flex lg:flex-col lg:justify-between xl:p-16">
                <div class="absolute -right-32 -top-32 size-96 rounded-full bg-indigo-500/20 blur-3xl"></div>
                <div class="absolute -bottom-40 -left-20 size-[28rem] rounded-full bg-violet-500/20 blur-3xl"></div>
                <a href="{{ route('login') }}" class="relative z-10 flex items-center gap-3">
                    <span class="grid size-11 place-items-center rounded-2xl bg-white text-lg font-extrabold text-indigo-700">P</span>
                    <span class="font-[Manrope] text-lg font-extrabold">Daywell</span>
                </a>
                <div class="relative z-10 max-w-xl">
                    <span class="inline-flex rounded-full border border-white/15 bg-white/10 px-4 py-2 text-xs font-semibold tracking-wide text-indigo-100">🌿 {{ __('YOUR PERSONAL MANAGEMENT SPACE') }}</span>
                    <h1 class="mt-7 font-[Manrope] text-5xl font-extrabold leading-[1.12] tracking-tight xl:text-6xl">{{ __('A calmer way to make progress.') }}</h1>
                    <p class="mt-6 max-w-md text-lg leading-8 text-indigo-100/80">{{ __('Bring your plans, reflections, habits, and reading into one thoughtful space.') }}</p>
                    <div class="mt-10 grid max-w-md grid-cols-2 gap-3">
                        <div class="rounded-2xl border border-white/10 bg-white/5 p-4"><span class="text-xl">✎</span><p class="mt-3 text-sm font-semibold">{{ __('Reflect daily') }}</p></div>
                        <div class="rounded-2xl border border-white/10 bg-white/5 p-4"><span class="text-xl">◷</span><p class="mt-3 text-sm font-semibold">{{ __('Build routines') }}</p></div>
                        <div class="rounded-2xl border border-white/10 bg-white/5 p-4"><span class="text-xl">✓</span><p class="mt-3 text-sm font-semibold">{{ __('Move priorities') }}</p></div>
                        <div class="rounded-2xl border border-white/10 bg-white/5 p-4"><span class="text-xl">♡</span><p class="mt-3 text-sm font-semibold">{{ __('Notice good things') }}</p></div>
                    </div>
                </div>
                <p class="relative z-10 text-xs text-indigo-200/60">{{ __('A little more intention, every day.') }}</p>
            </aside>
            <main class="flex items-center justify-center px-5 py-10 sm:px-10">
                <div class="w-full max-w-md">
                    <form method="POST" action="{{ route('language.update') }}" class="mb-4 flex justify-end">
                        @csrf
                        <label class="sr-only" for="guest-locale">{{ __('Language') }}</label>
                        <select id="guest-locale" name="locale" onchange="this.form.submit()" class="rounded-xl border-slate-200 bg-white py-2 text-xs font-semibold text-slate-700 focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="en" @selected(app()->getLocale() === 'en')>🇺🇸 English</option>
                            <option value="id" @selected(app()->getLocale() === 'id')>🇮🇩 Indonesia</option>
                        </select>
                    </form>
                    <a href="{{ route('login') }}" class="mb-10 flex items-center justify-center gap-3 lg:hidden">
                        <span class="grid size-10 place-items-center rounded-2xl bg-indigo-600 font-extrabold text-white">P</span>
                        <span class="font-[Manrope] text-lg font-extrabold">Daywell</span>
                    </a>
                    <div class="rounded-3xl border border-slate-200/80 bg-white p-6 shadow-xl shadow-slate-200/60 sm:p-9">
                        {{ $slot }}
                    </div>
                    <p class="mt-6 text-center text-xs text-slate-400">{{ __('Your personal data stays yours.') }}</p>
                </div>
            </main>
        </div>
    </body>
</html>
