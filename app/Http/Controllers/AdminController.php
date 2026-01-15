<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Guardia;
use App\Models\User;
use App\Models\Shift;
use App\Models\ShiftUser;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class AdminController extends Controller
{
    public function index()
    {
        // Limpieza automática de reemplazos vencidos (después de las 07:00 AM)
        $this->releaseExpiredReplacements();

        $user = auth()->user();

        // Permitir super_admin y guardia (para gestionar su propia dotación)
        if ($user->role !== 'super_admin' && $user->role !== 'guardia') {
            abort(403, 'No tienes permiso para acceder a esta sección.');
        }

        $query = Guardia::with(['users' => function($q) {
            $q->where('role', '!=', 'guardia')
              ->orderBy('admission_date', 'asc')
              ->with(['jobReplacement', 'replacedBy']);
        }]);

        // Si es cuenta de Guardia, filtrar solo su propia guardia
        if ($user->role === 'guardia') {
            $query->where('id', $user->guardia_id);
        }

        $guardias = $query->get();

        // Obtener usuarios para el select (excluyendo cuentas de sistema)
        $volunteers = User::where('role', '!=', 'guardia')
            ->orderBy('name')
            ->orderBy('last_name_paternal')
            ->get();

        return view('admin.guardias', compact('guardias', 'volunteers'));
    }

    /**
     * Libera automáticamente a los reemplazos (no titulares) después de las 07:00 AM
     * si fueron asignados antes de ese horario (pertenecen al turno anterior).
     */
    private function releaseExpiredReplacements()
    {
        $now = Carbon::now();
        
        // Solo ejecutar si es después de las 07:00 AM
        if ($now->hour >= 7) {
            $cutoffTime = $now->copy()->startOfDay()->addHours(7);
            
            // Buscar usuarios NO titulares asignados a una guardia
            // y que no hayan sido actualizados después de las 07:00 AM de hoy
            $expiredReplacements = User::where('is_titular', false)
                ->whereNotNull('guardia_id')
                ->where('updated_at', '<', $cutoffTime)
                ->get();

            foreach ($expiredReplacements as $user) {
                $user->update([
                    'guardia_id' => null,
                    'job_replacement_id' => null,
                    'attendance_status' => 'constituye', // Reset estado
                    'role' => ($user->role === 'jefe_guardia') ? 'bombero' : $user->role,
                    'is_shift_leader' => false,
                    'is_exchange' => false,
                    'is_penalty' => false,
                ]);
            }
        }
    }

    public function assignBombero(Request $request)
    {
        $user = auth()->user();

        if ($user->role !== 'super_admin' && $user->role !== 'guardia') {
            abort(403, 'No autorizado.');
        }

        // Validación base
        $rules = [
            'guardia_id' => 'required|exists:guardias,id',
            'user_id' => 'required|exists:users,id',
            'job_replacement_id' => 'nullable|exists:users,id',
            'is_driver' => 'nullable|boolean',
            'is_rescue_operator' => 'nullable|boolean',
            'is_trauma_assistant' => 'nullable|boolean',
            'is_shift_leader' => 'nullable|boolean',
            'is_exchange' => 'nullable|boolean',
            'is_penalty' => 'nullable|boolean',
        ];

        $validated = $request->validate($rules);

        // Si es cuenta de guardia, asegurar que solo asigna a SU guardia
        if ($user->role === 'guardia' && $validated['guardia_id'] != $user->guardia_id) {
            abort(403, 'No puedes asignar personal a otra guardia.');
        }

        $targetUser = User::findOrFail($validated['user_id']);
        
        // Determinar rol del sistema basado en si es oficial a cargo
        $systemRole = 'bombero';
        if ($request->has('is_shift_leader')) {
            $systemRole = 'jefe_guardia';
        }

        // Mantener roles superiores si ya los tiene
        if (in_array($targetUser->role, ['super_admin', 'capitania'])) {
            $systemRole = $targetUser->role;
        }

        $targetUser->update([
            'guardia_id' => $validated['guardia_id'],
            'role' => $systemRole,
            'job_type' => 'Bombero', // Legacy/Fallback
            'job_replacement_id' => $request->input('job_replacement_id'),
            'is_driver' => $request->has('is_driver') ? true : $targetUser->is_driver,
            'is_rescue_operator' => $request->has('is_rescue_operator') ? true : $targetUser->is_rescue_operator,
            'is_trauma_assistant' => $request->has('is_trauma_assistant') ? true : $targetUser->is_trauma_assistant,
            'is_shift_leader' => $request->has('is_shift_leader'),
            'is_exchange' => $request->has('is_exchange'),
            'is_penalty' => $request->has('is_penalty'),
            'is_titular' => true, // Al asignar manualmente, se asume titularidad por defecto
        ]);

        return redirect()->route('admin.guardias')->with('success', 'Bombero asignado correctamente a la guardia.');
    }

    public function toggleTitular($id)
    {
        $user = auth()->user();
        if ($user->role !== 'super_admin' && $user->role !== 'guardia') {
            abort(403, 'No autorizado.');
        }

        $bombero = User::findOrFail($id);

        if ($user->role === 'guardia' && $bombero->guardia_id != $user->guardia_id) {
            abort(403, 'No autorizado.');
        }

        $bombero->is_titular = !$bombero->is_titular;
        $bombero->save();

        $status = $bombero->is_titular ? 'TITULAR' : 'TRANSITORIO';
        return redirect()->back()->with('success', "Estado de titularidad actualizado: {$bombero->name} ahora es {$status}.");
    }

    public function assignReplacement(Request $request)
    {
        $user = auth()->user();

        if ($user->role !== 'super_admin' && $user->role !== 'guardia') {
            abort(403, 'No autorizado.');
        }

        $validated = $request->validate([
            'guardia_id' => 'required|exists:guardias,id',
            'original_user_id' => 'required|exists:users,id',
            'replacement_user_id' => 'required|exists:users,id',
        ]);

        // Verificar permisos de guardia
        if ($user->role === 'guardia' && $validated['guardia_id'] != $user->guardia_id) {
            abort(403, 'No puedes gestionar otra guardia.');
        }

        $guardia = Guardia::findOrFail($validated['guardia_id']);
        $originalUser = User::findOrFail($validated['original_user_id']);
        $replacementUser = User::findOrFail($validated['replacement_user_id']);

        // Validar que el original pertenezca a la guardia
        if ($originalUser->guardia_id != $guardia->id) {
            return back()->withErrors(['msg' => 'El bombero a reemplazar no pertenece a esta guardia.']);
        }

        // Validar que el reemplazo NO pertenezca ya a esta guardia (opcional, pero lógico)
        if ($replacementUser->guardia_id == $guardia->id) {
            return back()->withErrors(['msg' => 'El voluntario seleccionado ya pertenece a esta guardia.']);
        }

        // Asignar el reemplazo a la guardia
        $replacementUser->update([
            'guardia_id' => $guardia->id,
            'job_replacement_id' => $originalUser->id,
            'attendance_status' => 'reemplazo', // Marcar como reemplazo por defecto
            'role' => 'bombero', // Asegurar rol base
        ]);

        return redirect()->back()->with('success', "Reemplazo asignado: {$replacementUser->name} reemplaza a {$originalUser->name}.");
    }

    public function storeBombero(Request $request)
    {
        $user = auth()->user();

        if ($user->role !== 'super_admin' && $user->role !== 'guardia') {
            abort(403, 'No autorizado.');
        }

        $validated = $request->validate([
            'guardia_id' => 'required|exists:guardias,id',
            'name' => 'required|string|max:255',
            'last_name_paternal' => 'nullable|string|max:255',
            'email' => 'required|email|unique:users,email',
            'age' => 'required|integer|min:18',
            'years_of_service' => 'required|integer|min:0',
            'role' => 'nullable|string|in:bombero,jefe_guardia',
            'is_driver' => 'nullable|boolean',
        ]);

        // Si es cuenta de guardia, asegurar que crea en SU guardia
        if ($user->role === 'guardia' && $validated['guardia_id'] != $user->guardia_id) {
            abort(403, 'No puedes agregar personal a otra guardia.');
        }

        User::create([
            'name' => $validated['name'],
            'last_name_paternal' => $validated['last_name_paternal'] ?? null,
            'email' => $validated['email'],
            'password' => Hash::make('password'),
            'role' => $request->input('role', 'bombero'),
            'age' => $validated['age'],
            'years_of_service' => $validated['years_of_service'],
            'guardia_id' => $validated['guardia_id'],
            'is_driver' => $request->has('is_driver'),
            'is_titular' => true, // Nuevo ingreso directo es Titular
        ]);

        return redirect()->route('admin.guardias')->with('success', 'Bombero agregado correctamente a la guardia.');
    }

    public function editBombero($id)
    {
        $user = auth()->user();
        if ($user->role !== 'super_admin' && $user->role !== 'guardia') {
            abort(403, 'No autorizado.');
        }

        $bombero = User::findOrFail($id);

        // Si es guardia, verificar que el bombero pertenece a su guardia
        if ($user->role === 'guardia' && $bombero->guardia_id != $user->guardia_id) {
            abort(403, 'No puedes editar personal de otra guardia.');
        }

        $guardias = Guardia::all(); // Podríamos filtrar esto también, pero en el edit suele ser readonly o select

        return view('admin.bomberos.edit', compact('bombero', 'guardias'));
    }

    public function updateBombero(Request $request, $id)
    {
        $user = auth()->user();
        if ($user->role !== 'super_admin' && $user->role !== 'guardia') {
            abort(403, 'No autorizado.');
        }

        $bombero = User::findOrFail($id);

        // Si es guardia, verificar pertenencia
        if ($user->role === 'guardia' && $bombero->guardia_id != $user->guardia_id) {
            abort(403, 'No autorizado.');
        }

        $validated = $request->validate([
            'guardia_id' => 'required|exists:guardias,id',
            'name' => 'required|string|max:255',
            'last_name_paternal' => 'nullable|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'age' => 'required|integer|min:18',
            'years_of_service' => 'required|integer|min:0',
            'role' => 'required|string|in:super_admin,capitania,jefe_guardia,bombero',
            'is_driver' => 'nullable|boolean',
        ]);

        // Validación extra de guardia_id para rol guardia
        if ($user->role === 'guardia' && $validated['guardia_id'] != $user->guardia_id) {
            abort(403, 'No puedes mover personal a otra guardia.');
        }

        $data = $validated;
        $data['is_driver'] = $request->has('is_driver');
        
        $bombero->update($data);

        return redirect()->route('admin.guardias')->with('success', 'Bombero actualizado correctamente.');
    }

    public function destroyBombero($id)
    {
        $user = auth()->user();
        if ($user->role !== 'super_admin' && $user->role !== 'guardia') {
            abort(403, 'No autorizado.');
        }

        $bombero = User::findOrFail($id);

        // Si es guardia, verificar pertenencia
        if ($user->role === 'guardia' && $bombero->guardia_id != $user->guardia_id) {
            abort(403, 'No puedes eliminar personal de otra guardia.');
        }

        $bombero->delete();

        return redirect()->route('admin.guardias')->with('success', 'Bombero eliminado correctamente.');
    }

    // --- CRUD Guardias ---

    public function storeGuardia(Request $request)
    {
        if (auth()->user()->role !== 'super_admin') {
            abort(403, 'No autorizado.');
        }

        $request->validate(['name' => 'required|string|max:255|unique:guardias,name']);

        $guardia = Guardia::create(['name' => $request->name]);

        // Crear usuario automático para gestión de la guardia
        User::create([
            'name' => $request->name,
            'email' => strtolower(str_replace(' ', '.', $request->name)) . '@guardianocturna.cl',
            'password' => Hash::make('password'),
            'role' => 'guardia',
            'guardia_id' => $guardia->id,
            'years_of_service' => 0,
            'age' => 0,
        ]);

        return redirect()->route('admin.guardias')->with('success', 'Nueva guardia y usuario de gestión creados correctamente.');
    }

    public function editGuardia($id)
    {
        if (auth()->user()->role !== 'super_admin') {
            abort(403, 'No autorizado.');
        }

        $guardia = Guardia::findOrFail($id);
        
        return view('admin.guardias.edit', compact('guardia'));
    }

    public function updateGuardia(Request $request, $id)
    {
         if (auth()->user()->role !== 'super_admin') {
            abort(403, 'No autorizado.');
        }

        $guardia = Guardia::findOrFail($id);
        
        $request->validate(['name' => 'required|string|max:255|unique:guardias,name,' . $id]);

        $guardia->update(['name' => $request->name]);

        // Actualizar usuario de gestión asociado
        $guardiaUser = User::where('guardia_id', $guardia->id)->where('role', 'guardia')->first();
        if ($guardiaUser) {
            $guardiaUser->update([
                'name' => $request->name,
                'email' => strtolower(str_replace(' ', '', $request->name)) . '@guardianocturna.cl',
            ]);
        }

        return redirect()->route('admin.guardias')->with('success', 'Guardia actualizada correctamente.');
    }

    public function destroyGuardia($id)
    {
         if (auth()->user()->role !== 'super_admin') {
            abort(403, 'No autorizado.');
        }

        $guardia = Guardia::findOrFail($id);
        
        // Verificar si tiene personal asignado (excluyendo el usuario de gestión de la guardia)
        $usersCount = $guardia->users()->where('role', '!=', 'guardia')->count();

        if ($usersCount > 0) {
            return back()->withErrors(['msg' => 'No se puede eliminar una guardia que tiene personal operativo asignado.']);
        }

        // Eliminar usuario de gestión asociado
        User::where('guardia_id', $guardia->id)->where('role', 'guardia')->delete();

        $guardia->delete();

        return redirect()->route('admin.guardias')->with('success', 'Guardia eliminada correctamente.');
    }

    public function activateWeek($id)
    {
        if (auth()->user()->role !== 'super_admin') {
            abort(403, 'No autorizado.');
        }

        $newActiveGuardia = Guardia::findOrFail($id);

        // Buscar la guardia que estaba activa previamente para resetearla
        $previousActiveGuardia = Guardia::where('is_active_week', true)->first();
        
        if ($previousActiveGuardia && $previousActiveGuardia->id !== $newActiveGuardia->id) {
            $this->resetGuardiaState($previousActiveGuardia);
        }

        // Desactivar todas las guardias primero (limpieza general de flags)
        Guardia::query()->update(['is_active_week' => false]);

        // Activar la nueva seleccionada
        $newActiveGuardia->update(['is_active_week' => true]);

        $message = 'Semana de Guardia activada para: ' . $newActiveGuardia->name;
        if ($previousActiveGuardia && $previousActiveGuardia->id !== $newActiveGuardia->id) {
            $message .= '. La guardia anterior (' . $previousActiveGuardia->name . ') ha sido restablecida.';
        }

        return redirect()->route('admin.guardias')->with('success', $message);
    }

    /**
     * Restablece el estado de una guardia al salir de turno.
     * - NO Titulares: Se remueven de la guardia.
     * - Titulares: Se mantienen, reseteando sus estados diarios.
     */
    private function resetGuardiaState(Guardia $guardia)
    {
        // 1. NO Titulares (Reemplazos, Canjes, Apoyos temporales)
        // Se van de la guardia al terminar el turno
        $transitorios = User::where('guardia_id', $guardia->id)
                            ->where('is_titular', false)
                            ->get();

        foreach ($transitorios as $user) {
            $user->update([
                'guardia_id' => null,
                'job_replacement_id' => null,
                'attendance_status' => 'constituye',
                'is_shift_leader' => false,
                'is_exchange' => false,
                'is_penalty' => false,
                'role' => ($user->role === 'jefe_guardia') ? 'bombero' : $user->role,
            ]);
        }

        // 2. Titulares (Dotación permanente)
        // Se quedan, pero se limpia su estado del turno
        $titulares = User::where('guardia_id', $guardia->id)
                         ->where('is_titular', true)
                         ->get();

        foreach ($titulares as $user) {
            $user->update([
                'attendance_status' => 'constituye', // Vuelven a estado base
                'job_replacement_id' => null, // Por si acaso cubrieron a alguien interno
                'is_shift_leader' => false,
                'is_exchange' => false,
                'is_penalty' => false,
                'role' => ($user->role === 'jefe_guardia') ? 'bombero' : $user->role,
            ]);
        }
    }

    public function bulkUpdateGuardia(Request $request, $id)
    {
        if (auth()->user()->role !== 'super_admin' && auth()->user()->role !== 'guardia') {
            abort(403, 'No autorizado.');
        }

        $guardia = Guardia::findOrFail($id);
        
        // Si es cuenta de guardia, verificar propiedad
        if (auth()->user()->role === 'guardia' && auth()->user()->guardia_id != $guardia->id) {
            abort(403, 'No autorizado.');
        }

        $data = $request->validate([
            'users' => 'required|array',
            'users.*.attendance_status' => 'nullable|string|in:constituye,reemplazo,permiso,ausente,falta,licencia',
            'users.*.is_rescue_operator' => 'nullable|boolean',
            'users.*.is_trauma_assistant' => 'nullable|boolean',
        ]);

        // 1. Crear o recuperar el Turno (Shift) del día
        $shift = Shift::firstOrCreate(
            [
                'date' => Carbon::today(),
                'status' => 'active' // O buscar uno activo independientemente de la fecha si es guardia continua
            ],
            [
                'status' => 'active',
                'notes' => 'Guardia generada automáticamente'
            ]
        );

        foreach ($data['users'] as $userId => $attributes) {
            $user = User::find($userId);
            if ($user && $user->guardia_id == $guardia->id) {
                // Actualizar estado en tiempo real (User)
                $user->update([
                    'attendance_status' => $attributes['attendance_status'] ?? 'constituye',
                    'is_rescue_operator' => isset($attributes['is_rescue_operator']),
                    'is_trauma_assistant' => isset($attributes['is_trauma_assistant']),
                ]);

                // 2. Registrar Historial (ShiftUser)
                // Determinar presencia
                $isPresent = in_array($user->attendance_status, ['constituye', 'reemplazo']);
                
                // Determinar tipo de asignación para el historial
                $assignmentType = 'guardia';
                if ($user->attendance_status === 'reemplazo') $assignmentType = 'reemplazo';
                if ($user->is_exchange) $assignmentType = 'canje';
                
                // Guardar registro histórico
                $shiftUserPayload = [
                    'role' => $user->role, // Guardar rol que tenía en ese momento
                    'present' => $isPresent,
                    'assignment_type' => $assignmentType,
                    'replaced_user_id' => $user->job_replacement_id, // Si estaba reemplazando a alguien
                    'start_time' => Carbon::now(),
                    // Para efectos de reporte inmediato, si está presente, asumimos turno completo o en curso.
                    // El reporte actual filtra por end_time not null.
                    // Vamos a setear un end_time provisional si está presente para que aparezca en el reporte.
                    'end_time' => $isPresent ? Carbon::now()->addHours(9) : null,
                ];

                if (Schema::hasColumn('shift_users', 'guardia_id')) {
                    $shiftUserPayload['guardia_id'] = $guardia->id;
                }

                if (Schema::hasColumn('shift_users', 'attendance_status')) {
                    $shiftUserPayload['attendance_status'] = $user->attendance_status;
                }

                ShiftUser::updateOrCreate(
                    [
                        'shift_id' => $shift->id,
                        'user_id' => $user->id,
                    ],
                    $shiftUserPayload
                );
            }
        }

        return redirect()->back()->with('success', 'Asistencia guardada y registros históricos actualizados.');
    }
}
