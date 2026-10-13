<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DiaryEntryController;
use App\Http\Controllers\GoogleCalendarController;
use App\Http\Controllers\GoogleOAuthController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\ModuleExportController;
use App\Http\Controllers\PersonalModuleController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TodoController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }

    return redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/auth/google/redirect', [GoogleOAuthController::class, 'redirectToLogin'])->middleware('throttle:10,1')->name('google.login.redirect');
    Route::get('/auth/google/callback', [GoogleOAuthController::class, 'handleLoginCallback'])->middleware('throttle:20,1')->name('google.login.callback');
});

Route::get('/manifest.webmanifest', function () {
    $manifest = file_get_contents(public_path('manifest.webmanifest'));
    abort_if($manifest === false, 500, 'PWA manifest could not be read.');

    return response($manifest)->header('Content-Type', 'application/manifest+json');
})->name('pwa.manifest');

Route::get('/service-worker.js', function () {
    $script = file_get_contents(public_path('service-worker.js'));
    abort_if($script === false, 500, 'Service worker could not be read.');

    return response($script)->withHeaders([
        'Content-Type' => 'application/javascript',
        'Cache-Control' => 'no-cache',
        'Service-Worker-Allowed' => '/',
    ]);
})->name('pwa.service-worker');

Route::post('/language', [LocaleController::class, 'update'])->name('language.update');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::resource('diary-entries', DiaryEntryController::class);
    Route::resource('todos', TodoController::class);
    Route::get('/exports/{module}', [ModuleExportController::class, 'download'])
        ->whereIn('module', ['diary', 'journal', 'todos', 'habits', 'gratitude', 'mood', 'schedules', 'books'])
        ->name('exports.download');
    Route::get('/integrations/google', [GoogleCalendarController::class, 'show'])->name('integrations.google');
    Route::get('/integrations/google/redirect', [GoogleOAuthController::class, 'redirectToCalendar'])->middleware('throttle:10,1')->name('integrations.google.redirect');
    Route::get('/integrations/google/callback', [GoogleOAuthController::class, 'handleCalendarCallback'])->middleware('throttle:20,1')->name('integrations.google.callback');
    Route::delete('/integrations/google', [GoogleCalendarController::class, 'disconnect'])->name('integrations.google.disconnect');
    Route::post('/schedules/{schedule}/google-sync', [GoogleCalendarController::class, 'sync'])
        ->whereNumber('schedule')
        ->middleware('throttle:30,1')
        ->name('schedules.google-sync');

    Route::prefix('modules/{module}')
        ->where(['module' => 'journal|gratitude|mood|habits|schedules|books|book-notes'])
        ->name('modules.')
        ->controller(PersonalModuleController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/{record}', 'show')->whereNumber('record')->name('show');
            Route::get('/{record}/edit', 'edit')->whereNumber('record')->name('edit');
            Route::put('/{record}', 'update')->whereNumber('record')->name('update');
            Route::delete('/{record}', 'destroy')->whereNumber('record')->name('destroy');
        });

    Route::post('/modules/habits/{record}/today', [PersonalModuleController::class, 'toggleHabit'])
        ->whereNumber('record')
        ->name('modules.habits.toggle');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
