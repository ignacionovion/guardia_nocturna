<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\CleaningAssignment;

class CleaningAssignmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $assignments = CleaningAssignment::with(['cleaningTask', 'user'])
            ->orderBy('assigned_date', 'desc')
            ->paginate(20);
        return response()->json($assignments);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'cleaning_task_id' => 'required|exists:cleaning_tasks,id',
            'user_id' => 'required|exists:users,id',
            'assigned_date' => 'required|date',
            'status' => 'in:pending,completed',
        ]);

        $assignment = CleaningAssignment::create($validated);
        return response()->json($assignment, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $assignment = CleaningAssignment::with(['cleaningTask', 'user'])->findOrFail($id);
        return response()->json($assignment);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $assignment = CleaningAssignment::findOrFail($id);

        $validated = $request->validate([
            'status' => 'in:pending,completed',
            'assigned_date' => 'date',
        ]);

        $assignment->update($validated);
        return response()->json($assignment);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $assignment = CleaningAssignment::findOrFail($id);
        $assignment->delete();
        return response()->noContent();
    }
}
