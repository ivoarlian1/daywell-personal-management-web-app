<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LocaleController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'locale' => ['required', Rule::in(['en', 'id'])],
        ]);

        $request->session()->put('locale', $validated['locale']);

        if ($request->user() !== null) {
            $request->user()->update(['locale' => $validated['locale']]);
        }

        return back()->with('status', $validated['locale'] === 'id'
            ? 'Bahasa tampilan diubah ke Bahasa Indonesia.'
            : 'Display language changed to English.');
    }
}
