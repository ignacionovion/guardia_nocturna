<?php

namespace App\Http\Controllers;

use App\Models\PreventiveEvent;
use App\Models\PreventiveShift;
use App\Models\PreventiveShiftAssignment;
use App\Models\PreventiveShiftAttendance;
use App\Models\Bombero;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PreventivePublicController extends Controller
{
    /**
     * Procesa el RUT ingresado directamente desde la página pública
     */
    public function processRut(Request $request, string $token)
    {
        $event = PreventiveEvent::query()->where('public_token', $token)->firstOrFail();

        $validated = $request->validate([
            'rut' => ['required', 'string', 'max:20', 'regex:/^\d{7,8}-[0-9kK]$/'],
        ], [
            'rut.regex' => 'Formato inválido. Debe ser como 12345678-5.',
        ]);

        $rut = mb_strtolower(trim((string) $validated['rut']));

        // Buscar bombero por RUT
        $bombero = Bombero::query()
            ->whereRaw('lower(rut) = ?', [$rut])
            ->first();

        if (!$bombero) {
            return back()->withInput()->withErrors([
                'rut' => 'Bombero no existe en nuestra base de datos.',
            ]);
        }

        // Verificar si hay un turno activo
        $now = Carbon::now($event->timezone);
        $shift = $this->resolveCurrentShift($event, $now);

        if (!$shift) {
            return back()->with('warning', 'No hay un turno activo en este momento.');
        }

        // Verificar si el bombero está asignado al turno actual
        $assignment = PreventiveShiftAssignment::query()
            ->where('preventive_shift_id', $shift->id)
            ->where('bombero_id', $bombero->id)
            ->first();

        if ($assignment) {
            // Verificar si ya tiene asistencia registrada
            $attendance = PreventiveShiftAttendance::query()
                ->where('preventive_shift_assignment_id', $assignment->id)
                ->first();

            if ($attendance) {
                return back()->with('warning', 'Ya registraste asistencia para este turno.');
            }

            // El bombero está asignado - registrar asistencia automáticamente
            $this->registerAttendance($assignment, $request);
            
            return back()->with('success', '¡Asistencia registrada correctamente!');
        }

        // El bombero NO está asignado - guardar en sesión y redirigir a selección de tipo de ingreso
        $request->session()->put('preventiva_bombero_id', (int) $bombero->id);
        $request->session()->put('preventiva_event_token', $token);

        return redirect()->route('preventivas.public.tipo_ingreso.form', ['token' => $token]);
    }

    /**
     * Muestra el formulario de identificación con RUT
     */
    public function identificarForm(Request $request, string $token)
    {
        $event = PreventiveEvent::query()->where('public_token', $token)->firstOrFail();

        // Si ya hay un bombero identificado en sesión, redirigir a la vista principal
        if ($request->session()->has('preventiva_bombero_id')) {
            return redirect()->route('preventivas.public.show', ['token' => $token]);
        }

        return view('preventivas.identificar', [
            'event' => $event,
            'token' => $token,
        ]);
    }

    /**
     * Procesa el RUT ingresado
     */
    public function identificarStore(Request $request, string $token)
    {
        $event = PreventiveEvent::query()->where('public_token', $token)->firstOrFail();

        $validated = $request->validate([
            'rut' => ['required', 'string', 'max:20', 'regex:/^\d{7,8}-[0-9kK]$/'],
        ], [
            'rut.regex' => 'Formato inválido. Debe ser como 18485962-9.',
        ]);

        $rut = mb_strtolower(trim((string) $validated['rut']));

        $bombero = Bombero::query()
            ->whereRaw('lower(rut) = ?', [$rut])
            ->first();

        if (!$bombero) {
            return back()->withInput()->withErrors([
                'rut' => 'Bombero no existe en nuestra base de datos.',
            ]);
        }

        // Guardar bombero en sesión
        $request->session()->put('preventiva_bombero_id', (int) $bombero->id);
        $request->session()->put('preventiva_event_token', $token);

        // Verificar si hay un turno activo
        $now = Carbon::now($event->timezone);
        $shift = $this->resolveCurrentShift($event, $now);

        if (!$shift) {
            return redirect()->route('preventivas.public.show', ['token' => $token])
                ->with('warning', 'No hay un turno activo en este momento.');
        }

        // Verificar si el bombero está asignado al turno actual
        $assignment = PreventiveShiftAssignment::query()
            ->where('preventive_shift_id', $shift->id)
            ->where('bombero_id', $bombero->id)
            ->first();

        if ($assignment) {
            // El bombero está asignado - registrar asistencia automáticamente
            $this->registerAttendance($assignment, $request);
            
            return redirect()->route('preventivas.public.show', ['token' => $token])
                ->with('success', '¡Asistencia registrada correctamente!');
        }

        // El bombero NO está asignado - redirigir a selección de tipo de ingreso
        return redirect()->route('preventivas.public.tipo_ingreso.form', ['token' => $token]);
    }

    /**
     * Muestra el formulario para seleccionar tipo de ingreso (reemplazo o refuerzo)
     */
    public function tipoIngresoForm(Request $request, string $token)
    {
        $event = PreventiveEvent::query()->where('public_token', $token)->firstOrFail();

        // Verificar que hay un bombero en sesión
        if (!$request->session()->has('preventiva_bombero_id')) {
            return redirect()->route('preventivas.public.identificar.form', ['token' => $token]);
        }

        $bomberoId = $request->session()->get('preventiva_bombero_id');
        $bombero = Bombero::find($bomberoId);

        if (!$bombero) {
            $request->session()->forget('preventiva_bombero_id');
            return redirect()->route('preventivas.public.identificar.form', ['token' => $token])
                ->with('error', 'Sesión inválida. Por favor, identifícate nuevamente.');
        }

        // Obtener turno actual
        $now = Carbon::now($event->timezone);
        $shift = $this->resolveCurrentShift($event, $now);

        if (!$shift) {
            return redirect()->route('preventivas.public.show', ['token' => $token])
                ->with('warning', 'No hay un turno activo en este momento.');
        }

        // Obtener bomberos disponibles para reemplazo (los que están asignados al turno actual)
        $assignedBomberoIds = PreventiveShiftAssignment::query()
            ->where('preventive_shift_id', $shift->id)
            ->pluck('bombero_id')
            ->toArray();

        $availableForReplacement = Bombero::query()
            ->whereIn('id', $assignedBomberoIds)
            ->where(function ($q) {
                $q->whereNull('fuera_de_servicio')->orWhere('fuera_de_servicio', false);
            })
            ->orderBy('apellido_paterno')
            ->orderBy('nombres')
            ->get();

        return view('preventivas.tipo_ingreso', [
            'event' => $event,
            'token' => $token,
            'bombero' => $bombero,
            'shift' => $shift,
            'availableForReplacement' => $availableForReplacement,
        ]);
    }

    /**
     * Procesa la selección de tipo de ingreso
     */
    public function tipoIngresoStore(Request $request, string $token)
    {
        $event = PreventiveEvent::query()->where('public_token', $token)->firstOrFail();

        // Verificar que hay un bombero en sesión
        if (!$request->session()->has('preventiva_bombero_id')) {
            return redirect()->route('preventivas.public.identificar.form', ['token' => $token]);
        }

        $validated = $request->validate([
            'tipo' => ['required', 'in:refuerzo,reemplazo'],
            'bombero_reemplazo_id' => ['required_if:tipo,reemplazo', 'exists:bomberos,id'],
        ], [
            'tipo.required' => 'Debes seleccionar un tipo de ingreso.',
            'tipo.in' => 'Tipo de ingreso inválido.',
            'bombero_reemplazo_id.required_if' => 'Debes seleccionar al bombero que reemplazas.',
        ]);

        $bomberoId = $request->session()->get('preventiva_bombero_id');
        $tipo = $validated['tipo'];

        // Obtener turno actual
        $now = Carbon::now($event->timezone);
        $shift = $this->resolveCurrentShift($event, $now);

        if (!$shift) {
            return redirect()->route('preventivas.public.show', ['token' => $token])
                ->with('warning', 'No hay un turno activo en este momento.');
        }

        // Crear o actualizar asignación
        $assignment = PreventiveShiftAssignment::query()
            ->where('preventive_shift_id', $shift->id)
            ->where('bombero_id', $bomberoId)
            ->first();

        if (!$assignment) {
            $assignment = PreventiveShiftAssignment::create([
                'preventive_shift_id' => $shift->id,
                'bombero_id' => $bomberoId,
                'es_refuerzo' => $tipo === 'refuerzo',
                'entrada_hora' => now(),
            ]);
        } else {
            $assignment->update([
                'es_refuerzo' => $tipo === 'refuerzo',
                'entrada_hora' => now(),
            ]);
        }

        // Registrar asistencia
        $this->registerAttendance($assignment, $request);

        // Limpiar sesión
        $request->session()->forget(['preventiva_bombero_id', 'preventiva_event_token']);

        $message = $tipo === 'refuerzo' 
            ? '¡Registrado como REFUERZO correctamente!' 
            : '¡Registrado como REEMPLAZO correctamente!';

        return redirect()->route('preventivas.public.show', ['token' => $token])
            ->with('success', $message);
    }

    /**
     * Registra la asistencia de un bombero
     */
    private function registerAttendance(PreventiveShiftAssignment $assignment, Request $request): void
    {
        $attendance = PreventiveShiftAttendance::query()
            ->where('preventive_shift_assignment_id', $assignment->id)
            ->first();

        if (!$attendance) {
            PreventiveShiftAttendance::create([
                'preventive_shift_assignment_id' => $assignment->id,
                'status' => 'present',
                'confirmed_at' => now(),
                'confirm_ip' => $request->ip(),
                'confirm_user_agent' => substr((string) $request->userAgent(), 0, 1024),
            ]);
        }
    }

    public function show(Request $request, string $token)
    {
        $event = PreventiveEvent::query()->where('public_token', $token)->firstOrFail();

        $now = Carbon::now($event->timezone);
        $shift = $this->resolveCurrentShift($event, $now);

        if (!$shift) {
            return view('preventivas.public', [
                'event' => $event,
                'shift' => null,
                'assignments' => collect(),
                'now' => $now,
                'identifiedBombero' => null,
            ]);
        }

        $assignments = PreventiveShiftAssignment::query()
            ->where('preventive_shift_id', $shift->id)
            ->with(['firefighter', 'attendance'])
            ->get()
            ->sortBy(function ($a) {
                return (string) ($a->firefighter?->apellido_paterno ?? '');
            })
            ->values();

        // Verificar si hay un bombero identificado en sesión
        $identifiedBombero = null;
        if ($request->session()->has('preventiva_bombero_id')) {
            $identifiedBombero = Bombero::find($request->session()->get('preventiva_bombero_id'));
        }

        return view('preventivas.public', [
            'event' => $event,
            'shift' => $shift,
            'assignments' => $assignments,
            'now' => $now,
            'identifiedBombero' => $identifiedBombero,
        ]);
    }

    public function confirm(Request $request, string $token)
    {
        $event = PreventiveEvent::query()->where('public_token', $token)->firstOrFail();

        $validated = $request->validate([
            'assignment_id' => ['required', 'exists:preventive_shift_assignments,id'],
        ]);

        $assignment = PreventiveShiftAssignment::query()
            ->with(['shift'])
            ->findOrFail((int) $validated['assignment_id']);

        if (!$assignment->shift || (int) $assignment->shift->preventive_event_id !== (int) $event->id) {
            abort(404);
        }

        $attendance = PreventiveShiftAttendance::query()->where('preventive_shift_assignment_id', $assignment->id)->first();
        if ($attendance) {
            return back()->with('warning', 'Ya registraste asistencia para este turno.');
        }

        PreventiveShiftAttendance::create([
            'preventive_shift_assignment_id' => $assignment->id,
            'status' => 'present',
            'confirmed_at' => now(),
            'confirm_ip' => $request->ip(),
            'confirm_user_agent' => substr((string) $request->userAgent(), 0, 1024),
        ]);

        return back()
            ->withInput(['assignment_id' => (string) $assignment->id])
            ->with('success', 'Asistencia registrada.');
    }

    private function resolveCurrentShift(PreventiveEvent $event, Carbon $now): ?PreventiveShift
    {
        $today = $now->toDateString();

        $shifts = PreventiveShift::query()
            ->where('preventive_event_id', $event->id)
            ->whereDate('shift_date', $today)
            ->orderBy('sort_order')
            ->get();

        foreach ($shifts as $shift) {
            $start = Carbon::parse($shift->shift_date->toDateString() . ' ' . $shift->start_time, $event->timezone);
            $end = Carbon::parse($shift->shift_date->toDateString() . ' ' . $shift->end_time, $event->timezone);

            if ($end->lessThanOrEqualTo($start)) {
                $end->addDay();
            }

            if ($now->greaterThanOrEqualTo($start) && $now->lessThan($end)) {
                return $shift;
            }
        }

        return null;
    }

}
