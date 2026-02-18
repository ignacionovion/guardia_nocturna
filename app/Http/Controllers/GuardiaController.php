<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Shift;
use App\Models\ShiftUser;
use App\Models\Bombero;
use App\Models\Guardia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use App\Models\MapaBomberoUsuarioLegacy;
use App\Services\ReplacementService;
use Carbon\Carbon;
use App\Models\SystemSetting;

class GuardiaController extends Controller
{
    private function cleanupTransitoriosOnConstitution(Guardia $guardia): void
    {
        $tz = SystemSetting::getValue('guardia_schedule_tz', env('GUARDIA_SCHEDULE_TZ', config('app.timezone')));
        $now = Carbon::now($tz);

        $scheduleHourToday = $now->isSunday() ? 22 : 23;
        $todayCutoff = $now->copy()->startOfDay()->addHours($scheduleHourToday);

        if ($now->greaterThanOrEqualTo($todayCutoff)) {
            $cutoff = $todayCutoff;
        } else {
            $yesterday = $now->copy()->subDay();
            $scheduleHourYesterday = $yesterday->isSunday() ? 22 : 23;
            $cutoff = $yesterday->copy()->startOfDay()->addHours($scheduleHourYesterday);
        }

        if ($now->diffInHours($cutoff) > 8) {
            return;
        }

        $transitorios = Bombero::where('guardia_id', $guardia->id)
            ->where('es_titular', false)
            ->where('updated_at', '<', $cutoff)
            ->get();

        foreach ($transitorios as $bombero) {
            $bombero->update([
                'guardia_id' => null,
                'estado_asistencia' => 'constituye',
                'es_jefe_guardia' => false,
                'es_cambio' => false,
                'es_sancion' => false,
            ]);
        }
    }

    public function index()
    {
        $user = Auth::user();
        $now = Carbon::now();

        ReplacementService::expire($now);
        
        $query = Shift::with(['leader', 'users.firefighter', 'users.replacedFirefighter'])
            ->where('status', 'active');

        // Si el usuario pertenece a una guardia (es cuenta de guardia o bombero), 
        // solo mostrar turnos de su guardia.
        if ($user->guardia_id) {
            $query->whereHas('leader', function($q) use ($user) {
                $q->where('guardia_id', $user->guardia_id);
            });
        }

        $shift = $query->latest()->first();

        if ($shift) {
            $shift->setRelation(
                'users',
                $shift->users->filter(function ($shiftUser) use ($now) {
                    return !$shiftUser->user
                        || !$shiftUser->user->replacement_until
                        || $shiftUser->user->replacement_until->greaterThan($now);
                })->values()
            );
        }
            
        $bomberosQuery = Bombero::query();

        if ($user->guardia_id) {
            $bomberosQuery->where('guardia_id', $user->guardia_id);
        }

        $users = $bomberosQuery
            ->orderBy('nombres')
            ->orderBy('apellido_paterno')
            ->get();
        
        // Usuarios actualmente en guardia para excluir del select si se desea, 
        // o para mostrar en el select de reemplazo.
        $currentGuardiaUsers = $shift ? $shift->users->pluck('firefighter_id')->filter()->toArray() : [];
        
        return view('guardia', compact('shift', 'users', 'currentGuardiaUsers'));
    }

    public function now(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            abort(403);
        }

        $now = Carbon::now();

        ReplacementService::expire($now);

        $query = Shift::with(['leader', 'users.firefighter', 'users.replacedFirefighter'])
            ->where('status', 'active');

        if ($user->role === 'guardia' && $user->guardia_id) {
            $query->whereHas('leader', function ($q) use ($user) {
                $q->where('guardia_id', $user->guardia_id);
            });
        }

        $shift = $query->latest()->first();

        if ($shift) {
            $shift->setRelation(
                'users',
                $shift->users->filter(function ($shiftUser) use ($now) {
                    return !$shiftUser->user
                        || !$shiftUser->user->replacement_until
                        || $shiftUser->user->replacement_until->greaterThan($now);
                })->values()
            );
        }

        // La vista NOW se alimenta por polling a nowData; no necesitamos cargar todos los bomberos aquí.
        return view('guardia_now', compact('shift'));
    }

    public function nowData(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $now = Carbon::now();
        ReplacementService::expire($now);

        $query = Shift::with(['leader', 'users.firefighter', 'users.replacedFirefighter'])
            ->where('status', 'active');

        if ($user->role === 'guardia' && $user->guardia_id) {
            $query->whereHas('leader', function ($q) use ($user) {
                $q->where('guardia_id', $user->guardia_id);
            });
        }

        $shift = $query->latest()->first();

        if ($shift) {
            $shift->setRelation(
                'users',
                $shift->users->filter(function ($shiftUser) use ($now) {
                    return !$shiftUser->user
                        || !$shiftUser->user->replacement_until
                        || $shiftUser->user->replacement_until->greaterThan($now);
                })->values()
            );
        }

        $onDutyShiftUsers = $shift
            ? $shift->users
                ->whereNull('end_time')
                ->filter(fn ($su) => (int) ($su->firefighter_id ?? 0) > 0)
                ->values()
            : collect();

        $payload = [
            'server_time' => $now->toIso8601String(),
            'shift' => $shift ? [
                'id' => $shift->id,
                'status' => $shift->status,
                'leader' => $shift->leader?->name,
                'created_at' => optional($shift->created_at)->toIso8601String(),
            ] : null,
            'bomberos' => $onDutyShiftUsers->map(function ($su) {
                $b = $su->firefighter;
                if (!$b) {
                    return null;
                }

                $name = trim((string)($b->nombres ?? '') . ' ' . (string)($b->apellido_paterno ?? '') . ' ' . (string)($b->apellido_materno ?? ''));

                $estado = $b->estado_asistencia ?? null;
                if (!$estado && property_exists($su, 'attendance_status')) {
                    $estado = $su->attendance_status;
                }

                return [
                    'id' => (int) $b->id,
                    'nombre' => $name,
                    'portatil' => $b->numero_portatil,
                    'estado_asistencia' => $estado ?: 'constituye',
                    'es_jefe_guardia' => (bool) ($b->es_jefe_guardia ?? false),
                    'es_refuerzo' => (bool) ($b->es_refuerzo ?? false),
                    'es_cambio' => (bool) ($b->es_cambio ?? false),
                    'es_sancion' => (bool) ($b->es_sancion ?? false),
                    'fuera_de_servicio' => (bool) ($b->fuera_de_servicio ?? false),
                    'en_turno' => true,
                ];
            })->filter()->values(),
        ];

        return response()->json($payload);
    }

    public function start(Request $request)
    {
        $authUser = Auth::user();

        $guardia = null;
        if ($authUser?->guardia_id) {
            $guardia = Guardia::find($authUser->guardia_id);
        }
        if (!$guardia) {
            $guardia = Guardia::where('is_active_week', true)->first();
        }

        if ($guardia) {
            $this->cleanupTransitoriosOnConstitution($guardia);
        }

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
            'firefighter_id' => 'required|exists:bomberos,id',
            'assignment_type' => 'required|string',
            'replaced_firefighter_id' => 'nullable|required_if:assignment_type,Reemplazo|exists:bomberos,id',
        ]);

        $exists = ShiftUser::where('shift_id', $id)
            ->where('firefighter_id', $request->firefighter_id)
            ->whereNull('end_time')
            ->exists();

        if ($exists) {
            return back()->withErrors(['firefighter_id' => 'El voluntario ya está activo en esta guardia.']);
        }

        $shift = Shift::with('leader')->findOrFail($id);
        $bombero = Bombero::findOrFail($request->firefighter_id);

        $legacyUserId = MapaBomberoUsuarioLegacy::where('firefighter_id', $bombero->id)->value('user_id');
        $legacyReplacedUserId = null;
        if (!empty($request->replaced_firefighter_id)) {
            $legacyReplacedUserId = MapaBomberoUsuarioLegacy::where('firefighter_id', (int) $request->replaced_firefighter_id)->value('user_id');
        }

        $attendanceStatus = 'constituye';
        if ($request->assignment_type === 'Reemplazo') {
            $attendanceStatus = 'reemplazo';
        }
        if ($request->assignment_type === 'Cumple falta') {
            $attendanceStatus = 'falta';
        }

        $shiftUserPayload = [
            'shift_id' => $id,
            'user_id' => $legacyUserId,
            'firefighter_id' => $bombero->id,
            'assignment_type' => $request->assignment_type,
            'replaced_user_id' => $legacyReplacedUserId,
            'replaced_firefighter_id' => $request->replaced_firefighter_id,
            'start_time' => now(),
            'present' => $request->assignment_type !== 'Cumple falta', // Asumo que cumple falta es no presente físicamente o algo así, pero lo dejaré true por defecto salvo que sea falta explícita
        ];

        if (Schema::hasColumn('shift_users', 'guardia_id')) {
            $shiftUserPayload['guardia_id'] = $shift->leader?->guardia_id ?? Auth::user()?->guardia_id ?? $bombero->guardia_id;
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
            ->where('firefighter_id', $userId)
            ->whereNull('end_time')
            ->firstOrFail();

        $shiftUser->update(['end_time' => now()]);

        return redirect()->route('guardia')->with('success', 'Salida registrada correctamente.');
    }
}
