<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Guardia;
use App\Models\GuardiaCalendarDay;
use App\Models\Bombero;
use App\Models\User;
use App\Models\Shift;
use App\Models\ShiftUser;
use App\Models\StaffEvent;
use App\Models\GuardiaAttendanceRecord;
use App\Models\MapaBomberoUsuarioLegacy;
use App\Models\ReemplazoBombero;
use App\Models\SystemSetting;
use App\Services\ReplacementService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class AdministradorController extends Controller
{
    private function resolveGuardiaIdForGuardiaUser(User $user): ?int
    {
        if ($user->role !== 'guardia') {
            return $user->guardia_id ? (int) $user->guardia_id : null;
        }
        if ($user->guardia_id) {
            return (int) $user->guardia_id;
        }

        $byName = Guardia::whereRaw('lower(name) = ?', [strtolower((string) $user->name)])->value('id');
        if ($byName) {
            return (int) $byName;
        }

        $emailLocal = explode('@', (string) $user->email)[0] ?? '';
        $emailLocal = str_replace('.', ' ', $emailLocal);
        $byEmail = Guardia::whereRaw('lower(name) = ?', [strtolower((string) $emailLocal)])->value('id');
        if ($byEmail) {
            return (int) $byEmail;
        }

        return null;
    }

    public function toggleFueraDeServicio($id)
    {
        $user = auth()->user();
        if (!in_array($user->role, ['super_admin', 'capitania', 'guardia'], true)) {
            abort(403, 'No autorizado.');
        }

        $firefighter = Bombero::findOrFail($id);

        if ($user->role === 'guardia' && (int) $firefighter->guardia_id !== (int) $user->guardia_id) {
            abort(403, 'No autorizado.');
        }

        $firefighter->fuera_de_servicio = !$firefighter->fuera_de_servicio;
        $firefighter->save();

        StaffEvent::create([
            'firefighter_id' => $firefighter->id,
            'type' => 'service_status',
            'start_date' => now(),
            'end_date' => null,
            'description' => $firefighter->fuera_de_servicio ? 'inhabilitado' : 'habilitado',
            'status' => 'approved',
            'user_id' => $user->id,
        ]);

        $status = $firefighter->fuera_de_servicio ? 'FUERA DE SERVICIO' : 'EN SERVICIO';
        return redirect()->back()->with('success', "Estado actualizado: {$firefighter->nombres} ahora está {$status}.");
    }

    private function resolveActiveGuardia($now)
    {
        $weekStart = $now->copy()->startOfWeek(Carbon::SUNDAY);

        $calendarDay = GuardiaCalendarDay::with('guardia')
            ->where('date', $weekStart->toDateString())
            ->first();

        if (!$calendarDay) {
            $calendarDay = GuardiaCalendarDay::with('guardia')
                ->where('date', $now->toDateString())
                ->first();
        }

        if ($calendarDay && $calendarDay->guardia) {
            return $calendarDay->guardia;
        }

        return Guardia::where('is_active_week', true)->first();
    }

    public function index()
    {
        // Limpieza automática de reemplazos vencidos
        ReplacementService::expire();

        $now = Carbon::now();

        $user = auth()->user();

        if (!in_array($user->role, ['super_admin', 'capitania', 'guardia'], true)) {
            abort(403, 'No tienes permiso para acceder a esta sección.');
        }

        $query = Guardia::with(['bomberos' => function ($q) {
            $orderFechaIngreso = Schema::hasColumn('bomberos', 'fecha_ingreso') ? 'fecha_ingreso' : 'admission_date';
            $q->where(function ($q2) {
                $q2->whereNull('fuera_de_servicio')->orWhere('fuera_de_servicio', false);
            });
            $q->orderBy($orderFechaIngreso, 'asc');
        }]);

        // Si es cuenta de Guardia, filtrar solo su propia guardia
        if ($user->role === 'guardia') {
            $query->where('id', $user->guardia_id);
        }

        $guardias = $query->get();

        $activeGuardia = $this->resolveActiveGuardia($now);

        $activeReplacements = ReemplazoBombero::with(['originalFirefighter', 'replacementFirefighter'])
            ->where('estado', 'activo')
            ->get();

        $replacementByOriginal = $activeReplacements->keyBy('bombero_titular_id');
        $replacementByReplacement = $activeReplacements->keyBy('bombero_reemplazante_id');

        $nombreCol = Schema::hasColumn('bomberos', 'nombres') ? 'nombres' : 'name';
        $apellidoPaternoCol = Schema::hasColumn('bomberos', 'apellido_paterno') ? 'apellido_paterno' : 'last_name_paternal';

        $volunteers = Bombero::query()
            ->where(function ($q) {
                $q->whereNull('fuera_de_servicio')->orWhere('fuera_de_servicio', false);
            })
            ->orderBy($nombreCol)
            ->orderBy($apellidoPaternoCol)
            ->get();

        return view('admin.guardias', compact('guardias', 'volunteers', 'activeGuardia', 'replacementByOriginal', 'replacementByReplacement'));
    }

    public function dotaciones()
    {
        ReplacementService::expire();

        $user = auth()->user();

        if (!in_array($user->role, ['super_admin', 'capitania', 'guardia'], true)) {
            abort(403, 'No tienes permiso para acceder a esta sección.');
        }

        $query = Guardia::with(['bomberos' => function ($q) {
            $orderFechaIngreso = Schema::hasColumn('bomberos', 'fecha_ingreso') ? 'fecha_ingreso' : 'admission_date';
            $q->where(function ($q2) {
                $q2->whereNull('fuera_de_servicio')->orWhere('fuera_de_servicio', false);
            });
            $q->orderBy($orderFechaIngreso, 'asc');
        }]);

        if ($user->role === 'guardia') {
            $query->where('id', $user->guardia_id);
        }

        $guardias = $query->get();

        $nombreCol = Schema::hasColumn('bomberos', 'nombres') ? 'nombres' : 'name';
        $apellidoPaternoCol = Schema::hasColumn('bomberos', 'apellido_paterno') ? 'apellido_paterno' : 'last_name_paternal';

        $volunteers = Bombero::query()
            ->where(function ($q) {
                $q->whereNull('fuera_de_servicio')->orWhere('fuera_de_servicio', false);
            })
            ->orderBy($nombreCol)
            ->orderBy($apellidoPaternoCol)
            ->get();

        return view('admin.dotaciones', compact('guardias', 'volunteers'));
    }

    /**
     * Libera automáticamente a los reemplazos (no titulares) después de las 07:00 AM
     * si fueron asignados antes de ese horario (pertenecen al turno anterior).
     */
    private function releaseExpiredReplacements()
    {
        ReplacementService::expire();
    }

    public function assignBombero(Request $request)
    {
        $user = auth()->user();

        if (!in_array($user->role, ['super_admin', 'capitania', 'guardia'], true)) {
            abort(403, 'No autorizado.');
        }

        try {
            $validated = $request->validate([
                'guardia_id' => 'required|exists:guardias,id',
                'firefighter_id' => 'required|exists:bomberos,id',
            ]);
        } catch (ValidationException $e) {
            return redirect()->route('admin.dotaciones')->withErrors(['msg' => 'No se pudo asignar el voluntario. Verifica los datos y reintenta.']);
        }

        // Si es cuenta de guardia, asegurar que solo asigna a SU guardia
        if ($user->role === 'guardia') {
            $userGuardiaId = $this->resolveGuardiaIdForGuardiaUser($user);
            if (!$userGuardiaId || (int) $validated['guardia_id'] !== (int) $userGuardiaId) {
                abort(403, 'No puedes asignar personal a otra guardia.');
            }
        }

        $firefighter = Bombero::find($validated['firefighter_id']);

        if (!$firefighter) {
            return redirect()->route('admin.dotaciones')->withErrors(['msg' => 'No se encontró el voluntario a quitar (puede haber sido actualizado o eliminado).']);
        }
        $firefighter->update([
            'guardia_id' => $validated['guardia_id'],
            'estado_asistencia' => 'constituye',
            'es_titular' => true,
            'es_cambio' => false,
            'es_sancion' => false,
        ]);

        return redirect()->back()->with('success', 'Voluntario asignado correctamente a la guardia.');
    }

    public function unassignBombero(Request $request)
    {
        $user = auth()->user();

        if ($request->isMethod('get')) {
            return redirect()->route('admin.dotaciones')->withErrors(['msg' => 'Acción inválida (método HTTP no permitido). Actualiza la página e intenta nuevamente.']);
        }

        if (!in_array($user->role, ['super_admin', 'capitania', 'guardia'], true)) {
            abort(403, 'No autorizado.');
        }

        $validated = $request->validate([
            'guardia_id' => 'required|exists:guardias,id',
            'firefighter_id' => 'required|exists:bomberos,id',
        ]);

        if ($user->role === 'guardia') {
            $userGuardiaId = $this->resolveGuardiaIdForGuardiaUser($user);
            if (!$userGuardiaId || (int) $validated['guardia_id'] !== (int) $userGuardiaId) {
                abort(403, 'No puedes quitar personal de otra guardia.');
            }
        }

        $firefighter = Bombero::find($validated['firefighter_id']);

        if (!$firefighter) {
            return redirect()->route('admin.dotaciones')->withErrors(['msg' => 'No se encontró el voluntario a quitar (puede haber sido actualizado o eliminado).']);
        }

        if ((int) $firefighter->guardia_id !== (int) $validated['guardia_id']) {
            return redirect()->route('admin.dotaciones')->withErrors(['msg' => 'El voluntario no pertenece a esa guardia.']);
        }

        $firefighter->update([
            'guardia_id' => null,
            'estado_asistencia' => 'constituye',
            'es_jefe_guardia' => false,
            'es_refuerzo' => false,
            'refuerzo_guardia_anterior_id' => null,
            'es_cambio' => false,
            'es_sancion' => false,
        ]);

        return redirect()->route('admin.dotaciones')->with('success', 'Voluntario quitado de la guardia.');
    }

    public function assignRefuerzo(Request $request)
    {
        $user = auth()->user();

        if (!in_array($user->role, ['super_admin', 'capitania', 'guardia'], true)) {
            abort(403, 'No autorizado.');
        }

        $validated = $request->validate([
            'guardia_id' => 'required|exists:guardias,id',
            'firefighter_id' => 'required|exists:bomberos,id',
        ]);

        if ($user->role === 'guardia') {
            $userGuardiaId = $this->resolveGuardiaIdForGuardiaUser($user);
            if (!$userGuardiaId || (int) $validated['guardia_id'] !== (int) $userGuardiaId) {
                abort(403, 'No puedes gestionar otra guardia.');
            }
        }

        $guardia = Guardia::findOrFail($validated['guardia_id']);
        $firefighter = Bombero::findOrFail($validated['firefighter_id']);

        if ((int) $firefighter->guardia_id === (int) $guardia->id) {
            return back()->withErrors(['msg' => 'El voluntario ya pertenece a esta guardia.']);
        }

        $hasActive = ReemplazoBombero::query()
            ->where('estado', 'activo')
            ->where(function ($q) use ($firefighter) {
                $q->where('bombero_titular_id', $firefighter->id)
                    ->orWhere('bombero_reemplazante_id', $firefighter->id);
            })
            ->exists();
        if ($hasActive) {
            return back()->withErrors(['msg' => 'Este voluntario está involucrado en un reemplazo activo y no puede agregarse como refuerzo.']);
        }

        $prevGuardiaId = $firefighter->guardia_id;

        $firefighter->update([
            'guardia_id' => $guardia->id,
            'estado_asistencia' => 'constituye',
            'es_titular' => false,
            'es_jefe_guardia' => false,
            'es_refuerzo' => true,
            'refuerzo_guardia_anterior_id' => $prevGuardiaId,
            'es_cambio' => false,
            'es_sancion' => false,
        ]);

        return back()->with('success', "Refuerzo agregado: {$firefighter->nombres} {$firefighter->apellido_paterno}.");
    }

    public function removeRefuerzo(Request $request)
    {
        $user = auth()->user();

        if (!in_array($user->role, ['super_admin', 'capitania', 'guardia'], true)) {
            abort(403, 'No autorizado.');
        }

        $validated = $request->validate([
            'guardia_id' => 'required|exists:guardias,id',
            'firefighter_id' => 'required|exists:bomberos,id',
        ]);

        if ($user->role === 'guardia') {
            $userGuardiaId = $this->resolveGuardiaIdForGuardiaUser($user);
            if (!$userGuardiaId || (int) $validated['guardia_id'] !== (int) $userGuardiaId) {
                abort(403, 'No puedes gestionar otra guardia.');
            }
        }

        $firefighter = Bombero::findOrFail($validated['firefighter_id']);

        if ((int) $firefighter->guardia_id !== (int) $validated['guardia_id']) {
            return back()->withErrors(['msg' => 'El voluntario no pertenece a esta guardia.']);
        }

        if (!$firefighter->es_refuerzo) {
            return back()->withErrors(['msg' => 'El voluntario no es refuerzo.']);
        }

        $prevGuardiaId = $firefighter->refuerzo_guardia_anterior_id;

        DB::transaction(function () use ($firefighter, $prevGuardiaId) {
            $firefighter->update([
                'guardia_id' => $prevGuardiaId,
                'estado_asistencia' => 'constituye',
                'es_refuerzo' => false,
                'refuerzo_guardia_anterior_id' => null,
                'es_jefe_guardia' => false,
                'es_cambio' => false,
                'es_sancion' => false,
            ]);
        });

        return back()->with('success', 'Refuerzo quitado correctamente.');
    }

    public function toggleTitular($id)
    {
        $user = auth()->user();
        if (!in_array($user->role, ['super_admin', 'capitania', 'guardia'], true)) {
            abort(403, 'No autorizado.');
        }

        $firefighter = Bombero::findOrFail($id);

        if ($user->role === 'guardia' && (int) $firefighter->guardia_id !== (int) $user->guardia_id) {
            abort(403, 'No autorizado.');
        }

        $firefighter->es_titular = !$firefighter->es_titular;
        $firefighter->save();

        $status = $firefighter->es_titular ? 'TITULAR' : 'TRANSITORIO';
        return redirect()->back()->with('success', "Estado de titularidad actualizado: {$firefighter->nombres} ahora es {$status}.");
    }

    public function assignReplacement(Request $request)
    {
        $user = auth()->user();

        if (!in_array($user->role, ['super_admin', 'capitania', 'guardia'], true)) {
            abort(403, 'No autorizado.');
        }

        $validated = $request->validate([
            'guardia_id' => 'required|exists:guardias,id',
            'original_firefighter_id' => 'required|exists:bomberos,id',
            'replacement_firefighter_id' => 'required|exists:bomberos,id',
        ]);

        // Verificar permisos de guardia
        if ($user->role === 'guardia') {
            $userGuardiaId = $this->resolveGuardiaIdForGuardiaUser($user);
            if (!$userGuardiaId || (int) $validated['guardia_id'] !== (int) $userGuardiaId) {
                abort(403, 'No puedes gestionar otra guardia.');
            }
        }

        $guardia = Guardia::findOrFail($validated['guardia_id']);
        $original = Bombero::findOrFail($validated['original_firefighter_id']);
        $replacement = Bombero::findOrFail($validated['replacement_firefighter_id']);

        if ((int) $replacement->id === (int) $original->id) {
            return back()->withErrors(['msg' => 'No puedes seleccionar al mismo voluntario como reemplazante.']);
        }

        if ((int) $original->guardia_id !== (int) $guardia->id) {
            return back()->withErrors(['msg' => 'El bombero a reemplazar no pertenece a esta guardia.']);
        }

        $hasActive = ReemplazoBombero::where('bombero_titular_id', $original->id)
            ->where('estado', 'activo')
            ->exists();
        if ($hasActive) {
            return back()->withErrors(['msg' => 'Este voluntario ya se encuentra reemplazado actualmente.']);
        }

        $endsAt = ReplacementService::calculateReplacementUntil(Carbon::now());

        $shift = Shift::where('status', 'active')->latest()->first();

        DB::transaction(function () use ($guardia, $original, $replacement, $endsAt, $shift) {
            $replacementPreviousGuardiaId = $replacement->guardia_id;
            ReemplazoBombero::create([
                'guardia_id' => $guardia->id,
                'bombero_titular_id' => $original->id,
                'bombero_reemplazante_id' => $replacement->id,
                'inicio' => Carbon::now(),
                'fin' => $endsAt,
                'estado' => 'activo',
                'notas' => json_encode([
                    'replacement_previous_guardia_id' => $replacementPreviousGuardiaId,
                ]),
            ]);

            $replacement->update([
                'guardia_id' => $guardia->id,
                'estado_asistencia' => 'constituye',
                'es_titular' => false,
                'es_jefe_guardia' => false,
                'es_cambio' => false,
                'es_sancion' => false,
            ]);

            $original->update([
                'estado_asistencia' => 'ausente',
                'es_jefe_guardia' => false,
                'es_cambio' => false,
                'es_sancion' => false,
            ]);

            if ($shift) {
                $replacementUserId = MapaBomberoUsuarioLegacy::where('firefighter_id', $replacement->id)->value('user_id');
                $originalUserId = MapaBomberoUsuarioLegacy::where('firefighter_id', $original->id)->value('user_id');

                ShiftUser::updateOrCreate(
                    [
                        'shift_id' => $shift->id,
                        'firefighter_id' => $replacement->id,
                    ],
                    [
                        'user_id' => $replacementUserId,
                        'guardia_id' => $guardia->id,
                        'attendance_status' => 'constituye',
                        'assignment_type' => 'Reemplazo',
                        'replaced_user_id' => $originalUserId,
                        'replaced_firefighter_id' => $original->id,
                        'present' => true,
                        'start_time' => $shift->created_at,
                        'end_time' => null,
                    ]
                );
            }
        });

        return redirect()->back()->with('success', "Reemplazo asignado: {$replacement->nombres} reemplaza a {$original->nombres}.");
    }

    public function undoReplacement(Request $request, ReemplazoBombero $replacement)
    {
        $user = auth()->user();

        if (!in_array($user->role, ['super_admin', 'capitania', 'guardia'], true)) {
            abort(403, 'No autorizado.');
        }

        $replacement->loadMissing(['originalFirefighter', 'replacementFirefighter']);

        if ($replacement->estado !== 'activo') {
            return back()->withErrors(['msg' => 'El reemplazo ya no está activo.']);
        }

        if ($user->role === 'guardia') {
            $userGuardiaId = $this->resolveGuardiaIdForGuardiaUser($user);
            if (!$userGuardiaId || (int) $replacement->guardia_id !== (int) $userGuardiaId) {
                abort(403, 'No puedes gestionar otra guardia.');
            }
        }

        $guardia = Guardia::findOrFail($replacement->guardia_id);
        $original = $replacement->originalFirefighter;
        $replacer = $replacement->replacementFirefighter;

        if (!$original || !$replacer) {
            return back()->withErrors(['msg' => 'Reemplazo inválido (faltan datos de voluntarios).']);
        }

        $shift = Shift::where('status', 'active')->latest()->first();

        DB::transaction(function () use ($replacement, $guardia, $original, $replacer, $shift) {
            $replacement->update([
                'estado' => 'cerrado',
                'fin' => Carbon::now(),
            ]);

            // Volver el original a constituye (visible nuevamente en la guardia)
            if ((int) $original->guardia_id === (int) $guardia->id) {
                $original->update([
                    'estado_asistencia' => 'constituye',
                    'es_jefe_guardia' => false,
                    'es_cambio' => false,
                    'es_sancion' => false,
                ]);
            }

            // El reemplazante vuelve a su guardia original.
            // Preferimos el valor persistido en notas (más confiable), y como fallback usamos el user legacy.
            $originalReplacerGuardiaId = null;
            if ($replacement->notas) {
                $decodedNotes = $replacement->notas ? json_decode((string) $replacement->notas, true) : null;
                if (is_array($decodedNotes) && array_key_exists('replacement_previous_guardia_id', $decodedNotes)) {
                    $originalReplacerGuardiaId = $decodedNotes['replacement_previous_guardia_id'];
                }
            }

            if ($originalReplacerGuardiaId === null) {
                $originalReplacerGuardiaId = MapaBomberoUsuarioLegacy::query()
                    ->join('users', 'users.id', '=', 'mapa_bombero_usuario_legacy.user_id')
                    ->where('mapa_bombero_usuario_legacy.firefighter_id', $replacer->id)
                    ->value('users.guardia_id');
            }

            $replacerUserId = MapaBomberoUsuarioLegacy::where('firefighter_id', $replacer->id)->value('user_id');

            $replacer->update([
                'guardia_id' => $originalReplacerGuardiaId,
                'estado_asistencia' => 'constituye',
                'es_titular' => false,
                'es_jefe_guardia' => false,
                'es_cambio' => false,
                'es_sancion' => false,
            ]);

            // Limpieza legacy: evitar que el dashboard reimporte el reemplazo desde users.job_replacement_id
            if ($replacerUserId) {
                User::where('id', $replacerUserId)->update([
                    'job_replacement_id' => null,
                    'replacement_until' => null,
                    'attendance_status' => 'constituye',
                    'original_guardia_id' => null,
                    'original_attendance_status' => null,
                    'original_is_titular' => null,
                    'original_is_shift_leader' => null,
                    'original_is_exchange' => null,
                    'original_is_penalty' => null,
                    'original_job_replacement_id' => null,
                    'original_role' => null,
                ]);
            }

            if ($shift) {
                $replacementUserId = MapaBomberoUsuarioLegacy::where('firefighter_id', $replacer->id)->value('user_id');

                ShiftUser::where('shift_id', $shift->id)
                    ->where('firefighter_id', $replacer->id)
                    ->update([
                        'guardia_id' => $originalReplacerGuardiaId,
                        'attendance_status' => 'constituye',
                        'assignment_type' => null,
                        'replaced_user_id' => null,
                        'replaced_firefighter_id' => null,
                        'user_id' => $replacementUserId,
                    ]);
            }
        });

        return redirect()->back()->with('success', 'Reemplazo deshecho correctamente.');
    }

    public function cleanupReplacements(Request $request, Guardia $guardia)
    {
        $user = auth()->user();

        if (!in_array($user->role, ['super_admin', 'capitania', 'guardia'], true)) {
            abort(403, 'No autorizado.');
        }

        if ($user->role === 'guardia') {
            $userGuardiaId = $this->resolveGuardiaIdForGuardiaUser($user);
            if (!$userGuardiaId || (int) $guardia->id !== (int) $userGuardiaId) {
                abort(403, 'No puedes gestionar otra guardia.');
            }
        }

        $shift = Shift::where('status', 'active')->latest()->first();

        $activeReplacements = ReemplazoBombero::with(['originalFirefighter', 'replacementFirefighter'])
            ->where('guardia_id', $guardia->id)
            ->where('estado', 'activo')
            ->get();

        DB::transaction(function () use ($guardia, $activeReplacements, $shift) {
            foreach ($activeReplacements as $rep) {
                $rep->update([
                    'estado' => 'cerrado',
                    'fin' => Carbon::now(),
                ]);

                $original = $rep->originalFirefighter;
                $replacer = $rep->replacementFirefighter;

                if ($original && (int) $original->guardia_id === (int) $guardia->id) {
                    $original->update([
                        'estado_asistencia' => 'constituye',
                        'es_jefe_guardia' => false,
                        'es_cambio' => false,
                        'es_sancion' => false,
                    ]);
                }

                if ($replacer) {
                    $prevGuardiaId = null;
                    if ($rep->notas) {
                        $decodedNotes = json_decode((string) $rep->notas, true);
                        if (is_array($decodedNotes) && array_key_exists('replacement_previous_guardia_id', $decodedNotes)) {
                            $prevGuardiaId = $decodedNotes['replacement_previous_guardia_id'];
                        }
                    }

                    // Si no hay info de origen, lo sacamos de la guardia actual
                    if ((int) $replacer->guardia_id === (int) $guardia->id) {
                        $replacer->update([
                            'guardia_id' => $prevGuardiaId,
                            'estado_asistencia' => 'constituye',
                            'es_titular' => false,
                            'es_jefe_guardia' => false,
                            'es_cambio' => false,
                            'es_sancion' => false,
                        ]);
                    }

                    $replacerUserId = MapaBomberoUsuarioLegacy::where('firefighter_id', $replacer->id)->value('user_id');
                    if ($replacerUserId) {
                        User::where('id', $replacerUserId)->update([
                            'job_replacement_id' => null,
                            'replacement_until' => null,
                            'attendance_status' => 'constituye',
                            'original_guardia_id' => null,
                            'original_attendance_status' => null,
                            'original_is_titular' => null,
                            'original_is_shift_leader' => null,
                            'original_is_exchange' => null,
                            'original_is_penalty' => null,
                            'original_job_replacement_id' => null,
                            'original_role' => null,
                        ]);
                    }

                    if ($shift) {
                        ShiftUser::where('shift_id', $shift->id)
                            ->where('firefighter_id', $replacer->id)
                            ->update([
                                'guardia_id' => $prevGuardiaId,
                                'attendance_status' => 'constituye',
                                'assignment_type' => null,
                                'replaced_user_id' => null,
                                'replaced_firefighter_id' => null,
                            ]);
                    }
                }
            }
        });

        return redirect()->back()->with('success', 'Reemplazos cerrados y dotación restaurada.');
    }

    private function calculateReplacementUntil(Carbon $now): Carbon
    {
        return ReplacementService::calculateReplacementUntil($now);
    }

    // --- CRUD Guardias ---

    public function storeGuardia(Request $request)
    {
        if (!in_array(auth()->user()->role, ['super_admin', 'capitania'], true)) {
            abort(403, 'No autorizado.');
        }

        $request->validate(['name' => 'required|string|max:255|unique:guardias,name']);

        $guardia = Guardia::create(['name' => $request->name]);

        // Crear usuario automático para gestión de la guardia
        $baseUsername = Str::slug((string) $request->name);
        if ($baseUsername === '') {
            $baseUsername = 'guardia';
        }

        $username = $baseUsername;
        $i = 2;
        while (User::where('username', $username)->exists()) {
            $username = $baseUsername . '-' . $i;
            $i++;
        }

        User::create([
            'name' => $request->name,
            'username' => $username,
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
        if (!in_array(auth()->user()->role, ['super_admin', 'capitania'], true)) {
            abort(403, 'No autorizado.');
        }

        $guardia = Guardia::findOrFail($id);
        
        return view('admin.guardias.edit', compact('guardia'));
    }

    public function updateGuardia(Request $request, $id)
    {
         if (!in_array(auth()->user()->role, ['super_admin', 'capitania'], true)) {
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
         if (!in_array(auth()->user()->role, ['super_admin', 'capitania'], true)) {
            abort(403, 'No autorizado.');
        }

        $guardia = Guardia::findOrFail($id);
        
        // Verificar si tiene personal asignado (excluyendo el usuario de gestión de la guardia)
        $usersCount = $guardia->bomberos()->count();

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
        if (!in_array(auth()->user()->role, ['super_admin', 'capitania'], true)) {
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
        $transitorios = Bombero::where('guardia_id', $guardia->id)
            ->where('es_titular', false)
            ->get();

        foreach ($transitorios as $user) {
            $user->update([
                'guardia_id' => null,
                'estado_asistencia' => 'constituye',
                'es_jefe_guardia' => false,
                'es_cambio' => false,
                'es_sancion' => false,
            ]);
        }

        // 2. Titulares (Dotación permanente)
        // Se quedan, pero se limpia su estado del turno
        $titulares = Bombero::where('guardia_id', $guardia->id)
            ->where('es_titular', true)
            ->get();

        foreach ($titulares as $user) {
            $user->update([
                'estado_asistencia' => 'constituye', // Vuelven a estado base
                'es_jefe_guardia' => false,
                'es_cambio' => false,
                'es_sancion' => false,
            ]);
        }
    }

    /**
     * Al constituir guardia (horario de inicio), se deben remover los NO titulares
     * para que solo quede la dotación titular. Esto evita que arrastren reemplazos
     * del turno anterior.
     *
     * Horario:
     * - Lun-Sáb: 23:00
     * - Dom: 22:00
     */
    private function cleanupTransitoriosOnConstitution(Guardia $guardia): void
    {
        $now = Carbon::now();

        $scheduleHourToday = $now->isSunday() ? 22 : 23;
        $todayCutoff = $now->copy()->startOfDay()->addHours($scheduleHourToday);

        // Buscar el último horario programado (hoy si ya pasó, si no ayer)
        if ($now->greaterThanOrEqualTo($todayCutoff)) {
            $cutoff = $todayCutoff;
        } else {
            $yesterday = $now->copy()->subDay();
            $scheduleHourYesterday = $yesterday->isSunday() ? 22 : 23;
            $cutoff = $yesterday->copy()->startOfDay()->addHours($scheduleHourYesterday);
        }

        // Solo ejecutar si estamos razonablemente cerca del inicio del turno
        // (evita borrar transitorios en cualquier momento del día)
        if ($now->diffInHours($cutoff) > 8) {
            return;
        }

        $transitorios = Bombero::where('guardia_id', $guardia->id)
            ->where('es_titular', false)
            ->where('updated_at', '<', $cutoff)
            ->get();

        foreach ($transitorios as $user) {
            $user->update([
                'guardia_id' => null,
                'estado_asistencia' => 'constituye',
                'es_jefe_guardia' => false,
                'es_cambio' => false,
                'es_sancion' => false,
            ]);
        }
    }

    public function bulkUpdateGuardia(Request $request, $id)
    {
        if (!in_array(auth()->user()->role, ['super_admin', 'capitania', 'guardia'], true)) {
            abort(403, 'No autorizado.');
        }

        $tz = SystemSetting::getValue('guardia_schedule_tz', env('GUARDIA_SCHEDULE_TZ', config('app.timezone')));
        $now = Carbon::now($tz);
        $attendanceEnableTime = SystemSetting::getValue('attendance_enable_time', '21:00');
        $attendanceDisableTime = SystemSetting::getValue('attendance_disable_time', '10:00');

        [$enableH, $enableM] = array_map('intval', explode(':', (string) $attendanceEnableTime));
        [$disableH, $disableM] = array_map('intval', explode(':', (string) $attendanceDisableTime));

        $enableAt = $now->copy()->setTime($enableH, $enableM, 0);
        $disableAt = $now->copy()->setTime($disableH, $disableM, 0);

        $attendanceEnabled = (function () use ($now, $enableAt, $disableAt) {
            if ($enableAt->lessThan($disableAt)) {
                return $now->greaterThanOrEqualTo($enableAt) && $now->lessThan($disableAt);
            }
            return $now->greaterThanOrEqualTo($enableAt) || $now->lessThan($disableAt);
        })();

        if (!$attendanceEnabled) {
            return redirect()->back()->with('warning', 'Guardar asistencia está habilitado desde las ' . $attendanceEnableTime . '.');
        }

        $guardia = Guardia::findOrFail($id);
        
        // Si es cuenta de guardia, verificar propiedad
        if (auth()->user()->role === 'guardia') {
            $userGuardiaId = $this->resolveGuardiaIdForGuardiaUser(auth()->user());
            if (!$userGuardiaId || (int) $userGuardiaId !== (int) $guardia->id) {
                abort(403, 'No autorizado.');
            }
        }

        $data = $request->validate([
            'users' => 'required|array',
            'users.*.estado_asistencia' => 'required|string',
        ]);

        $shiftQuery = Shift::query();
        if (method_exists(Shift::class, 'firefighters')) {
            $shiftQuery->with('firefighters');
        }

        $shift = $shiftQuery->where('status', 'active')
            ->latest()
            ->first();

        if (!$shift) {
            $shift = Shift::create([
                'date' => Carbon::today(),
                'status' => 'active',
                'shift_leader_id' => auth()->id(),
                'notes' => 'Guardia constituida manualmente',
            ]);
        }

        DB::transaction(function () use ($data, $guardia, $shift) {
            $lockedReplacementIds = ReemplazoBombero::query()
                ->where('estado', 'activo')
                ->pluck('bombero_reemplazante_id')
                ->filter()
                ->map(fn ($v) => (int) $v)
                ->values()
                ->toArray();

            foreach ($data['users'] as $firefighterId => $attributes) {
                $firefighter = Bombero::find($firefighterId);
                if (!$firefighter || (int) $firefighter->guardia_id !== (int) $guardia->id) {
                    continue;
                }

                $attendanceStatus = $attributes['estado_asistencia'] ?? 'constituye';

                // Enforce invariant: refuerzos y reemplazantes activos siempre constituyen
                if ((bool) ($firefighter->es_refuerzo ?? false) || in_array((int) $firefighter->id, $lockedReplacementIds, true)) {
                    $attendanceStatus = 'constituye';
                }

                $firefighter->update([
                    'estado_asistencia' => $attendanceStatus,
                ]);

                $userId = MapaBomberoUsuarioLegacy::where('firefighter_id', $firefighter->id)->value('user_id');

                $shiftUserPayload = [
                    'assignment_type' => $attendanceStatus,
                    'present' => $attendanceStatus !== 'ausente' && $attendanceStatus !== 'permiso' && $attendanceStatus !== 'licencia',
                    'start_time' => $shift->created_at,
                    'end_time' => null,
                    'firefighter_id' => $firefighter->id,
                ];

                if ($userId) {
                    $shiftUserPayload['user_id'] = $userId;
                }

                if (Schema::hasColumn('shift_users', 'guardia_id')) {
                    $shiftUserPayload['guardia_id'] = $guardia->id;
                }
                if (Schema::hasColumn('shift_users', 'attendance_status')) {
                    $shiftUserPayload['attendance_status'] = $attendanceStatus;
                }

                ShiftUser::updateOrCreate(
                    [
                        'shift_id' => $shift->id,
                        'firefighter_id' => $firefighter->id,
                    ],
                    $shiftUserPayload
                );
            }

            GuardiaAttendanceRecord::updateOrCreate(
                [
                    'guardia_id' => $guardia->id,
                    'date' => Carbon::today($tz),
                ],
                [
                    'saved_by_user_id' => auth()->id(),
                    'saved_at' => Carbon::now($tz),
                ]
            );
        });

        return redirect()->back()->with('success', 'Asistencia guardada y registros históricos actualizados.');
    }
}
