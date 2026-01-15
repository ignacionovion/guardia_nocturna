<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Shift;
use App\Models\ShiftUser;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class GuardiaController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        $query = Shift::with(['leader', 'users.user', 'users.replacedUser'])
            ->where('status', 'active');

        // Si el usuario pertenece a una guardia (es cuenta de guardia o bombero), 
        // solo mostrar turnos de su guardia.
        if ($user->guardia_id) {
            $query->whereHas('leader', function($q) use ($user) {
                $q->where('guardia_id', $user->guardia_id);
            });
        }

        $shift = $query->latest()->first();
            
        // Filtrar lista de voluntarios para agregar:
        // 1. Excluir rol 'guardia' (cuentas de sistema)
        // 2. Si el usuario logueado tiene guardia_id, mostrar solo voluntarios de esa guardia
        $usersQuery = User::where('role', '!=', 'guardia');
        
        if ($user->guardia_id) {
            $usersQuery->where('guardia_id', $user->guardia_id);
        }
        
        $users = $usersQuery->orderBy('name')->get();
        
        // Usuarios actualmente en guardia para excluir del select si se desea, 
        // o para mostrar en el select de reemplazo.
        $currentGuardiaUsers = $shift ? $shift->users->pluck('user_id')->toArray() : [];
        
        return view('guardia', compact('shift', 'users', 'currentGuardiaUsers'));
    }

    public function start(Request $request)
    {
        $shift = Shift::create([
            'date' => now(),
            'status' => 'active',
            'shift_leader_id' => Auth::id(), // Inicialmente el que crea, luego se puede cambiar
        ]);

        return redirect()->route('guardia')->with('success', 'Guardia iniciada correctamente.');
    }

    public function close(Request $request, $id)
    {
        $shift = Shift::findOrFail($id);
        
        // Cerrar todos los turnos abiertos de los usuarios
        foreach ($shift->users as $shiftUser) {
            if (!$shiftUser->end_time) {
                $shiftUser->update(['end_time' => now()]);
            }
        }

        $shift->update([
            'status' => 'closed',
            'notes' => $request->input('notes')
        ]);

        return redirect()->route('guardia')->with('success', 'Guardia finalizada correctamente.');
    }

    public function addUser(Request $request, $id)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'assignment_type' => 'required|string',
            'replaced_user_id' => 'nullable|required_if:assignment_type,Reemplazo|exists:users,id',
        ]);

        $exists = ShiftUser::where('shift_id', $id)
            ->where('user_id', $request->user_id)
            ->whereNull('end_time')
            ->exists();

        if ($exists) {
            return back()->withErrors(['user_id' => 'El voluntario ya está activo en esta guardia.']);
        }

        $shift = Shift::with('leader')->findOrFail($id);
        $user = User::findOrFail($request->user_id);

        $attendanceStatus = 'constituye';
        if ($request->assignment_type === 'Reemplazo') {
            $attendanceStatus = 'reemplazo';
        }
        if ($request->assignment_type === 'Cumple falta') {
            $attendanceStatus = 'falta';
        }

        $shiftUserPayload = [
            'shift_id' => $id,
            'user_id' => $user->id,
            'assignment_type' => $request->assignment_type,
            'replaced_user_id' => $request->replaced_user_id,
            'start_time' => now(),
            'present' => $request->assignment_type !== 'Cumple falta', // Asumo que cumple falta es no presente físicamente o algo así, pero lo dejaré true por defecto salvo que sea falta explícita
        ];

        if (Schema::hasColumn('shift_users', 'guardia_id')) {
            $shiftUserPayload['guardia_id'] = $shift->leader?->guardia_id ?? Auth::user()?->guardia_id ?? $user->guardia_id;
        }

        if (Schema::hasColumn('shift_users', 'attendance_status')) {
            $shiftUserPayload['attendance_status'] = $attendanceStatus;
        }

        ShiftUser::create($shiftUserPayload);

        return redirect()->route('guardia')->with('success', 'Voluntario asignado correctamente.');
    }

    public function removeUser(Request $request, $shiftId, $userId)
    {
        $shiftUser = ShiftUser::where('shift_id', $shiftId)
            ->where('user_id', $userId)
            ->whereNull('end_time')
            ->firstOrFail();

        $shiftUser->update(['end_time' => now()]);

        return redirect()->route('guardia')->with('success', 'Salida registrada correctamente.');
    }
}
