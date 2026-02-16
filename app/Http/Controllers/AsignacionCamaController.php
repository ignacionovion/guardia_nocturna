<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Bed;
use App\Models\BedAssignment;
use App\Models\MapaBomberoUsuarioLegacy;
use App\Services\SystemEmailService;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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

        // Evitar pegarle a information_schema en cada request.
        // Si en el futuro agregas/eliminas la columna, reinicia PHP-FPM o el servidor para limpiar cache.
        static $hasUserIdColumn = null;
        if ($hasUserIdColumn === null) {
            $hasUserIdColumn = Schema::hasColumn('bed_assignments', 'user_id');
        }

        // Resolver legacy user_id una sola vez (en tu código original se hacía 2 veces)
        $legacyUserId = null;
        if ($hasUserIdColumn) {
            $legacyUserId = MapaBomberoUsuarioLegacy::where('firefighter_id', $validated['firefighter_id'])->value('user_id');
        }

        try {
            // Transacción + lock para evitar condiciones de carrera (2 guardias asignando a la vez)
            $assignment = DB::transaction(function () use ($validated, $legacyUserId) {
                // Lock a la cama para que no la asignen en paralelo
                $bed = Bed::whereKey($validated['bed_id'])->lockForUpdate()->firstOrFail();

                if ($bed->status !== 'available') {
                    // Lanzamos excepción controlada para salir de la transacción
                    throw new \RuntimeException('La cama no está disponible.');
                }

                // Validación: Verificar si el voluntario (o su usuario legacy) ya tiene una cama activa
                $existingAssignment = BedAssignment::query()
                    ->whereNull('released_at')
                    ->where(function ($q) use ($validated, $legacyUserId) {
                        $q->where('firefighter_id', $validated['firefighter_id']);
                        if ($legacyUserId) {
                            $q->orWhere('user_id', $legacyUserId);
                        }
                    })
                    ->with('bed')
                    ->first();

                if ($existingAssignment) {
                    throw new \RuntimeException('Este voluntario ya tiene asignada la cama #' . ($existingAssignment->bed->number ?? $existingAssignment->bed_id));
                }

                $data = [
                    'bed_id' => $validated['bed_id'],
                    'firefighter_id' => $validated['firefighter_id'],
                    'notes' => $validated['notes'] ?? null,
                ];

                if ($legacyUserId) {
                    $data['user_id'] = $legacyUserId;
                }

                // Crear asignación
                $assignment = BedAssignment::create($data);
                $assignment->load(['bed', 'firefighter']);

                // Actualizar estado de la cama
                $bed->update(['status' => 'occupied']);

                return $assignment;
            }, 3); // 3 reintentos si hay deadlock

            // Enviar email fuera de la transacción (si el SMTP se pega, no bloquea locks)
            DB::afterCommit(function () use ($assignment) {
                try {
                    $bed = $assignment->bed;

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
                } catch (\Throwable $e) {
                    // No fallar la asignación por un problema de correo
                    Log::warning('Fallo envío correo (beds asignada): ' . $e->getMessage(), [
                        'bed_id' => $assignment->bed_id,
                        'firefighter_id' => $assignment->firefighter_id,
                    ]);
                }
            });

            return redirect()->route('camas')->with('success', 'Cama asignada correctamente.');
        } catch (\RuntimeException $e) {
            return back()->withErrors(['msg' => $e->getMessage()]);
        } catch (\Throwable $e) {
            Log::error('Error asignando cama: ' . $e->getMessage(), [
                'bed_id' => $validated['bed_id'] ?? null,
                'firefighter_id' => $validated['firefighter_id'] ?? null,
            ]);
            return back()->withErrors(['msg' => 'Ocurrió un error al asignar la cama. Revisa los logs.']);
        }
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
