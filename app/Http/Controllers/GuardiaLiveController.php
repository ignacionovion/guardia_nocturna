<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Guardia;
use App\Models\Bombero;
use App\Models\ReemplazoBombero;
use App\Models\TurnoSession;
use App\Models\TurnoSessionItem;
use App\Models\GuardiaAttendanceRecord;
use App\Models\BedAssignment;
use App\Models\Novelty;
use App\Models\SystemSetting;
use App\Services\TurnoDraftService;
use Carbon\Carbon;

class GuardiaLiveController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if (!$user || $user->role !== 'guardia') {
            return redirect()->route('dashboard');
        }

        $payload = $this->buildPayload($user);

        if (!$payload) {
            abort(403, 'Cuenta de guardia sin guardia asignada.');
        }

        return view('dashboard.live', [
            'initialState' => $payload,
        ]);
    }

    public function state(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['ok' => false], 401);
        }

        if ($user->role !== 'guardia') {
            return response()->json(['ok' => false], 403);
        }

        $payload = $this->buildPayload($user);

        if (!$payload) {
            return response()->json(['ok' => false, 'message' => 'Guardia no encontrada'], 403);
        }

        return response()->json(array_merge(['ok' => true], $payload));
    }

    private function resolveGuardiaId($user): ?int
    {
        $guardiaId = $user->guardia_id;

        if (!$guardiaId) {
            $guardiaId = Guardia::whereRaw('lower(name) = ?', [strtolower($user->name)])->value('id');
        }

        if (!$guardiaId) {
            $emailLocal = explode('@', (string) $user->email)[0] ?? '';
            $emailLocal = str_replace('.', ' ', $emailLocal);
            $guardiaId = Guardia::whereRaw('lower(name) = ?', [strtolower($emailLocal)])->value('id');
        }

        if (!$guardiaId) {
            $guardiaId = Guardia::where('is_active_week', true)->value('id');
        }

        return $guardiaId ? (int) $guardiaId : null;
    }

    private function buildPayload($user): ?array
    {
        $guardiaId = $this->resolveGuardiaId($user);
        if (!$guardiaId) {
            return null;
        }

        $guardia = Guardia::find($guardiaId);
        if (!$guardia) {
            return null;
        }

        $tz  = SystemSetting::getValue('guardia_schedule_tz', env('GUARDIA_SCHEDULE_TZ', config('app.timezone')));
        $now = now()->setTimezone($tz);

        // Active replacements
        $activeReplacements = ReemplazoBombero::with(['originalFirefighter', 'replacementFirefighter'])
            ->where('estado', 'activo')
            ->whereHas('originalFirefighter', fn ($q) => $q->where('guardia_id', $guardiaId))
            ->get();

        $replacementByOriginal    = $activeReplacements->keyBy(fn ($r) => (int) $r->bombero_titular_id);
        $replacementByReplacement = $activeReplacements->keyBy(fn ($r) => (int) $r->bombero_reemplazante_id);

        // All staff in this guardia
        $allStaff = Bombero::where('guardia_id', $guardiaId)
            ->orderBy('apellido_paterno')
            ->orderBy('nombres')
            ->get();

        // Active staff (not out-of-service, not replaced by someone else)
        $activeStaff = $allStaff
            ->reject(fn ($b) => ($b->fuera_de_servicio ?? false) || $replacementByOriginal->has($b->id))
            ->sortBy(fn ($b) => sprintf(
                '%d-%s-%s',
                ($replacementByReplacement->has($b->id) || ($b->es_refuerzo ?? false)) ? 1 : 0,
                $b->apellido_paterno ?? '',
                $b->nombres ?? ''
            ))
            ->values();

        // Bed assignments
        $bedByFirefighter = BedAssignment::whereNull('released_at')
            ->whereNotNull('firefighter_id')
            ->with('bed')
            ->get()
            ->keyBy('firefighter_id')
            ->map(fn ($a) => $a->bed?->number);

        // Draft session for confirm tokens and present count
        /** @var TurnoDraftService $draftService */
        $draftService = app(TurnoDraftService::class);
        $draftEditable = $draftService->isEditableNow();
        $opDate        = $draftService->resolveOperationalDate($now);

        $draftSession = TurnoSession::where('guardia_id', $guardiaId)
            ->whereDate('operational_date', $opDate->toDateString())
            ->first();

        $draftItemsByFirefighterId = $draftSession
            ? TurnoSessionItem::where('turno_session_id', $draftSession->id)->get()->keyBy('firefighter_id')
            : collect();

        $presentCount = $draftSession
            ? TurnoSessionItem::where('turno_session_id', $draftSession->id)
                ->whereIn('attendance_status', ['constituye', 'reemplazo'])
                ->where('included', true)
                ->count()
            : $activeStaff->filter(fn ($b) => in_array($b->estado_asistencia, ['constituye', 'reemplazo']))->count();

        // Attendance window
        $enableTime  = SystemSetting::getValue('attendance_enable_time', '22:00');
        $disableTime = SystemSetting::getValue('attendance_disable_time', '07:00');
        [$eH, $eM]   = array_map('intval', explode(':', (string) $enableTime));
        [$dH, $dM]   = array_map('intval', explode(':', (string) $disableTime));
        $nowMins     = $now->hour * 60 + $now->minute;
        $enableMins  = $eH * 60 + $eM;
        $disableMins = $dH * 60 + $dM;

        $attendanceEnabled = ($enableMins > $disableMins)
            ? ($nowMins >= $enableMins || $nowMins < $disableMins)
            : ($nowMins >= $enableMins && $nowMins < $disableMins);

        $hasAttendanceSavedToday = GuardiaAttendanceRecord::where('guardia_id', $guardiaId)
            ->whereDate('date', Carbon::today()->toDateString())
            ->exists();

        $endTime = SystemSetting::getValue('guardia_daily_end_time', '07:00');
        [$endH, $endM] = array_map('intval', explode(':', (string) $endTime));
        $dailyEndAt        = $now->copy()->setTime($endH, $endM, 0);
        $shiftClosedForToday = $now->greaterThanOrEqualTo($dailyEndAt);
        $isAfter2200         = $now->hour >= 22;

        if (!$attendanceEnabled) {
            $attendanceMessage = 'FUERA DE HORARIO DE REGISTRO';
            $attendanceVariant = 'default';
        } elseif ($hasAttendanceSavedToday) {
            $attendanceMessage = 'ASISTENCIA REGISTRADA';
            $attendanceVariant = 'success';
        } elseif ($shiftClosedForToday) {
            $attendanceMessage = 'RECORDAR REGISTRO 22:00';
            $attendanceVariant = 'warning';
        } else {
            $attendanceMessage = $isAfter2200 ? 'GUARDAR ANTES DE IRTE' : 'SIN REGISTRAR';
            $attendanceVariant = 'danger';
        }

        // Novelties
        $novelties = Novelty::with(['user', 'guardia'])
            ->notAcademy()
            ->where(fn ($q) => $q
                ->where('guardia_id', $guardiaId)
                ->orWhere('is_permanent', true)
                ->orWhereNull('guardia_id'))
            ->latest()
            ->take(5)
            ->get();

        // Academies
        $academies = Novelty::with(['user', 'firefighter'])
            ->academy()
            ->where('guardia_id', $guardiaId)
            ->latest()
            ->take(5)
            ->get();

        // Birthdays this month
        $birthdaysThisMonth = $allStaff
            ->filter(fn ($b) => $b->fecha_nacimiento && (int) $b->fecha_nacimiento->month === (int) $now->month)
            ->sortBy(fn ($b) => (int) $b->fecha_nacimiento->day)
            ->values();

        // Serialize staff
        $staffData = $activeStaff->map(function (Bombero $b) use (
            $bedByFirefighter, $draftItemsByFirefighterId, $replacementByReplacement
        ) {
            $draftItem = $draftItemsByFirefighterId->get($b->id);

            return [
                'id'                     => $b->id,
                'nombres'                => $b->nombres,
                'apellido_paterno'       => $b->apellido_paterno,
                'apellido_materno'       => $b->apellido_materno,
                'cargo'                  => $b->cargo,
                'rut'                    => $b->rut,
                'estado_asistencia'      => $b->estado_asistencia,
                'draft_attendance_status'=> $draftItem?->attendance_status,
                'draft_included'         => $draftItem ? (bool) $draftItem->included : null,
                'confirm_token'          => $draftItem?->confirm_token,
                'confirmed_at'           => $draftItem?->confirmed_at?->toISOString(),
                'es_jefe_guardia'        => (bool) ($b->es_jefe_guardia ?? false),
                'es_refuerzo'            => (bool) ($b->es_refuerzo ?? false),
                'es_reemplazo'           => $replacementByReplacement->has($b->id),
                'fuera_de_servicio'      => (bool) ($b->fuera_de_servicio ?? false),
                'es_titular'             => (bool) ($b->es_titular ?? true),
                'bed_number'             => $bedByFirefighter->get($b->id),
                'photo_path'             => $b->photo_path ?? null,
                'numero_portatil'        => $b->numero_portatil ?? null,
                'years_service' => isset($b->fecha_ingreso)
                ? (int) Carbon::parse($b->fecha_ingreso)->diffInYears(now())
                : null,
            'months_service' => isset($b->fecha_ingreso)
                ? (int) (Carbon::parse($b->fecha_ingreso)->diffInMonths(now()) % 12)
                : null,
                'es_conductor'           => (bool) ($b->es_conductor ?? false),
                'es_operador_rescate'    => (bool) ($b->es_operador_rescate ?? false),
                'es_asistente_trauma'    => (bool) ($b->es_asistente_trauma ?? false),
                'replacement_info'       => $replacementByReplacement->has($b->id) 
                    ? [
                        'id' => $replacementByReplacement->get($b->id)->id,
                        'original_name' => $replacementByReplacement->get($b->id)->originalFirefighter?->nombres . ' ' . $replacementByReplacement->get($b->id)->originalFirefighter?->apellido_paterno,
                    ]
                    : null,
            ];
        })->values();

        return [
            'guardia' => [
                'id'             => $guardia->id,
                'name'           => $guardia->name,
                'numero_guardia' => $guardia->numero_guardia ?? null,
            ],
            'staff'                  => $staffData,
            'visible_count'          => $activeStaff->count(),
            'present_count'          => $presentCount,
            'novelties'              => $novelties->map(fn ($n) => [
                'id'           => $n->id,
                'content'      => $n->content ?? $n->description ?? '',
                'type'         => $n->type ?? 'general',
                'is_permanent' => (bool) ($n->is_permanent ?? false),
                'created_at'   => $n->created_at?->toISOString(),
                'user_name'    => $n->user?->name,
            ])->values(),
            'academies'              => $academies->map(fn ($a) => [
                'id'               => $a->id,
                'content'          => $a->content ?? $a->description ?? '',
                'created_at'       => $a->created_at?->toISOString(),
                'firefighter_name' => $a->firefighter
                    ? trim(($a->firefighter->nombres ?? '') . ' ' . ($a->firefighter->apellido_paterno ?? ''))
                    : null,
            ])->values(),
            'birthdays_this_month'   => $birthdaysThisMonth->map(fn ($b) => [
                'id'    => $b->id,
                'name'  => trim(($b->nombres ?? '') . ' ' . ($b->apellido_paterno ?? '')),
                'cargo' => $b->cargo,
                'day'   => (int) $b->fecha_nacimiento->day,
            ])->values(),
            'bed_by_firefighter'     => $bedByFirefighter->toArray(),
            'attendance_enabled'     => $attendanceEnabled,
            'attendance_saved'       => $hasAttendanceSavedToday,
            'attendance_message'     => $attendanceMessage,
            'attendance_variant'     => $attendanceVariant,
            'draft_editable'         => $draftEditable,
            'local_time_iso'         => $now->toIso8601String(),
            'guardia_tz'             => $tz,
            'bulk_update_url'        => '/admin/guardias/' . $guardia->id . '/bulk-update',
        ];
    }

    /**
     * Get emergency history for a specific guardia
     */
    public function emergencies(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['ok' => false], 401);
        }

        if ($user->role !== 'guardia') {
            return response()->json(['ok' => false], 403);
        }

        $guardiaId = $request->query('guardia_id');
        if (!$guardiaId) {
            return response()->json(['ok' => false, 'message' => 'Guardia ID required'], 400);
        }

        // Verify the guardia belongs to this user
        $guardia = Guardia::where('id', $guardiaId)
            ->whereHas('users', fn ($q) => $q->where('users.id', $user->id))
            ->first();

        if (!$guardia) {
            return response()->json(['ok' => false, 'message' => 'Guardia not found'], 404);
        }

        $emergencies = \App\Models\Emergency::with(['key', 'units', 'officerInChargeFirefighter'])
            ->where('guardia_id', $guardiaId)
            ->orderBy('dispatched_at', 'desc')
            ->limit(50)
            ->get();

        return response()->json($emergencies->map(fn ($e) => [
            'id' => $e->id,
            'emergency_key' => $e->key ? [
                'id' => $e->key->id,
                'code' => $e->key->code,
                'description' => $e->key->description,
            ] : null,
            'dispatched_at' => $e->dispatched_at?->toISOString(),
            'arrived_at' => $e->arrived_at?->toISOString(),
            'call_details' => $e->details,
            'officer_in_charge' => $e->officerInChargeFirefighter ? [
                'id' => $e->officerInChargeFirefighter->id,
                'nombres' => $e->officerInChargeFirefighter->nombres,
                'apellido_paterno' => $e->officerInChargeFirefighter->apellido_paterno,
            ] : null,
            'units' => $e->units->map(fn ($u) => [
                'id' => $u->id,
                'name' => $u->name,
            ])->values(),
        ]));
    }

    /**
     * Get cleaning assignments for a specific guardia and date
     */
    public function cleaningAssignments(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['ok' => false], 401);
        }

        if ($user->role !== 'guardia') {
            return response()->json(['ok' => false], 403);
        }

        $guardiaId = $request->query('guardia_id');
        if (!$guardiaId) {
            return response()->json(['ok' => false, 'message' => 'Guardia ID required'], 400);
        }

        // Verify the guardia belongs to this user
        $guardia = Guardia::where('id', $guardiaId)
            ->whereHas('users', fn ($q) => $q->where('users.id', $user->id))
            ->first();

        if (!$guardia) {
            return response()->json(['ok' => false, 'message' => 'Guardia not found'], 404);
        }

        $date = $request->query('date') 
            ? Carbon::parse($request->query('date'))->startOfDay()
            : now()->startOfDay();

        $desiredTasks = [
            'Aseo Pieza N°1',
            'Aseo Pieza N°2', 
            'Aseo Pieza N°3',
            'Aseo Pieza N°4',
            'Aseo Pieza N°5',
            'Aseo Sector Duchas',
            'Aseo Sector Baños',
            'Aseo Sala de Estar',
            'Aseo Cocina Y Quincho',
        ];

        $tasks = \App\Models\CleaningTask::whereIn('name', $desiredTasks)
            ->orderByRaw('FIELD(name, ' . implode(',', array_fill(0, count($desiredTasks), '?')) . ')', $desiredTasks)
            ->get();

        // Create mapping: 1-based index (like frontend) -> task_id
        $taskIdByIndex = [];
        foreach ($tasks as $index => $task) {
            $taskIdByIndex[$index + 1] = $task->id; // Frontend uses 1-9
        }

        $assignments = \App\Models\CleaningAssignment::whereDate('assigned_date', $date->toDateString())
            ->whereIn('cleaning_task_id', $tasks->pluck('id'))
            ->get();

        // Map assignments by frontend index (1-9) instead of DB task_id
        $assignmentsByIndex = [];
        foreach ($assignments as $assignment) {
            // Find which index (1-9) this task_id corresponds to
            $index = array_search($assignment->cleaning_task_id, $taskIdByIndex);
            if ($index !== false) {
                $assignmentsByIndex[$index] = $assignment->firefighter_id;
            }
        }

        return response()->json([
            'ok' => true,
            'assignments' => $assignmentsByIndex,
            'date' => $date->toDateString(),
        ]);
    }
}
