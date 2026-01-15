<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Bed;

class BedController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $beds = Bed::with('currentAssignment.user')->get();
        return response()->json($beds);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'number' => 'required|string|unique:beds,number',
            'status' => 'in:available,occupied,maintenance',
            'description' => 'nullable|string',
        ]);

        $bed = Bed::create($validated);
        return response()->json($bed, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $bed = Bed::with(['assignments' => function($query) {
            $query->orderBy('created_at', 'desc')->limit(5);
        }, 'assignments.user'])->findOrFail($id);
        
        return response()->json($bed);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $bed = Bed::findOrFail($id);
        
        $validated = $request->validate([
            'status' => 'in:available,occupied,maintenance',
            'description' => 'nullable|string',
        ]);

        $bed->update($validated);
        return response()->json($bed);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $bed = Bed::findOrFail($id);
        $bed->delete();
        return response()->noContent();
    }
}
