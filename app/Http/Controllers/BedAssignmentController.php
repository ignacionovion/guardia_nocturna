<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Bed;
use App\Models\BedAssignment;

class BedAssignmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $assignments = BedAssignment::with(['bed', 'user'])->latest()->paginate(20);
        return response()->json($assignments);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'bed_id' => 'required|exists:beds,id',
            'user_id' => 'required|exists:users,id',
            'notes' => 'nullable|string',
        ]);

        $bed = Bed::findOrFail($validated['bed_id']);

        if ($bed->status !== 'available') {
            return back()->withErrors(['msg' => 'La cama no está disponible.']);
        }

        // Validación: Verificar si el usuario ya tiene una cama asignada
        $existingAssignment = BedAssignment::where('user_id', $validated['user_id'])
                                           ->whereNull('released_at')
                                           ->first();

        if ($existingAssignment) {
            return back()->withErrors(['msg' => 'Este voluntario ya tiene asignada la cama #' . $existingAssignment->bed->number]);
        }

        // Crear asignación
        $assignment = BedAssignment::create($validated);

        // Actualizar estado de la cama
        $bed->update(['status' => 'occupied']);

        return redirect()->route('camas')->with('success', 'Cama asignada correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $assignment = BedAssignment::with(['bed', 'user'])->findOrFail($id);
        return response()->json($assignment);
    }

    /**
     * Update the specified resource in storage.
     * Used mainly to release the bed (update released_at)
     */
    public function update(Request $request, string $id)
    {
        $assignment = BedAssignment::findOrFail($id);

        if ($request->has('release') && $request->release) {
             $assignment->update([
                 'released_at' => now(),
             ]);

             $assignment->bed->update(['status' => 'available']);
             
             return redirect()->route('camas')->with('success', 'Cama liberada correctamente.');
        }

        $validated = $request->validate([
            'notes' => 'nullable|string',
        ]);

        $assignment->update($validated);

        return redirect()->route('camas')->with('success', 'Asignación actualizada.');
    }

    public function markMaintenance(Request $request, Bed $bed)
    {
        $user = auth()->user();
        if (!$user || $user->role !== 'super_admin') {
            abort(403, 'No autorizado.');
        }

        if ($bed->status === 'occupied') {
            return back()->withErrors(['msg' => 'No puedes poner en mantención una cama ocupada.']);
        }

        $bed->update([
            'status' => 'maintenance',
            'description' => $bed->description ?: 'En mantención',
        ]);

        return redirect()->route('camas')->with('success', 'Cama marcada en mantención.');
    }

    public function markAvailable(Request $request, Bed $bed)
    {
        $user = auth()->user();
        if (!$user || $user->role !== 'super_admin') {
            abort(403, 'No autorizado.');
        }

        if ($bed->currentAssignment) {
            return back()->withErrors(['msg' => 'No puedes habilitar una cama con ocupación activa.']);
        }

        $bed->update([
            'status' => 'available',
        ]);

        return redirect()->route('camas')->with('success', 'Cama habilitada como disponible.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
         // Optional: Delete assignment history? Preferably not.
         return response()->json(['message' => 'Cannot delete assignments history.'], 403);
    }
}
