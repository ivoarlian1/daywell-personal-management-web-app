<?php

namespace App\Providers;

use App\Models\DiaryEntry;
use App\Models\JournalEntry;
use App\Models\Todo;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(DiaryEntry::class, \App\Policies\DiaryEntryPolicy::class);
        Gate::policy(JournalEntry::class, \App\Policies\JournalEntryPolicy::class);
        Gate::policy(Todo::class, \App\Policies\TodoPolicy::class);
    }
}
