<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Bed;
use App\Models\BedAssignment;
use App\Models\MapaBomberoUsuarioLegacy;
use App\Services\SystemEmailService;
use Illuminate\Support\Facades\Schema;

class AsignacionCamaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $assignments = BedAssignment::with(['bed', 'firefighter', 'user'])->latest()->paginate(20);
        return response()->json($assignments);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'bed_id' => 'required|exists:beds,id',
            'firefighter_id' => 'required|exists:bomberos,id',
            'notes' => 'nullable|string',
        ]);

        $bed = Bed::findOrFail($validated['bed_id']);

        if ($bed->status !== 'available') {
            return back()->withErrors(['msg' => 'La cama no está disponible.']);
        }

        // Validación: Verificar si el usuario ya tiene una cama asignada
        $existingAssignment = BedAssignment::where(function ($q) use ($validated) {
                $q->where('firefighter_id', $validated['firefighter_id']);

                if (Schema::hasColumn('bed_assignments', 'user_id')) {
                    $legacyUserId = MapaBomberoUsuarioLegacy::where('firefighter_id', $validated['firefighter_id'])->value('user_id');
                    if ($legacyUserId) {
                        $q->orWhere('user_id', $legacyUserId);
                    }
                }
            })
            ->whereNull('released_at')
            ->first();

        if ($existingAssignment) {
            return back()->withErrors(['msg' => 'Este voluntario ya tiene asignada la cama #' . $existingAssignment->bed->number]);
        }

        $data = [
            'bed_id' => $validated['bed_id'],
            'firefighter_id' => $validated['firefighter_id'],
            'notes' => $validated['notes'] ?? null,
        ];

        if (Schema::hasColumn('bed_assignments', 'user_id')) {
            $legacyUserId = MapaBomberoUsuarioLegacy::where('firefighter_id', $validated['firefighter_id'])->value('user_id');
            if ($legacyUserId) {
                $data['user_id'] = $legacyUserId;
            }
        }

        // Crear asignación
        $assignment = BedAssignment::create($data);
        $assignment->load(['bed', 'firefighter']);

        // Actualizar estado de la cama
        $bed->update(['status' => 'occupied']);

        $lines = [];
        $lines[] = 'Cama: #' . (string) ($bed->number ?? $bed->id);
        $lines[] = 'Voluntario: ' . trim((string) ($assignment->firefighter?->nombres ?? '') . ' ' . (string) ($assignment->firefighter?->apellido_paterno ?? ''));
        $lines[] = 'Notas: ' . (string) ($assignment->notes ?? '');

        SystemEmailService::send(
            type: 'beds',
            subject: 'Cama asignada',
            lines: $lines,
            actorEmail: auth()->user()?->email
        );

        return redirect()->route('camas')->with('success', 'Cama asignada correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $assignment = BedAssignment::with(['bed', 'firefighter', 'user'])->findOrFail($id);
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

             $assignment->load(['bed', 'firefighter']);
             $assignment->bed->update(['status' => 'available']);

             $lines = [];
             $lines[] = 'Cama: #' . (string) ($assignment->bed?->number ?? $assignment->bed_id);
             $lines[] = 'Voluntario: ' . trim((string) ($assignment->firefighter?->nombres ?? '') . ' ' . (string) ($assignment->firefighter?->apellido_paterno ?? ''));

             SystemEmailService::send(
                 type: 'beds',
                 subject: 'Cama liberada',
                 lines: $lines,
                 actorEmail: auth()->user()?->email
             );
             
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
