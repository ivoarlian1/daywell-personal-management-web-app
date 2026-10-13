<?php

namespace App\Http\Controllers;

use App\Models\User;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;

class GoogleOAuthController extends Controller
{
    private const CALENDAR_SCOPE = 'https://www.googleapis.com/auth/calendar.events';

    public function redirectToLogin(): RedirectResponse
    {
        abort_unless($this->hasClientCredentials() && filled(config('services.google.redirect')), 503, 'Google sign-in is not configured.');

        return Socialite::driver('google')
            ->scopes(['openid', 'email', 'profile'])
            ->redirect();
    }

    public function handleLoginCallback(): RedirectResponse
    {
        if (! $this->hasClientCredentials()) {
            return redirect()->route('login')->with('oauth_error', 'Google sign-in is not configured.');
        }

        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (GuzzleException|InvalidStateException $exception) {
            Log::warning('Google sign-in callback failed.', ['exception' => class_basename($exception)]);

            return redirect()->route('login')->with('oauth_error', 'Google sign-in could not be completed. Please try again.');
        }

        $email = strtolower((string) $googleUser->getEmail());

        if ($email === '' || ! data_get($googleUser->user, 'verified_email', false)) {
            return redirect()->route('login')->with('oauth_error', 'Google did not provide a verified email address.');
        }

        $user = User::query()->where('google_id', $googleUser->getId())->first();
        $emailUser = User::query()->where('email', $email)->first();

        if ($user === null && $emailUser?->google_id !== null && $emailUser->google_id !== $googleUser->getId()) {
            return redirect()->route('login')->with('oauth_error', 'This email is linked to a different Google account.');
        }

        $user ??= $emailUser;

        if ($user === null) {
            $user = User::query()->create([
                'name' => $googleUser->getName() ?: $email,
                'email' => $email,
                'password' => Hash::make(Str::random(64)),
                'email_verified_at' => now(),
                'google_id' => $googleUser->getId(),
                'avatar' => $googleUser->getAvatar(),
            ]);
        } else {
            $user->forceFill([
                'google_id' => $googleUser->getId(),
                'avatar' => $googleUser->getAvatar(),
                'email_verified_at' => $user->email_verified_at ?? now(),
            ])->save();
        }

        Auth::login($user);
        request()->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function redirectToCalendar(Request $request): RedirectResponse
    {
        abort_unless($this->hasClientCredentials() && filled(config('services.google.calendar_redirect')), 503, 'Google Calendar is not configured.');

        $request->session()->put('google_oauth_purpose', 'calendar');

        return Socialite::driver('google')
            ->redirectUrl(config('services.google.calendar_redirect'))
            ->scopes(['openid', 'email', 'profile', self::CALENDAR_SCOPE])
            ->with(['access_type' => 'offline', 'prompt' => 'consent'])
            ->redirect();
    }

    public function handleCalendarCallback(Request $request): RedirectResponse
    {
        abort_unless($this->hasClientCredentials() && filled(config('services.google.calendar_redirect')), 503, 'Google Calendar is not configured.');

        if ($request->session()->pull('google_oauth_purpose') !== 'calendar') {
            return redirect()->route('integrations.google')->with('error', 'The Google Calendar connection expired. Please try again.');
        }

        try {
            $googleUser = Socialite::driver('google')
                ->redirectUrl(config('services.google.calendar_redirect'))
                ->user();
        } catch (GuzzleException|InvalidStateException $exception) {
            Log::warning('Google Calendar authorization callback failed.', [
                'user_id' => $request->user()->id,
                'exception' => class_basename($exception),
            ]);

            return redirect()->route('integrations.google')->with('error', 'Google Calendar could not be connected. Please try again.');
        }

        $request->user()->googleIntegrations()->updateOrCreate(
            ['provider' => 'google'],
            [
                'google_account_email' => $googleUser->getEmail(),
                'encrypted_access_token' => $googleUser->token,
                'encrypted_refresh_token' => $googleUser->refreshToken,
                'token_expires_at' => now()->addSeconds($googleUser->expiresIn ?? 3600),
                'scopes' => ['openid', 'email', 'profile', self::CALENDAR_SCOPE],
                'calendar_id' => 'primary',
                'status' => 'connected',
            ],
        );

        return redirect()->route('integrations.google')->with('status', 'Google Calendar is connected.');
    }

    private function hasClientCredentials(): bool
    {
        return filled(config('services.google.client_id')) && filled(config('services.google.client_secret'));
    }
}
