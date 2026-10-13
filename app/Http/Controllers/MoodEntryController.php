<?php

namespace App\Http\Controllers;

use App\Models\MoodEntry;
use App\Http\Requests\StoreMoodEntryRequest;
use App\Http\Requests\UpdateMoodEntryRequest;

class MoodEntryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreMoodEntryRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(MoodEntry $moodEntry)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(MoodEntry $moodEntry)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateMoodEntryRequest $request, MoodEntry $moodEntry)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MoodEntry $moodEntry)
    {
        //
    }
}
