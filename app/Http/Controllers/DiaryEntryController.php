<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDiaryEntryRequest;
use App\Http\Requests\UpdateDiaryEntryRequest;
use App\Models\DiaryEntry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DiaryEntryController extends Controller
{
    public function index(): View
    {
        $entries = DiaryEntry::where('user_id', auth()->id())
            ->latest('entry_date')
            ->get();

        return view('diary-entries.index', compact('entries'));
    }

    public function create(): View
    {
        return view('diary-entries.create');
    }

    public function store(StoreDiaryEntryRequest $request): RedirectResponse
    {
        $entry = DiaryEntry::create([
            'user_id' => auth()->id(),
            ...$request->validated(),
        ]);

        return redirect()->route('diary-entries.index')->with('status', 'Diary entry saved.');
    }

    public function show(DiaryEntry $diaryEntry): View
    {
        $this->authorize('view', $diaryEntry);

        return view('diary-entries.show', compact('diaryEntry'));
    }

    public function edit(DiaryEntry $diaryEntry): View
    {
        $this->authorize('update', $diaryEntry);

        return view('diary-entries.edit', compact('diaryEntry'));
    }

    public function update(UpdateDiaryEntryRequest $request, DiaryEntry $diaryEntry): RedirectResponse
    {
        $this->authorize('update', $diaryEntry);

        $diaryEntry->update($request->validated());

        return redirect()->route('diary-entries.index')->with('status', 'Diary entry updated.');
    }

    public function destroy(DiaryEntry $diaryEntry): RedirectResponse
    {
        $this->authorize('delete', $diaryEntry);

        $diaryEntry->delete();

        return redirect()->route('diary-entries.index')->with('status', 'Diary entry deleted.');
    }
}
