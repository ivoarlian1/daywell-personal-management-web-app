<?php

namespace App\Http\Controllers;

use App\Http\Requests\PersonalModuleRequest;
use App\Models\Habit;
use App\Models\HabitLog;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PersonalModuleController extends Controller
{
    public function index(Request $request, string $module): View
    {
        $definition = $this->definition($module);
        $query = $this->ownedQuery($module);
        $filters = ['search' => ['nullable', 'string', 'max:255']];

        if ($module === 'books') {
            $filters['status'] = ['nullable', Rule::in(['want_to_read', 'reading', 'completed'])];
        }

        if (in_array($module, ['journal', 'gratitude', 'mood', 'schedules'], true)) {
            $filters['period'] = ['nullable', 'date_format:Y-m'];
        }

        $request->validate($filters);

        foreach ($this->relationships($definition) as $relationship) {
            $query->with($relationship);
        }

        $search = trim((string) $request->input('search', ''));

        if ($search !== '') {
            $searchFields = $definition['search'];
            $query->where(function (Builder $query) use ($search, $searchFields): void {
                foreach ($searchFields as $index => $field) {
                    $method = $index === 0 ? 'where' : 'orWhere';
                    $query->{$method}($field, 'like', '%'.$search.'%');
                }
            });
        }

        if ($module === 'books' && $request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $calendarMonth = null;
        $calendarDays = [];
        $calendarEvents = collect();

        if (in_array($module, ['journal', 'gratitude', 'mood', 'schedules'], true)) {
            $period = (string) ($request->input('period') ?? '');

            if ($module === 'schedules' || $period !== '') {
                if ($period === '') {
                    $period = CarbonImmutable::now($request->user()->timezone ?? config('app.timezone'))->format('Y-m');
                }
            }

            if ($period !== '') {
                [$year, $month] = explode('-', $period);

                if ($module === 'gratitude') {
                    $query->whereHas('journalEntry', function (Builder $journalQuery) use ($year, $month): void {
                        $journalQuery->whereYear('entry_date', $year)->whereMonth('entry_date', $month);
                    });
                } elseif ($module === 'schedules') {
                    $timezone = $request->user()->timezone ?? config('app.timezone');
                    $calendarMonth = CarbonImmutable::create((int) $year, (int) $month, 1, 0, 0, 0, $timezone);
                    $query->whereBetween('start_at', [
                        $calendarMonth->startOfMonth()->utc(),
                        $calendarMonth->endOfMonth()->utc(),
                    ]);
                } else {
                    $query->whereYear('entry_date', $year)->whereMonth('entry_date', $month);
                }
            }
        }

        $query->orderBy($definition['sort'], in_array($module, ['habits', 'schedules'], true) ? 'asc' : 'desc')
            ->orderByDesc('id');

        if ($module === 'schedules') {
            $calendarRecords = (clone $query)->get();
            $calendarTimezone = $request->user()->timezone ?? config('app.timezone');
            $calendarEvents = $calendarRecords->groupBy(
                fn (Model $schedule): string => $schedule->start_at->timezone($calendarTimezone)->format('Y-m-d'),
            );
            $gridStart = $calendarMonth->startOfMonth()->startOfWeek(Carbon::MONDAY);
            $gridEnd = $calendarMonth->endOfMonth()->endOfWeek(Carbon::SUNDAY);

            for ($day = $gridStart; $day->lte($gridEnd); $day = $day->addDay()) {
                $calendarDays[] = $day;
            }
        }

        $records = $query->paginate(12)->withQueryString();
        $today = CarbonImmutable::now($request->user()->timezone ?? config('app.timezone'))->toDateString();
        $completedHabits = $module === 'habits'
            ? HabitLog::query()->where('user_id', $request->user()->id)->where('log_date', $today)->pluck('habit_id')->all()
            : [];
        $habitWeekStart = CarbonImmutable::now($request->user()->timezone ?? config('app.timezone'))->subDays(6)->toDateString();
        $weeklyHabitCounts = $module === 'habits'
            ? HabitLog::query()
                ->selectRaw('habit_id, count(distinct log_date) as completed_days')
                ->where('user_id', $request->user()->id)
                ->whereBetween('log_date', [$habitWeekStart, $today])
                ->groupBy('habit_id')
                ->pluck('completed_days', 'habit_id')
            : collect();

        return view('modules.index', [
            'module' => $module,
            'definition' => $definition,
            'records' => $records,
            'completedHabits' => $completedHabits,
            'weeklyHabitCounts' => $weeklyHabitCounts,
            'today' => $today,
            'search' => $search,
            'calendarMonth' => $calendarMonth,
            'calendarDays' => $calendarDays,
            'calendarEvents' => $calendarEvents,
        ]);
    }

    public function create(string $module): View
    {
        return view('modules.form', [
            'module' => $module,
            'definition' => $this->definition($module),
            'record' => null,
            'options' => $this->options($module),
        ]);
    }

    public function store(PersonalModuleRequest $request, string $module): RedirectResponse
    {
        $data = $this->normalize($module, $request->validated());
        $data['user_id'] = $request->user()->id;

        $this->modelClass($module)::query()->create($data);

        return redirect()->route('modules.index', $module)->with('status', $this->definition($module)['title'].' entry saved.');
    }

    public function show(Request $request, string $module, int $record): View
    {
        $definition = $this->definition($module);
        $entry = $this->ownedQuery($module)->with($this->relationships($definition))->findOrFail($record);

        if ($module === 'books') {
            $entry->load('notes');
        }

        return view('modules.show', [
            'module' => $module,
            'definition' => $definition,
            'record' => $entry,
        ]);
    }

    public function edit(Request $request, string $module, int $record): View
    {
        $definition = $this->definition($module);
        $entry = $this->ownedQuery($module)->findOrFail($record);

        return view('modules.form', [
            'module' => $module,
            'definition' => $definition,
            'record' => $entry,
            'options' => $this->options($module),
        ]);
    }

    public function update(PersonalModuleRequest $request, string $module, int $record): RedirectResponse
    {
        $entry = $this->ownedQuery($module)->findOrFail($record);
        $entry->update($this->normalize($module, $request->validated()));

        return redirect()->route('modules.index', $module)->with('status', $this->definition($module)['title'].' entry updated.');
    }

    public function destroy(Request $request, string $module, int $record): RedirectResponse
    {
        $this->ownedQuery($module)->findOrFail($record)->delete();

        return redirect()->route('modules.index', $module)->with('status', $this->definition($module)['title'].' entry deleted.');
    }

    public function toggleHabit(Request $request, int $record): RedirectResponse
    {
        $habit = $this->ownedQuery('habits')->whereKey($record)->firstOrFail();
        $today = CarbonImmutable::now($request->user()->timezone ?? config('app.timezone'))->toDateString();

        DB::transaction(function () use ($habit, $request, $today): void {
            Habit::query()->whereBelongsTo($request->user())->whereKey($habit->id)->lockForUpdate()->firstOrFail();
            $log = HabitLog::query()
                ->where('user_id', $request->user()->id)
                ->where('habit_id', $habit->id)
                ->where('log_date', $today)
                ->first();

            if ($log !== null) {
                $log->delete();

                return;
            }

            HabitLog::query()->create([
                'user_id' => $request->user()->id,
                'habit_id' => $habit->id,
                'log_date' => $today,
                'completed_at' => now(),
            ]);
        });

        return back()->with('status', 'Today’s habit check-in was updated.');
    }

    private function definition(string $module): array
    {
        $definition = config('pms.modules.'.$module);

        abort_if($definition === null, 404);

        return $definition;
    }

    /**
     * @return Builder<Model>
     */
    private function ownedQuery(string $module): Builder
    {
        return $this->modelClass($module)::query()->where('user_id', auth()->id());
    }

    /**
     * @return class-string<Model>
     */
    private function modelClass(string $module): string
    {
        return $this->definition($module)['model'];
    }

    /**
     * @return array<int, string>
     */
    private function relationships(array $definition): array
    {
        return array_values(array_unique(array_filter(array_map(
            fn (array $field): ?string => $field['with'] ?? $field['relationship'] ?? null,
            $definition['fields'],
        ))));
    }

    /**
     * @return array<string, array<int|string, string>>
     */
    private function options(string $module): array
    {
        $options = [];
        $user = request()->user();

        foreach ($this->definition($module)['fields'] as $field => $definition) {
            if (isset($definition['options'])) {
                $options[$field] = $definition['options'];
            } elseif (isset($definition['relationship'])) {
                $relationship = $definition['relationship'];
                $options[$field] = $user->{$relationship}()
                    ->orderByDesc('id')
                    ->limit(200)
                    ->get()
                    ->mapWithKeys(fn (Model $related): array => [
                        $related->getKey() => $related->{$definition['option_label']},
                    ])
                    ->all();
            }
        }

        return $options;
    }

    private function normalize(string $module, array $data): array
    {
        if ($module === 'habits') {
            $data['is_active'] = (bool) ($data['is_active'] ?? false);
        }

        if ($module === 'mood') {
            $data['entry_date'] = CarbonImmutable::parse($data['entry_date'])->startOfDay()->toDateTimeString();
        }

        if ($module === 'schedules') {
            $data['start_at'] = CarbonImmutable::parse($data['start_at'], $data['timezone'])->utc();
            $data['end_at'] = CarbonImmutable::parse($data['end_at'], $data['timezone'])->utc();
        }

        if ($module === 'books' && ($data['status'] ?? null) === 'completed' && empty($data['finished_at'])) {
            $data['finished_at'] = CarbonImmutable::now()->toDateString();
        }

        return $data;
    }
}
