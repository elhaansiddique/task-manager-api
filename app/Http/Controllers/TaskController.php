<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();
        $tasks = [];

        if ($user->role === 'manager') {
            // Managers can see all tasks
            $tasks = Task::with(['creator', 'assignee'])->get();
            $users = User::where('role', 'employee')->get();
        } else {
            // Employees can see tasks assigned to them
            $tasks = Task::with(['creator', 'assignee'])
                        ->where('assigned_to', $user->id)
                        ->get();
            $users = [];
        }

        return response()->json([
            'tasks' => $tasks,
            'users' => $users
        ]);
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
    public function store(Request $request)
    {
        $this->authorize('create', Task::class);

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'assigned_to' => 'required|exists:users,id',
        ]);

        $task = Task::create([
            'title' => $request->title,
            'description' => $request->description,
            'assigned_to' => $request->assigned_to,
            'created_by' => auth()->id(),
        ]);

        return response()->json($task, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Task $task)
    {
        $this->authorize('view', $task);

        return response()->json($task);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Task $task)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Task $task)
    {
        $this->authorize('update', $task);

        if (auth()->user()->role === 'manager') {
            $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'assigned_to' => 'required|exists:users,id',
            ]);

            $task->update($request->only(['title', 'description', 'assigned_to']));
        } else {
            $request->validate([
                'status' => 'required|in:pending,in_progress,completed',
            ]);

            $task->update($request->only(['status']));
        }

        return response()->json($task);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task)
    {
        $this->authorize('delete', $task);

        $task->delete();

        return response()->noContent();
    }
}
