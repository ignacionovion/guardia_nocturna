<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Reminder;

class ReminderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $reminders = Reminder::with('creator')
            ->orderBy('remind_at', 'asc')
            ->where('remind_at', '>=', now())
            ->get();
        return response()->json($reminders);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'description' => 'nullable|string',
            'remind_at' => 'required|date',
            'type' => 'string|in:general,anniversary',
            'created_by' => 'required|exists:users,id',
        ]);

        $reminder = Reminder::create($validated);
        return response()->json($reminder, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $reminder = Reminder::with('creator')->findOrFail($id);
        return response()->json($reminder);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $reminder = Reminder::findOrFail($id);

        $validated = $request->validate([
            'title' => 'string',
            'description' => 'nullable|string',
            'remind_at' => 'date',
            'type' => 'string|in:general,anniversary',
        ]);

        $reminder->update($validated);
        return response()->json($reminder);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $reminder = Reminder::findOrFail($id);
        $reminder->delete();
        return response()->noContent();
    }
}
