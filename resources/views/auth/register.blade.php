<x-guest-layout>
    <div class="mb-7">
        <p class="text-sm font-semibold text-indigo-600">✨ {{ __('START WITH A FRESH PAGE') }}</p>
        <h2 class="mt-2 font-[Manrope] text-3xl font-extrabold tracking-tight text-slate-950">{{ __('Create your space') }}</h2>
        <p class="mt-2 text-sm leading-6 text-slate-500">{{ __('One place for the plans and moments that matter.') }}</p>
    </div>
    <form method="POST" action="{{ route('register') }}">
        @csrf
        <div>
            <x-input-label for="name" :value="__('Your name')" class="mb-2 block text-sm font-semibold text-slate-700" />
            <x-text-input id="name" class="block w-full rounded-xl border-slate-200 px-4 py-3 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email address')" class="mb-2 block text-sm font-semibold text-slate-700" />
            <x-text-input id="email" class="block w-full rounded-xl border-slate-200 px-4 py-3 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" class="mb-2 block text-sm font-semibold text-slate-700" />
            <x-text-input id="password" class="block w-full rounded-xl border-slate-200 px-4 py-3 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm password')" class="mb-2 block text-sm font-semibold text-slate-700" />
            <x-text-input id="password_confirmation" class="block w-full rounded-xl border-slate-200 px-4 py-3 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>
        <button type="submit" class="mt-7 flex w-full items-center justify-center gap-2 rounded-xl bg-indigo-600 px-5 py-3.5 text-sm font-bold text-white shadow-lg shadow-indigo-200 transition hover:-translate-y-0.5 hover:bg-indigo-700 focus:outline-none focus:ring-4 focus:ring-indigo-200">{{ __('Create account') }} <span aria-hidden="true">→</span></button>
    </form>
    <p class="mt-6 border-t border-slate-100 pt-6 text-center text-sm text-slate-500">{{ __('Already have an account?') }} <a href="{{ route('login') }}" class="font-bold text-indigo-600 hover:text-indigo-700">{{ __('Sign in') }}</a></p>
</x-guest-layout>
