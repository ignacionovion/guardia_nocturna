<?php

namespace App\Http\Controllers;

use App\Models\Bed;
use App\Models\BedAssignment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BedAssignmentController extends Controller
{
    public function assign(Request $request, string $bed)
    {
        $bedId = $request->route('bed') ?? $bed;
        $bed = Bed::query()->findOrFail((int) $bedId);

        // Validar que la cama puede ser asignada
        if (!$bed->canBeAssigned()) {
            if ($bed->is_occupied) {
                return back()->with('error', 'Esta cama ya está ocupada.');
            }
            if ($bed->status === 'maintenance') {
                return back()->with('error', 'No se puede asignar una cama en mantención.');
            }
            if ($bed->status === 'disabled') {
                return back()->with('error', 'No se puede asignar una cama deshabilitada.');
            }
        }

        $validated = $request->validate([
            'volunteer_id' => 'required|exists:users,id',
            'notes' => 'nullable|string|max:500',
        ]);

        $volunteer = User::findOrFail($validated['volunteer_id']);

        // Validar que el voluntario está en guardia activa y presente
        $activeGuardia = \App\Models\Guardia::where('is_active_week', true)->first();
        if (!$activeGuardia) {
            return back()->with('error', 'No hay guardia activa en este momento.');
        }

        // Buscar bombero asociado al usuario
        $userId = $volunteer->id;
        $firefighterId = \App\Models\MapaBomberoUsuarioLegacy::where('user_id', $userId)->value('firefighter_id');
        
        if (!$firefighterId) {
            return back()->with('error', 'El voluntario seleccionado no está registrado como bombero.');
        }

        $bombero = \App\Models\Bombero::find($firefighterId);
        if (!$bombero || $bombero->guardia_id != $activeGuardia->id) {
            return back()->with('error', 'El voluntario no pertenece a la guardia activa.');
        }

        // Validar que está presente
        $presentStatuses = ['constituye', 'reemplazo', 'refuerzo'];
        if (!in_array($bombero->estado_asistencia, $presentStatuses)) {
            return back()->with('error', 'Solo se pueden asignar camas a voluntarios presentes en la guardia.');
        }

        // Validar que no esté fuera de servicio
        if ($bombero->fuera_de_servicio) {
            return back()->with('error', 'El voluntario está fuera de servicio.');
        }

        // Validar género de la cama
        if ($bed->gender !== 'mixed') {
            $userGender = strtolower($volunteer->gender ?? '');
            if ($bed->gender === 'male' && !in_array($userGender, ['masculino', 'male'])) {
                return back()->with('error', 'Esta cama es solo para hombres.');
            }
            if ($bed->gender === 'female' && !in_array($userGender, ['femenino', 'female'])) {
                return back()->with('error', 'Esta cama es solo para mujeres.');
            }
        }

        // Validar que el voluntario no tenga otra cama asignada
        $hasActiveBed = BedAssignment::where('volunteer_id', $volunteer->id)
            ->whereNull('ended_at')
            ->exists();
        
        if ($hasActiveBed) {
            return back()->with('error', 'El voluntario ya tiene una cama asignada.');
        }

        DB::beginTransaction();
        try {
            // Crear asignación
            BedAssignment::create([
                'bed_id' => $bed->id,
                'volunteer_id' => $validated['volunteer_id'],
                'assigned_by' => Auth::id(),
                'started_at' => now(),
                'notes' => $validated['notes'] ?? null,
            ]);

            // Actualizar estado de la cama
            $bed->update(['status' => 'occupied']);

            DB::commit();

            return back()->with('success', 'Cama asignada exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al asignar la cama: ' . $e->getMessage());
        }
    }

    public function release(Request $request, string $bed)
    {
        $bedId = $request->route('bed') ?? $bed;
        $bed = Bed::query()->findOrFail((int) $bedId);

        // Validar que la cama está ocupada
        if (!$bed->is_occupied) {
            return back()->with('error', 'Esta cama no está ocupada.');
        }

        $validated = $request->validate([
            'notes' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            $assignment = $bed->currentAssignment;
            
            // Cerrar asignación
            $assignment->update([
                'ended_at' => now(),
                'notes' => $validated['notes'] ?? $assignment->notes,
            ]);

            // Actualizar estado de la cama
            $bed->update(['status' => 'available']);

            DB::commit();

            return back()->with('success', 'Cama liberada exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al liberar la cama: ' . $e->getMessage());
        }
    }

    public function history(Request $request, string $bed)
    {
        $bedId = $request->route('bed') ?? $bed;
        $bed = Bed::query()->findOrFail((int) $bedId);

        $assignments = $bed->assignments()
            ->with(['volunteer', 'assignedBy'])
            ->orderBy('started_at', 'desc')
            ->paginate(20);

        return view('admin.beds.history', compact('bed', 'assignments'));
    }

    public function getAvailableVolunteers(Request $request)
    {
        // Obtener guardia activa
        $activeGuardia = \App\Models\Guardia::where('is_active_week', true)->first();
        
        if (!$activeGuardia) {
            return response()->json([]);
        }

        // Filtro opcional por género de cama
        $genderFilter = $request->input('gender');

        // Obtener bomberos presentes en guardia activa
        $query = \App\Models\Bombero::where('guardia_id', $activeGuardia->id)
            ->whereIn('estado_asistencia', ['constituye', 'reemplazo', 'refuerzo'])
            ->where(function ($q) {
                $q->whereNull('fuera_de_servicio')->orWhere('fuera_de_servicio', false);
            });

        $bomberos = $query->orderBy('apellido_paterno')
            ->orderBy('nombres')
            ->get();

        // Mapear bomberos a users y aplicar filtro de género
        $volunteers = $bomberos->map(function ($bombero) use ($genderFilter) {
            // Obtener user_id del bombero
            $userId = \App\Models\MapaBomberoUsuarioLegacy::where('firefighter_id', $bombero->id)
                ->value('user_id');
            
            if (!$userId) {
                return null;
            }

            $user = \App\Models\User::find($userId);
            if (!$user) {
                return null;
            }

            // Aplicar filtro de género si existe
            if ($genderFilter && $genderFilter !== 'mixed') {
                $userGender = strtolower($user->gender ?? '');
                if ($genderFilter === 'male' && $userGender !== 'masculino' && $userGender !== 'male') {
                    return null;
                }
                if ($genderFilter === 'female' && $userGender !== 'femenino' && $userGender !== 'female') {
                    return null;
                }
            }

            // Verificar si ya tiene cama asignada
            $hasActiveBed = \App\Models\BedAssignment::where('volunteer_id', $user->id)
                ->whereNull('ended_at')
                ->exists();

            return [
                'id' => $user->id,
                'name' => trim($bombero->nombres . ' ' . $bombero->apellido_paterno),
                'rut' => $bombero->rut ?? null,
                'cargo' => $bombero->cargo_texto ?? null,
                'has_active_bed' => $hasActiveBed,
                'estado_asistencia' => $bombero->estado_asistencia,
            ];
        })->filter()->values();

        return response()->json($volunteers);
    }
}
