<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\CleaningTask;

class TareaAseoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tasks = CleaningTask::all();
        return response()->json($tasks);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:cleaning_tasks,name',
            'description' => 'nullable|string',
        ]);

        $task = CleaningTask::create($validated);
        return response()->json($task, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $task = CleaningTask::with('assignments.user')->findOrFail($id);
        return response()->json($task);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $task = CleaningTask::findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'string|unique:cleaning_tasks,name,' . $id,
            'description' => 'nullable|string',
        ]);

        $task->update($validated);
        return response()->json($task);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $task = CleaningTask::findOrFail($id);
        $task->delete();
        return response()->noContent();
    }
}
