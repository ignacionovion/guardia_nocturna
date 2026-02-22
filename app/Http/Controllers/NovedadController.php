<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Novelty;
use App\Models\MapaBomberoUsuarioLegacy;
use App\Services\SystemEmailService;

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
            'guardia_id' => 'nullable|exists:guardias,id',
            'is_permanent' => 'nullable|boolean',
        ]);

        try {
            $novelty = new Novelty($validated);
            
            // Si es Academia, manejar los campos específicos
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
                // Las academias NO van en la bitácora de novedades, se manejan aparte
            } else {
                // Es una novedad regular (no academia)
                $novelty->user_id = auth()->id();
                $novelty->date = now();
                
                // Asignar guardia_id si es una cuenta de guardia
                $authUser = auth()->user();
                if ($authUser->role === 'guardia' && $authUser->guardia_id) {
                    $novelty->guardia_id = $authUser->guardia_id;
                }
                
                // Si es novedad permanente, cualquier usuario puede crearla pero solo admin/capitán pueden eliminarla
                if (!empty($validated['is_permanent']) || ($validated['type'] ?? null) === 'Permanente') {
                    $novelty->is_permanent = true;
                    // Las novedades permanentes no tienen guardia específica (son para todas)
                    $novelty->guardia_id = null;
                }
            }
            
            $novelty->save();

            $isAcademy = (($validated['type'] ?? null) === 'Academia');
            $lines = [];
            $lines[] = 'Título: ' . (string) ($novelty->title ?? '');
            $lines[] = 'Tipo: ' . (string) ($novelty->type ?? '');
            $lines[] = 'Descripción: ' . (string) ($novelty->description ?? '');
            $lines[] = 'Fecha: ' . optional($novelty->date)->format('Y-m-d H:i');

            SystemEmailService::send(
                type: $isAcademy ? 'academy' : 'novelty',
                subject: $isAcademy ? 'Academia registrada' : 'Novedad registrada',
                lines: $lines,
                actorEmail: auth()->user()?->email
            );

            return back()->with('success', $isAcademy ? 'Academia registrada correctamente.' : 'Novedad registrada correctamente.');
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
        
        // Solo admin/capitán pueden eliminar novedades permanentes
        if ($novelty->is_permanent && !in_array(auth()->user()->role, ['super_admin', 'capitania'], true)) {
            return response()->json(['error' => 'Solo administradores o capitanía pueden eliminar novedades permanentes.'], 403);
        }
        
        // Solo admin/capitán o el creador pueden eliminar novedades de su guardia
        $authUser = auth()->user();
        if (!in_array($authUser->role, ['super_admin', 'capitania'], true)) {
            if ($novelty->guardia_id && $novelty->guardia_id !== $authUser->guardia_id) {
                return response()->json(['error' => 'No puedes eliminar novedades de otra guardia.'], 403);
            }
        }
        
        $novelty->delete();
        return response()->noContent();
    }
}
