<?php

use App\Models\Book;
use App\Models\BookNote;
use App\Models\GratitudeItem;
use App\Models\Habit;
use App\Models\JournalEntry;
use App\Models\MoodEntry;
use App\Models\Schedule;

return [
    'modules' => [
        'journal' => [
            'model' => JournalEntry::class,
            'title' => 'Journal',
            'description' => 'A quiet place to capture your thoughts and reflections.',
            'sort' => 'entry_date',
            'search' => ['notes'],
            'fields' => [
                'entry_date' => ['label' => 'Entry date', 'type' => 'date', 'rules' => ['required', 'date']],
                'notes' => ['label' => 'Your reflection', 'type' => 'textarea', 'rules' => ['nullable', 'string', 'max:10000']],
            ],
        ],
        'gratitude' => [
            'model' => GratitudeItem::class,
            'title' => 'Gratitude journal',
            'description' => 'Save the small and meaningful things you are grateful for.',
            'sort' => 'created_at',
            'search' => ['content'],
            'fields' => [
                'journal_entry_id' => ['label' => 'Journal day', 'type' => 'select', 'relationship' => 'journalEntries', 'option_label' => 'entry_date', 'rules' => ['required', 'integer']],
                'content' => ['label' => 'I am grateful for', 'type' => 'textarea', 'rules' => ['required', 'string', 'max:5000']],
            ],
        ],
        'mood' => [
            'model' => MoodEntry::class,
            'title' => 'Mood tracker',
            'description' => 'Notice how you feel over time. This is a personal reflection tool, not a medical assessment.',
            'sort' => 'entry_date',
            'search' => ['notes'],
            'fields' => [
                'entry_date' => ['label' => 'Date', 'type' => 'date', 'rules' => ['required', 'date']],
                'mood_score' => ['label' => 'How are you feeling? (1 = low, 5 = great)', 'type' => 'select', 'options' => [1 => '1 · Low', 2 => '2 · A little low', 3 => '3 · Okay', 4 => '4 · Good', 5 => '5 · Great'], 'rules' => ['required', 'integer', 'between:1,5']],
                'notes' => ['label' => 'A note to yourself (optional)', 'type' => 'textarea', 'rules' => ['nullable', 'string', 'max:5000']],
            ],
        ],
        'habits' => [
            'model' => Habit::class,
            'title' => 'Habit tracker',
            'description' => 'Choose a small routine and check in when you complete it.',
            'sort' => 'name',
            'search' => ['name', 'description'],
            'fields' => [
                'name' => ['label' => 'Habit name', 'type' => 'text', 'rules' => ['required', 'string', 'max:255']],
                'description' => ['label' => 'Why it matters (optional)', 'type' => 'textarea', 'rules' => ['nullable', 'string', 'max:2000']],
                'frequency' => ['label' => 'Frequency', 'type' => 'select', 'options' => ['daily' => 'Daily', 'weekly' => 'Weekly'], 'rules' => ['required', 'in:daily,weekly']],
                'is_active' => ['label' => 'Habit is active', 'type' => 'checkbox', 'rules' => ['sometimes', 'boolean']],
            ],
        ],
        'schedules' => [
            'model' => Schedule::class,
            'title' => 'My schedule',
            'description' => 'Plan focused sessions and everyday activities.',
            'sort' => 'start_at',
            'search' => ['title', 'description', 'location'],
            'fields' => [
                'title' => ['label' => 'Activity', 'type' => 'text', 'rules' => ['required', 'string', 'max:255']],
                'description' => ['label' => 'Description (optional)', 'type' => 'textarea', 'rules' => ['nullable', 'string', 'max:5000']],
                'start_at' => ['label' => 'Starts', 'type' => 'datetime-local', 'rules' => ['required', 'date']],
                'end_at' => ['label' => 'Ends', 'type' => 'datetime-local', 'rules' => ['required', 'date', 'after:start_at']],
                'timezone' => ['label' => 'Time zone', 'type' => 'text', 'rules' => ['required', 'timezone']],
                'location' => ['label' => 'Location (optional)', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:255']],
                'todo_id' => ['label' => 'Related task (optional)', 'type' => 'select', 'relationship' => 'todos', 'with' => 'todo', 'option_label' => 'title', 'nullable' => true, 'rules' => ['nullable', 'integer']],
            ],
        ],
        'books' => [
            'model' => Book::class,
            'title' => 'Reading list',
            'description' => 'Keep your collection, reading progress, and next book in view.',
            'sort' => 'title',
            'search' => ['title', 'author', 'isbn'],
            'fields' => [
                'title' => ['label' => 'Book title', 'type' => 'text', 'rules' => ['required', 'string', 'max:255']],
                'author' => ['label' => 'Author (optional)', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:255']],
                'isbn' => ['label' => 'ISBN (optional)', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:32']],
                'description' => ['label' => 'Description (optional)', 'type' => 'textarea', 'rules' => ['nullable', 'string', 'max:5000']],
                'total_pages' => ['label' => 'Total pages (optional)', 'type' => 'number', 'rules' => ['nullable', 'integer', 'min:1', 'max:100000']],
                'current_page' => ['label' => 'Current page', 'type' => 'number', 'rules' => ['required', 'integer', 'min:0', 'max:100000']],
                'status' => ['label' => 'Reading status', 'type' => 'select', 'options' => ['want_to_read' => 'Want to read', 'reading' => 'Currently reading', 'completed' => 'Completed'], 'rules' => ['required', 'in:want_to_read,reading,completed']],
                'started_at' => ['label' => 'Started on (optional)', 'type' => 'date', 'rules' => ['nullable', 'date']],
                'finished_at' => ['label' => 'Finished on (optional)', 'type' => 'date', 'rules' => ['nullable', 'date', 'after_or_equal:started_at']],
                'rating' => ['label' => 'Your rating (1–5, optional)', 'type' => 'number', 'rules' => ['nullable', 'integer', 'between:1,5']],
            ],
        ],
        'book-notes' => [
            'model' => BookNote::class,
            'title' => 'Book notes & quotes',
            'description' => 'Save ideas, quotations, and page references from your books.',
            'sort' => 'created_at',
            'search' => ['title', 'content'],
            'fields' => [
                'book_id' => ['label' => 'Book', 'type' => 'select', 'relationship' => 'books', 'option_label' => 'title', 'rules' => ['required', 'integer']],
                'note_type' => ['label' => 'Entry type', 'type' => 'select', 'options' => ['note' => 'Note', 'quote' => 'Quote', 'summary' => 'Summary'], 'rules' => ['required', 'in:note,quote,summary']],
                'title' => ['label' => 'Title (optional)', 'type' => 'text', 'rules' => ['nullable', 'string', 'max:255']],
                'page_number' => ['label' => 'Page (optional)', 'type' => 'number', 'rules' => ['nullable', 'integer', 'min:1']],
                'content' => ['label' => 'Note or quote', 'type' => 'textarea', 'rules' => ['required', 'string', 'max:10000']],
            ],
        ],
    ],
];
