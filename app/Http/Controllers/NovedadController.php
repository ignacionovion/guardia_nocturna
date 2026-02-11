<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Novelty;
use App\Models\MapaBomberoUsuarioLegacy;

class NovedadController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $novelties = Novelty::with('user')->orderBy('date', 'desc')->paginate(20);
        return response()->json($novelties);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'type' => 'nullable|string',
            'user_id' => 'nullable|exists:users,id',
            'firefighter_id' => 'nullable|exists:bomberos,id',
            'date' => 'nullable|date',
        ]);

        try {
            $novelty = new Novelty($validated);
            if (($validated['type'] ?? null) === 'Academia') {
                $userId = $validated['user_id'] ?? null;
                $firefighterId = $validated['firefighter_id'] ?? null;

                if (!$userId && $firefighterId) {
                    $userId = MapaBomberoUsuarioLegacy::where('firefighter_id', (int) $firefighterId)->value('user_id');
                }

                $novelty->user_id = $userId ?: auth()->id();
                if ($firefighterId) {
                    $novelty->firefighter_id = (int) $firefighterId;
                }
                $novelty->date = isset($validated['date']) ? \Carbon\Carbon::parse($validated['date']) : now();
            } else {
                $novelty->user_id = auth()->id();
                $novelty->date = now();
            }
            $novelty->save();

            return back()->with('success', 'Novedad registrada correctamente.');
        } catch (\Exception $e) {
            return back()->withErrors(['msg' => 'Error al guardar la novedad: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $novelty = Novelty::with('user')->findOrFail($id);
        return response()->json($novelty);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $novelty = Novelty::findOrFail($id);

        $validated = $request->validate([
            'title' => 'string',
            'description' => 'string',
            'date' => 'date',
            'type' => 'nullable|string',
        ]);

        $novelty->update($validated);
        return response()->json($novelty);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $novelty = Novelty::findOrFail($id);
        $novelty->delete();
        return response()->noContent();
    }
}
