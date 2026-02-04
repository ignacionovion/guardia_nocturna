<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Guardia;
use App\Models\GuardiaCalendarDay;
use App\Models\Firefighter;
use App\Models\User;
use App\Models\Shift;
use App\Models\ShiftUser;
use App\Models\StaffEvent;
use App\Models\GuardiaAttendanceRecord;
use App\Models\FirefighterUserLegacyMap;
use App\Models\FirefighterReplacement;
use App\Services\ReplacementService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class AdminController extends Controller
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

        $query = Guardia::with(['users', 'firefighters' => function ($q) {
            $q->orderBy('admission_date', 'asc');
        }]);

        // Si es cuenta de Guardia, filtrar solo su propia guardia
        if ($user->role === 'guardia') {
            $query->where('id', $user->guardia_id);
        }

        $guardias = $query->get();

        $activeGuardia = $this->resolveActiveGuardia($now);

        $activeReplacements = FirefighterReplacement::with(['originalFirefighter', 'replacementFirefighter'])
            ->where('status', 'active')
            ->get();

        $replacementByOriginal = $activeReplacements->keyBy('original_firefighter_id');
        $replacementByReplacement = $activeReplacements->keyBy('replacement_firefighter_id');

        $volunteers = Firefighter::query()
            ->when($user->role === 'guardia', function ($q) use ($user) {
                $q->where('guardia_id', $user->guardia_id);
            })
            ->orderBy('name')
            ->orderBy('last_name_paternal')
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

        $query = Guardia::with(['firefighters' => function ($q) {
            $q->orderBy('admission_date', 'asc');
        }]);

        if ($user->role === 'guardia') {
            $query->where('id', $user->guardia_id);
        }

        $guardias = $query->get();

        $volunteers = Firefighter::query()
            ->orderBy('name')
            ->orderBy('last_name_paternal')
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

        $validated = $request->validate([
            'guardia_id' => 'required|exists:guardias,id',
            'firefighter_id' => 'required|exists:firefighters,id',
        ]);

        // Si es cuenta de guardia, asegurar que solo asigna a SU guardia
        if ($user->role === 'guardia') {
            $userGuardiaId = $this->resolveGuardiaIdForGuardiaUser($user);
            if (!$userGuardiaId || (int) $validated['guardia_id'] !== (int) $userGuardiaId) {
                abort(403, 'No puedes asignar personal a otra guardia.');
            }
        }

        $firefighter = Firefighter::findOrFail($validated['firefighter_id']);
        $firefighter->update([
            'guardia_id' => $validated['guardia_id'],
            'attendance_status' => 'constituye',
            'is_titular' => true,
            'is_exchange' => false,
            'is_penalty' => false,
        ]);

        return redirect()->back()->with('success', 'Voluntario asignado correctamente a la guardia.');
    }

    public function unassignBombero(Request $request)
    {
        $user = auth()->user();

        if (!in_array($user->role, ['super_admin', 'capitania', 'guardia'], true)) {
            abort(403, 'No autorizado.');
        }

        $validated = $request->validate([
            'guardia_id' => 'required|exists:guardias,id',
            'firefighter_id' => 'required|exists:firefighters,id',
        ]);

        if ($user->role === 'guardia') {
            $userGuardiaId = $this->resolveGuardiaIdForGuardiaUser($user);
            if (!$userGuardiaId || (int) $validated['guardia_id'] !== (int) $userGuardiaId) {
                abort(403, 'No puedes quitar personal de otra guardia.');
            }
        }

        $firefighter = Firefighter::findOrFail($validated['firefighter_id']);

        if ((int) $firefighter->guardia_id !== (int) $validated['guardia_id']) {
            return redirect()->back()->withErrors(['msg' => 'El voluntario no pertenece a esa guardia.']);
        }

        $firefighter->update([
            'guardia_id' => null,
            'attendance_status' => 'constituye',
            'is_shift_leader' => false,
            'is_exchange' => false,
            'is_penalty' => false,
        ]);

        return redirect()->back()->with('success', 'Voluntario quitado de la guardia.');
    }

    public function toggleTitular($id)
    {
        $user = auth()->user();
        if (!in_array($user->role, ['super_admin', 'capitania', 'guardia'], true)) {
            abort(403, 'No autorizado.');
        }

        $firefighter = Firefighter::findOrFail($id);

        if ($user->role === 'guardia' && (int) $firefighter->guardia_id !== (int) $user->guardia_id) {
            abort(403, 'No autorizado.');
        }

        $firefighter->is_titular = !$firefighter->is_titular;
        $firefighter->save();

        $status = $firefighter->is_titular ? 'TITULAR' : 'TRANSITORIO';
        return redirect()->back()->with('success', "Estado de titularidad actualizado: {$firefighter->name} ahora es {$status}.");
    }

    public function assignReplacement(Request $request)
    {
        $user = auth()->user();

        if (!in_array($user->role, ['super_admin', 'capitania', 'guardia'], true)) {
            abort(403, 'No autorizado.');
        }

        $validated = $request->validate([
            'guardia_id' => 'required|exists:guardias,id',
            'original_firefighter_id' => 'required|exists:firefighters,id',
            'replacement_firefighter_id' => 'required|exists:firefighters,id',
        ]);

        // Verificar permisos de guardia
        if ($user->role === 'guardia') {
            $userGuardiaId = $this->resolveGuardiaIdForGuardiaUser($user);
            if (!$userGuardiaId || (int) $validated['guardia_id'] !== (int) $userGuardiaId) {
                abort(403, 'No puedes gestionar otra guardia.');
            }
        }

        $guardia = Guardia::findOrFail($validated['guardia_id']);
        $original = Firefighter::findOrFail($validated['original_firefighter_id']);
        $replacement = Firefighter::findOrFail($validated['replacement_firefighter_id']);

        if ((int) $replacement->id === (int) $original->id) {
            return back()->withErrors(['msg' => 'No puedes seleccionar al mismo voluntario como reemplazante.']);
        }

        if ((int) $original->guardia_id !== (int) $guardia->id) {
            return back()->withErrors(['msg' => 'El bombero a reemplazar no pertenece a esta guardia.']);
        }

        $hasActive = FirefighterReplacement::where('original_firefighter_id', $original->id)
            ->where('status', 'active')
            ->exists();
        if ($hasActive) {
            return back()->withErrors(['msg' => 'Este voluntario ya se encuentra reemplazado actualmente.']);
        }

        $endsAt = ReplacementService::calculateReplacementUntil(Carbon::now());

        $shift = Shift::where('status', 'active')->latest()->first();

        DB::transaction(function () use ($guardia, $original, $replacement, $endsAt, $shift) {
            $replacementPreviousGuardiaId = $replacement->guardia_id;
            FirefighterReplacement::create([
                'guardia_id' => $guardia->id,
                'original_firefighter_id' => $original->id,
                'replacement_firefighter_id' => $replacement->id,
                'starts_at' => Carbon::now(),
                'ends_at' => $endsAt,
                'status' => 'active',
                'notes' => json_encode([
                    'replacement_previous_guardia_id' => $replacementPreviousGuardiaId,
                ]),
            ]);

            $replacement->update([
                'guardia_id' => $guardia->id,
                'attendance_status' => 'constituye',
                'is_titular' => false,
                'is_shift_leader' => false,
                'is_exchange' => false,
                'is_penalty' => false,
            ]);

            $original->update([
                'attendance_status' => 'ausente',
                'is_shift_leader' => false,
                'is_exchange' => false,
                'is_penalty' => false,
            ]);

            if ($shift) {
                $replacementUserId = FirefighterUserLegacyMap::where('firefighter_id', $replacement->id)->value('user_id');
                $originalUserId = FirefighterUserLegacyMap::where('firefighter_id', $original->id)->value('user_id');

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

        return redirect()->back()->with('success', "Reemplazo asignado: {$replacement->name} reemplaza a {$original->name}.");
    }

    public function undoReplacement(Request $request, FirefighterReplacement $replacement)
    {
        $user = auth()->user();

        if (!in_array($user->role, ['super_admin', 'capitania', 'guardia'], true)) {
            abort(403, 'No autorizado.');
        }

        $replacement->loadMissing(['originalFirefighter', 'replacementFirefighter']);

        if ($replacement->status !== 'active') {
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
                'status' => 'closed',
                'ends_at' => Carbon::now(),
            ]);

            // Volver el original a constituye (visible nuevamente en la guardia)
            if ((int) $original->guardia_id === (int) $guardia->id) {
                $original->update([
                    'attendance_status' => 'constituye',
                    'is_shift_leader' => false,
                    'is_exchange' => false,
                    'is_penalty' => false,
                ]);
            }

            // El reemplazante vuelve a su guardia original.
            // Preferimos el valor persistido en notes (más confiable), y como fallback usamos el user legacy.
            $originalReplacerGuardiaId = null;
            if ($replacement->notes) {
                $decodedNotes = json_decode((string) $replacement->notes, true);
                if (is_array($decodedNotes) && array_key_exists('replacement_previous_guardia_id', $decodedNotes)) {
                    $originalReplacerGuardiaId = $decodedNotes['replacement_previous_guardia_id'];
                }
            }

            if ($originalReplacerGuardiaId === null) {
                $originalReplacerGuardiaId = FirefighterUserLegacyMap::query()
                    ->join('users', 'users.id', '=', 'firefighter_user_legacy_maps.user_id')
                    ->where('firefighter_user_legacy_maps.firefighter_id', $replacer->id)
                    ->value('users.guardia_id');
            }

            $replacerUserId = FirefighterUserLegacyMap::where('firefighter_id', $replacer->id)->value('user_id');

            $replacer->update([
                'guardia_id' => $originalReplacerGuardiaId,
                'attendance_status' => 'constituye',
                'is_titular' => false,
                'is_shift_leader' => false,
                'is_exchange' => false,
                'is_penalty' => false,
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
                $replacementUserId = FirefighterUserLegacyMap::where('firefighter_id', $replacer->id)->value('user_id');

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

        $activeReplacements = FirefighterReplacement::with(['originalFirefighter', 'replacementFirefighter'])
            ->where('guardia_id', $guardia->id)
            ->where('status', 'active')
            ->get();

        DB::transaction(function () use ($guardia, $activeReplacements, $shift) {
            foreach ($activeReplacements as $rep) {
                $rep->update([
                    'status' => 'closed',
                    'ends_at' => Carbon::now(),
                ]);

                $original = $rep->originalFirefighter;
                $replacer = $rep->replacementFirefighter;

                if ($original && (int) $original->guardia_id === (int) $guardia->id) {
                    $original->update([
                        'attendance_status' => 'constituye',
                        'is_shift_leader' => false,
                        'is_exchange' => false,
                        'is_penalty' => false,
                    ]);
                }

                if ($replacer) {
                    $prevGuardiaId = null;
                    if ($rep->notes) {
                        $decodedNotes = json_decode((string) $rep->notes, true);
                        if (is_array($decodedNotes) && array_key_exists('replacement_previous_guardia_id', $decodedNotes)) {
                            $prevGuardiaId = $decodedNotes['replacement_previous_guardia_id'];
                        }
                    }

                    // Si no hay info de origen, lo sacamos de la guardia actual
                    if ((int) $replacer->guardia_id === (int) $guardia->id) {
                        $replacer->update([
                            'guardia_id' => $prevGuardiaId,
                            'attendance_status' => 'constituye',
                            'is_titular' => false,
                            'is_shift_leader' => false,
                            'is_exchange' => false,
                            'is_penalty' => false,
                        ]);
                    }

                    $replacerUserId = FirefighterUserLegacyMap::where('firefighter_id', $replacer->id)->value('user_id');
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

    public function storeBombero(Request $request)
    {
        $user = auth()->user();

        if (!in_array($user->role, ['super_admin', 'capitania', 'guardia'], true)) {
            abort(403, 'No autorizado.');
        }

        $validated = $request->validate([
            'guardia_id' => 'required|exists:guardias,id',
            'name' => 'required|string|max:255',
            'last_name_paternal' => 'nullable|string|max:255',
            'age' => 'required|integer|min:18',
            'years_of_service' => 'required|integer|min:0',
            'is_driver' => 'nullable|boolean',
        ]);

        // Si es cuenta de guardia, asegurar que crea en SU guardia
        if ($user->role === 'guardia' && $validated['guardia_id'] != $user->guardia_id) {
            abort(403, 'No puedes agregar personal a otra guardia.');
        }

        User::create([
            'name' => $validated['name'],
            'last_name_paternal' => $validated['last_name_paternal'] ?? null,
            'email' => 'no-email-' . uniqid() . '@system.local',
            'password' => Hash::make(Str::random(12)),
            'role' => 'bombero',
            'age' => $validated['age'],
            'years_of_service' => $validated['years_of_service'],
            'guardia_id' => $validated['guardia_id'],
            'is_driver' => $request->has('is_driver'),
            'is_titular' => true, // Nuevo ingreso directo es Titular
            'attendance_status' => 'constituye',
            'job_replacement_id' => null,
            'is_shift_leader' => false,
            'is_exchange' => false,
            'is_penalty' => false,
        ]);

        return redirect()->route($user->role === 'guardia' ? 'admin.dotaciones' : 'admin.guardias')->with('success', 'Bombero agregado correctamente a la guardia.');
    }

    public function editBombero($id)
    {
        $user = auth()->user();
        if (!in_array($user->role, ['super_admin', 'capitania', 'guardia'], true)) {
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
        if (!in_array($user->role, ['super_admin', 'capitania', 'guardia'], true)) {
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
            'age' => 'required|integer|min:18',
            'years_of_service' => 'required|integer|min:0',
            'is_driver' => 'nullable|boolean',
        ]);

        // Validación extra de guardia_id para rol guardia
        if ($user->role === 'guardia' && $validated['guardia_id'] != $user->guardia_id) {
            abort(403, 'No puedes mover personal a otra guardia.');
        }

        $data = $validated;
        $data['is_driver'] = $request->has('is_driver');
        $data['role'] = 'bombero';

        $bombero->update($data);

        return redirect()->route($user->role === 'guardia' ? 'admin.dotaciones' : 'admin.guardias')->with('success', 'Bombero actualizado correctamente.');
    }

    public function destroyBombero($id)
    {
        $user = auth()->user();
        if (!in_array($user->role, ['super_admin', 'capitania', 'guardia'], true)) {
            abort(403, 'No autorizado.');
        }

        $bombero = User::findOrFail($id);

        // Si es guardia, verificar pertenencia
        if ($user->role === 'guardia' && $bombero->guardia_id != $user->guardia_id) {
            abort(403, 'No puedes eliminar personal de otra guardia.');
        }

        $bombero->delete();

        return redirect()->route($user->role === 'guardia' ? 'admin.dotaciones' : 'admin.guardias')->with('success', 'Bombero eliminado correctamente.');
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
        $usersCount = $guardia->firefighters()->count();

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
        $transitorios = Firefighter::where('guardia_id', $guardia->id)
            ->where('is_titular', false)
            ->get();

        foreach ($transitorios as $user) {
            $user->update([
                'guardia_id' => null,
                'attendance_status' => 'constituye',
                'is_shift_leader' => false,
                'is_exchange' => false,
                'is_penalty' => false,
            ]);
        }

        // 2. Titulares (Dotación permanente)
        // Se quedan, pero se limpia su estado del turno
        $titulares = Firefighter::where('guardia_id', $guardia->id)
            ->where('is_titular', true)
            ->get();

        foreach ($titulares as $user) {
            $user->update([
                'attendance_status' => 'constituye', // Vuelven a estado base
                'is_shift_leader' => false,
                'is_exchange' => false,
                'is_penalty' => false,
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

        $transitorios = Firefighter::where('guardia_id', $guardia->id)
            ->where('is_titular', false)
            ->where('updated_at', '<', $cutoff)
            ->get();

        foreach ($transitorios as $user) {
            $user->update([
                'guardia_id' => null,
                'attendance_status' => 'constituye',
                'is_shift_leader' => false,
                'is_exchange' => false,
                'is_penalty' => false,
            ]);
        }
    }

    public function bulkUpdateGuardia(Request $request, $id)
    {
        if (!in_array(auth()->user()->role, ['super_admin', 'capitania', 'guardia'], true)) {
            abort(403, 'No autorizado.');
        }

        $guardia = Guardia::findOrFail($id);

        // Al momento de constituir turno, limpiar transitorios/reemplazos del turno anterior
        $this->cleanupTransitoriosOnConstitution($guardia);
        
        // Si es cuenta de guardia, verificar propiedad
        if (auth()->user()->role === 'guardia') {
            $userGuardiaId = $this->resolveGuardiaIdForGuardiaUser(auth()->user());
            if (!$userGuardiaId || (int) $userGuardiaId !== (int) $guardia->id) {
                abort(403, 'No autorizado.');
            }
        }

        $data = $request->validate([
            'users' => 'required|array',
            'users.*.attendance_status' => 'required|string',
        ]);

        $shift = Shift::with('users')
            ->where('status', 'active')
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
            foreach ($data['users'] as $firefighterId => $attributes) {
                $firefighter = Firefighter::find($firefighterId);
                if (!$firefighter || (int) $firefighter->guardia_id !== (int) $guardia->id) {
                    continue;
                }

                $attendanceStatus = $attributes['attendance_status'] ?? 'constituye';

                $firefighter->update([
                    'attendance_status' => $attendanceStatus,
                ]);

                $userId = FirefighterUserLegacyMap::where('firefighter_id', $firefighter->id)->value('user_id');

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
                    'date' => Carbon::today(),
                ],
                [
                    'saved_by_user_id' => auth()->id(),
                    'saved_at' => Carbon::now(),
                ]
            );
        });

        return redirect()->back()->with('success', 'Asistencia guardada y registros históricos actualizados.');
    }
}
