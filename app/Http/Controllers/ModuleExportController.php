<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\DiaryEntry;
use App\Models\GratitudeItem;
use App\Models\HabitLog;
use App\Models\JournalEntry;
use App\Models\MoodEntry;
use App\Models\Schedule;
use App\Models\Todo;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ModuleExportController extends Controller
{
    public function download(Request $request, string $module): Response
    {
        $filters = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $rows = match ($module) {
            'diary' => $this->diaryRows($request, $filters),
            'journal' => $this->journalRows($request, $filters),
            'todos' => $this->todoRows($request, $filters),
            'habits' => $this->habitRows($request, $filters),
            'gratitude' => $this->gratitudeRows($request, $filters),
            'mood' => $this->moodRows($request, $filters),
            'schedules' => $this->scheduleRows($request, $filters),
            'books' => $this->bookRows($request),
            default => abort(404),
        };

        $title = [
            'diary' => 'Diary entries',
            'journal' => 'Journal entries',
            'todos' => 'Task list',
            'habits' => 'Habit tracker',
            'gratitude' => 'Gratitude journal',
            'mood' => 'Mood history',
            'schedules' => 'Schedule',
            'books' => 'Reading list and notes',
        ][$module];
        $date = CarbonImmutable::now($request->user()->timezone ?? config('app.timezone'))->format('Y-m-d');
        $filename = 'pms-'.str_replace('_', '-', $module).'-'.$date.'.pdf';

        return Pdf::loadView('exports.module', [
            'title' => $title,
            'user' => $request->user(),
            'rows' => $rows,
            'filters' => $filters,
            'generatedAt' => CarbonImmutable::now($request->user()->timezone ?? config('app.timezone')),
        ])->setPaper('a4')->download($filename);
    }

    private function diaryRows(Request $request, array $filters): array
    {
        return $this->dateFiltered(
            DiaryEntry::query()->whereBelongsTo($request->user()),
            'entry_date',
            $filters,
        )->orderBy('entry_date')->get(['entry_date', 'title', 'content', 'insights'])->map(fn (DiaryEntry $entry): array => [
            'date' => $entry->entry_date->format('Y-m-d'),
            'title' => $entry->title,
            'details' => $entry->content.($entry->insights ? "\n\nInsight: ".$entry->insights : ''),
        ])->all();
    }

    private function todoRows(Request $request, array $filters): array
    {
        return $this->dateTimeFiltered(
            Todo::query()->whereBelongsTo($request->user()),
            'due_at',
            $filters,
            $request->user()->timezone ?? config('app.timezone'),
        )->orderByRaw('due_at IS NULL')->orderBy('due_at')->orderBy('title')
            ->get(['title', 'description', 'priority', 'status', 'due_at'])
            ->map(fn (Todo $todo): array => [
                'date' => $todo->due_at?->timezone($request->user()->timezone ?? config('app.timezone'))->format('Y-m-d') ?? 'No due date',
                'title' => $todo->title.' · '.ucfirst(str_replace('_', ' ', $todo->status)),
                'details' => 'Priority: '.ucfirst($todo->priority).($todo->description ? "\n".$todo->description : ''),
            ])->all();
    }

    private function journalRows(Request $request, array $filters): array
    {
        return $this->dateFiltered(
            JournalEntry::query()->whereBelongsTo($request->user()),
            'entry_date',
            $filters,
        )->orderBy('entry_date')->get(['entry_date', 'notes'])->map(fn (JournalEntry $entry): array => [
            'date' => $entry->entry_date->format('Y-m-d'),
            'title' => 'Journal entry',
            'details' => $entry->notes ?: 'No reflection added',
        ])->all();
    }

    private function habitRows(Request $request, array $filters): array
    {
        return $this->dateFiltered(
            HabitLog::query()->where('user_id', $request->user()->id)->with('habit'),
            'log_date',
            $filters,
        )->orderBy('log_date')->get()->map(fn (HabitLog $log): array => [
            'date' => $log->log_date,
            'title' => $log->habit?->name ?? 'Habit',
            'details' => $log->notes ?: 'Completed check-in',
        ])->all();
    }

    private function gratitudeRows(Request $request, array $filters): array
    {
        $query = GratitudeItem::query()->whereBelongsTo($request->user())->with('journalEntry');

        if (isset($filters['from']) || isset($filters['to'])) {
            $query->whereHas('journalEntry', function (Builder $journalQuery) use ($filters): void {
                if (isset($filters['from'])) {
                    $journalQuery->whereDate('entry_date', '>=', $filters['from']);
                }

                if (isset($filters['to'])) {
                    $journalQuery->whereDate('entry_date', '<=', $filters['to']);
                }
            });
        }

        return $query->orderBy('journal_entry_id')->get()->map(fn (GratitudeItem $item): array => [
            'date' => $item->journalEntry?->entry_date?->format('Y-m-d') ?? '',
            'title' => 'A gratitude note',
            'details' => $item->content,
        ])->all();
    }

    private function moodRows(Request $request, array $filters): array
    {
        return $this->dateFiltered(
            MoodEntry::query()->whereBelongsTo($request->user()),
            'entry_date',
            $filters,
        )->orderBy('entry_date')->get(['entry_date', 'mood_score', 'notes'])->map(fn (MoodEntry $entry): array => [
            'date' => $entry->entry_date->format('Y-m-d'),
            'title' => 'Mood '.$entry->mood_score.' of 5',
            'details' => $entry->notes ?: 'No note added',
        ])->all();
    }

    private function scheduleRows(Request $request, array $filters): array
    {
        return $this->dateTimeFiltered(
            Schedule::query()->whereBelongsTo($request->user()),
            'start_at',
            $filters,
            $request->user()->timezone ?? config('app.timezone'),
        )->orderBy('start_at')->get(['title', 'description', 'start_at', 'end_at', 'timezone', 'location'])
            ->map(fn (Schedule $schedule): array => [
                'date' => $schedule->start_at->timezone($schedule->timezone)->format('Y-m-d g:i A'),
                'title' => $schedule->title,
                'details' => $schedule->description.($schedule->location ? "\nLocation: ".$schedule->location : '')."\nEnds ".$schedule->end_at->timezone($schedule->timezone)->format('g:i A').' ('.$schedule->timezone.')',
            ])->all();
    }

    private function bookRows(Request $request): array
    {
        return Book::query()->whereBelongsTo($request->user())->with('notes')->orderBy('title')->get()
            ->map(fn (Book $book): array => [
                'date' => ucfirst(str_replace('_', ' ', $book->status)),
                'title' => $book->title.($book->author ? ' — '.$book->author : ''),
                'details' => 'Progress: '.$book->current_page.($book->total_pages ? ' of '.$book->total_pages.' pages' : ' pages')
                    .($book->description ? "\n".$book->description : '')
                    .($book->notes->isNotEmpty() ? "\n\nNotes:\n".$book->notes->map(fn ($note): string => ($note->note_type === 'quote' ? '“'.$note->content.'”' : $note->content).($note->page_number ? ' (p. '.$note->page_number.')' : ''))->implode("\n\n") : ''),
            ])->all();
    }

    /**
     * @param  Builder<Model>  $query
     * @param  array{from?: string, to?: string}  $filters
     * @return Builder<Model>
     */
    private function dateFiltered(Builder $query, string $column, array $filters): Builder
    {
        if (isset($filters['from'])) {
            $query->whereDate($column, '>=', $filters['from']);
        }

        if (isset($filters['to'])) {
            $query->whereDate($column, '<=', $filters['to']);
        }

        return $query;
    }

    /**
     * @param  Builder<Model>  $query
     * @param  array{from?: string, to?: string}  $filters
     * @return Builder<Model>
     */
    private function dateTimeFiltered(Builder $query, string $column, array $filters, string $timezone): Builder
    {
        if (isset($filters['from'])) {
            $query->where($column, '>=', CarbonImmutable::parse($filters['from'], $timezone)->startOfDay()->utc());
        }

        if (isset($filters['to'])) {
            $query->where($column, '<', CarbonImmutable::parse($filters['to'], $timezone)->addDay()->startOfDay()->utc());
        }

        return $query;
    }
}
