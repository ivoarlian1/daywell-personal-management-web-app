<x-guest-layout>
    <div class="mb-8">
        <p class="text-sm font-semibold text-indigo-600">🌷 {{ __('WELCOME BACK') }}</p>
        <h2 class="mt-2 font-[Manrope] text-3xl font-extrabold tracking-tight text-slate-950">{{ __('Sign in to your space') }}</h2>
        <p class="mt-2 text-sm leading-6 text-slate-500">{{ __('Pick up where you left off and make today count.') }}</p>
    </div>
    <x-auth-session-status class="mb-5 rounded-xl bg-emerald-50 px-4 py-3 text-sm text-emerald-700" :status="session('status')" />
    @if (session('oauth_error'))
        <div role="alert" class="mb-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">{{ session('oauth_error') }}</div>
    @endif
    <form method="POST" action="{{ route('login') }}">
        @csrf
        <div>
            <x-input-label for="email" :value="__('Email address')" class="mb-2 block text-sm font-semibold text-slate-700" />
            <x-text-input id="email" class="block w-full rounded-xl border-slate-200 px-4 py-3.5 text-sm shadow-sm placeholder:text-slate-400 focus:border-indigo-500 focus:ring-indigo-500" type="email" name="email" :value="old('email')" placeholder="you@example.com" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>
        <div class="mt-5">
            <x-input-label for="password" :value="__('Password')" class="mb-2 block text-sm font-semibold text-slate-700" />
            <x-text-input id="password" class="block w-full rounded-xl border-slate-200 px-4 py-3.5 text-sm placeholder:text-slate-400 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" type="password" name="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>
        <div class="mt-5 flex items-center justify-between gap-3">
            <label for="remember_me" class="inline-flex items-center gap-2">
                <input id="remember_me" type="checkbox" class="rounded border-slate-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
                <span class="text-sm text-slate-600">{{ __('Remember me') }}</span>
            </label>
            @if (Route::has('password.request'))
                <a class="text-sm font-semibold text-indigo-600 hover:text-indigo-700" href="{{ route('password.request') }}">{{ __('Forgot password?') }}</a>
            @endif
        </div>
        <button type="submit" class="mt-7 flex w-full items-center justify-center gap-2 rounded-xl bg-indigo-600 px-5 py-3.5 text-sm font-bold text-white shadow-lg shadow-indigo-200 transition hover:-translate-y-0.5 hover:bg-indigo-700 focus:outline-none focus:ring-4 focus:ring-indigo-200">{{ __('Sign in') }} <span aria-hidden="true">→</span></button>
    </form>
    @if (filled(config('services.google.client_id')) && filled(config('services.google.redirect')))
        <div class="my-6 flex items-center gap-3 text-xs text-slate-400"><span class="h-px flex-1 bg-slate-100"></span>{{ __('OR CONTINUE WITH') }}<span class="h-px flex-1 bg-slate-100"></span></div>
        <a href="{{ route('google.login.redirect') }}" class="flex w-full items-center justify-center gap-3 rounded-xl border border-slate-200 px-5 py-3.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"><span class="font-bold text-rose-500">G</span> {{ __('Continue with Google') }}</a>
    @endif
    <p class="mt-7 border-t border-slate-100 pt-6 text-center text-sm text-slate-500">{{ __('New to Daywell?') }} <a href="{{ route('register') }}" class="font-bold text-indigo-600 hover:text-indigo-700">{{ __('Create an account') }}</a></p>
</x-guest-layout>
