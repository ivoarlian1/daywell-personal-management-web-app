<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\DiaryEntry;
use App\Models\Habit;
use App\Models\HabitLog;
use App\Models\MoodEntry;
use App\Models\Schedule;
use App\Models\Todo;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $today = CarbonImmutable::now($user->timezone ?? config('app.timezone'));
        $todayStart = $today->startOfDay()->utc();
        $tomorrowStart = $today->addDay()->startOfDay()->utc();
        $completedHabitIds = HabitLog::query()
            ->where('user_id', $user->id)
            ->whereDate('log_date', $today->toDateString())
            ->pluck('habit_id');

        return view('dashboard', [
            'diaryCount' => DiaryEntry::query()->whereBelongsTo($user)->count(),
            'todoCount' => Todo::query()->whereBelongsTo($user)->count(),
            'pendingTodos' => Todo::query()->whereBelongsTo($user)->where('status', '!=', 'completed')->count(),
            'completedTodos' => Todo::query()->whereBelongsTo($user)->where('status', 'completed')->count(),
            'overdueTodos' => Todo::query()->whereBelongsTo($user)
                ->where('status', '!=', 'completed')
                ->where('due_at', '<', $today->startOfDay()->utc())
                ->count(),
            'recentEntries' => DiaryEntry::query()->whereBelongsTo($user)->latest('entry_date')->limit(5)->get(),
            'todaySchedules' => Schedule::query()->whereBelongsTo($user)
                ->where('start_at', '>=', $todayStart)
                ->where('start_at', '<', $tomorrowStart)
                ->orderBy('start_at')
                ->limit(5)
                ->get(),
            'activeHabits' => Habit::query()->whereBelongsTo($user)->where('is_active', true)->orderBy('name')->limit(6)->get(),
            'completedHabitIds' => $completedHabitIds,
            'latestMood' => MoodEntry::query()->whereBelongsTo($user)->latest('entry_date')->latest('id')->first(),
            'readingBooks' => Book::query()->whereBelongsTo($user)->where('status', 'reading')->orderBy('title')->limit(4)->get(),
        ]);
    }
}
