<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\StaffEvent;

class EventoPersonalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $events = StaffEvent::with(['user', 'replacementUser'])->orderBy('start_date', 'desc')->paginate(20);
        return response()->json($events);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'type' => 'required|in:replacement,permission,license,academy',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'description' => 'nullable|string',
            'replacement_user_id' => 'nullable|exists:users,id',
        ]);

        $event = StaffEvent::create($validated);
        return response()->json($event, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $event = StaffEvent::with(['user', 'replacementUser'])->findOrFail($id);
        return response()->json($event);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $event = StaffEvent::findOrFail($id);

        $validated = $request->validate([
            'start_date' => 'date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'description' => 'nullable|string',
            'status' => 'in:pending,approved,rejected',
            'replacement_user_id' => 'nullable|exists:users,id',
        ]);

        $event->update($validated);
        return response()->json($event);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $event = StaffEvent::findOrFail($id);
        $event->delete();
        return response()->noContent();
    }
}
