<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTodoRequest;
use App\Http\Requests\UpdateTodoRequest;
use App\Models\Todo;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TodoController extends Controller
{
    public function index(): View
    {
        $todos = Todo::where('user_id', auth()->id())
            ->latest('due_at')
            ->get();

        return view('todos.index', compact('todos'));
    }

    public function create(): View
    {
        return view('todos.create');
    }

    public function store(StoreTodoRequest $request): RedirectResponse
    {
        $todo = Todo::create([
            'user_id' => auth()->id(),
            ...$request->validated(),
        ]);

        return redirect()->route('todos.index')->with('status', 'Task created.');
    }

    public function show(Todo $todo): View
    {
        $this->authorize('view', $todo);

        return view('todos.show', compact('todo'));
    }

    public function edit(Todo $todo): View
    {
        $this->authorize('update', $todo);

        return view('todos.edit', compact('todo'));
    }

    public function update(UpdateTodoRequest $request, Todo $todo): RedirectResponse
    {
        $this->authorize('update', $todo);

        $todo->update($request->validated());

        return redirect()->route('todos.index')->with('status', 'Task updated.');
    }

    public function destroy(Todo $todo): RedirectResponse
    {
        $this->authorize('delete', $todo);

        $todo->delete();

        return redirect()->route('todos.index')->with('status', 'Task deleted.');
    }
}
