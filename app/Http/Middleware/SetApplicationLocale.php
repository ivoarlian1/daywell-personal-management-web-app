<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetApplicationLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->user()?->locale
            ?? $request->session()->get('locale', config('app.fallback_locale'));

        $locale = in_array($locale, ['en', 'id'], true) ? $locale : config('app.fallback_locale');

        App::setLocale($locale);
        Carbon::setLocale($locale);

        return $next($request);
    }
}
