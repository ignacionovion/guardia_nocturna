<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Shift;
use App\Models\ShiftUser;

class TurnoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $shifts = Shift::with(['leader', 'users.user'])->orderBy('date', 'desc')->paginate(10);
        return response()->json($shifts);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'shift_leader_id' => 'nullable|exists:users,id',
            'status' => 'in:active,closed',
            'notes' => 'nullable|string',
        ]);

        $shift = Shift::create($validated);
        return response()->json($shift, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $shift = Shift::with(['leader', 'users.user'])->findOrFail($id);
        return response()->json($shift);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $shift = Shift::findOrFail($id);

        $validated = $request->validate([
            'shift_leader_id' => 'nullable|exists:users,id',
            'status' => 'in:active,closed',
            'notes' => 'nullable|string',
        ]);

        $shift->update($validated);
        return response()->json($shift);
    }

    /**
     * Add a user to the shift.
     */
    public function addUser(Request $request, string $id)
    {
        $shift = Shift::findOrFail($id);
        
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'nullable|string',
            'present' => 'boolean',
        ]);

        $shiftUser = ShiftUser::create([
            'shift_id' => $shift->id,
            'user_id' => $validated['user_id'],
            'role' => $validated['role'] ?? null,
            'present' => $validated['present'] ?? true,
        ]);

        return response()->json($shiftUser, 201);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $shift = Shift::findOrFail($id);
        $shift->delete();
        return response()->noContent();
    }
}
