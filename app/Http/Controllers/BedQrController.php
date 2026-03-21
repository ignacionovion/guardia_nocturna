<?php

namespace App\Http\Controllers;

use App\Models\Bed;
use App\Models\BedAssignment;
use App\Models\Bombero;
use App\Models\Guardia;
use App\Models\Shift;
use App\Models\ShiftUser;
use App\Models\SystemSetting;
use App\Models\TurnoSession;
use App\Models\TurnoSessionItem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BedQrController extends Controller
{
    private function scheduleTimezone(): string
    {
        return SystemSetting::getValue('guardia_schedule_tz', env('GUARDIA_SCHEDULE_TZ', config('app.timezone')));
    }

    private function isWithinGuardiaHours(?Carbon $now = null): bool
    {
        $tz = $this->scheduleTimezone();
        $now = ($now ?: Carbon::now($tz))->copy()->setTimezone($tz);

        // Ventana fija todos los días: 22:00 -> 07:00
        $startHour = 22;

        // Si son las 00:00-06:59, pertenecemos a la ventana que empezó el día anterior
        $startDay = $now->copy()->startOfDay();
        if ((int) $now->hour < 7) {
            $startDay->subDay();
        }

        $startAt = $startDay->copy()->setTime($startHour, 0, 0);
        $endAt = $startDay->copy()->addDay()->setTime(7, 0, 0);

        return $now->greaterThanOrEqualTo($startAt) && $now->lessThan($endAt);
    }

    private function isBomberoInActiveGuardiaShift(Bombero $bombero, ?Carbon $now = null): bool
    {
        $now = $now ?: now();

        $shift = Shift::query()
            ->with(['leader'])
            ->where('status', 'active')
            ->whereHas('leader', function ($q) use ($bombero) {
                $q->where('guardia_id', $bombero->guardia_id);
            })
            ->latest()
            ->first();

        if (!$shift) {
            return false;
        }

        // Check ShiftUser (traditional way - after "Guardar asistencia")
        $inShiftUser = ShiftUser::query()
            ->where('shift_id', $shift->id)
            ->where('firefighter_id', $bombero->id)
            ->whereNull('end_time')
            ->exists();

        if ($inShiftUser) {
            return true;
        }

        // Check turno_session_items (for confirmed but not yet saved)
        // Find the active draft session for this guardia
        $session = TurnoSession::query()
            ->where('guardia_id', $bombero->guardia_id)
            ->where('status', 'draft')
            ->whereDate('operational_date', '=', $now->toDateString())
            ->latest()
            ->first();

        if (!$session) {
            // Try without date constraint
            $session = TurnoSession::query()
                ->where('guardia_id', $bombero->guardia_id)
                ->where('status', 'draft')
                ->latest()
                ->first();
        }

        if ($session) {
            $confirmedInDraft = TurnoSessionItem::query()
                ->where('turno_session_id', $session->id)
                ->where('firefighter_id', $bombero->id)
                ->whereNotNull('confirmed_at')
                ->whereNotNull('confirm_token')
                ->exists();

            if ($confirmedInDraft) {
                return true;
            }
        }

        return false;
    }

    /**
     * Muestra el formulario para escanear QR de cama (pide RUT)
     */
    public function scanForm(Request $request, string $bedId)
    {
        $bedId = $request->route('bedId') ?? $bedId;

        DB::enableQueryLog();

        // DIAGNÓSTICO COMPLETO
        $paramInfo = [
            'raw_value' => $bedId,
            'php_type'  => gettype($bedId),
            'strlen'    => strlen((string) $bedId),
            'hex'       => bin2hex((string) $bedId),
            'int_cast'  => (int) $bedId,
        ];

        $dbDefault  = DB::connection()->getDatabaseName();
        $modelDb    = Bed::query()->getConnection()->getDatabaseName();
        $modelConn  = Bed::query()->getConnection()->getName();

        $step1Exists  = Bed::where('id', $bedId)->exists();
        $step2WKey    = Bed::whereKey($bedId)->exists();
        $step3RawStr  = DB::table('beds')->where('id', $bedId)->exists();
        $step4RawInt  = DB::table('beds')->where('id', (int) $bedId)->exists();
        $step5First   = Bed::query()->where('id', $bedId)->first();
        $step6Find    = Bed::find((int) $bedId);

        $queryLog = DB::getQueryLog();
        DB::disableQueryLog();

        \Log::info('BedQrController::scanForm DIAGNÓSTICO', [
            'param_info'            => $paramInfo,
            'tenant_initialized'    => tenancy()->initialized,
            'tenant_id'             => tenant('id'),
            'db_default_connection' => $dbDefault,
            'model_connection_name' => $modelConn,
            'model_connection_db'   => $modelDb,
            'step1_eloquent_exists' => $step1Exists,
            'step2_wherekey_exists' => $step2WKey,
            'step3_raw_str_exists'  => $step3RawStr,
            'step4_raw_int_exists'  => $step4RawInt,
            'step5_first'           => $step5First?->id,
            'step6_find'            => $step6Find?->id,
            'query_log'             => $queryLog,
        ]);

        if (! $step6Find && ! $step5First) {
            \Log::error('BedQrController::scanForm REGISTRO NO ENCONTRADO', [
                'bedId' => $bedId, 'db' => $modelDb, 'all_ids' => Bed::pluck('id')->toArray(),
            ]);
            abort(404, 'Bed ' . $bedId . ' not found in ' . $modelDb);
        }

        $bed = $step6Find ?? $step5First;

        // Si viene el parámetro reset, limpiar la sesión del bombero
        if ($request->has('reset')) {
            $request->session()->forget('bed_qr_bombero_id');
        }

        $withinHours = $this->isWithinGuardiaHours(Carbon::now($this->scheduleTimezone()));

        // Si ya hay un bombero identificado en sesión, mostrar info
        $bombero = null;
        $bomberoId = $request->session()->get('bed_qr_bombero_id');
        if ($bomberoId) {
            $bombero = Bombero::query()->where('id', (int) $bomberoId)->first();
            if (!$bombero) {
                $request->session()->forget('bed_qr_bombero_id');
            }
        }

        return view('camas.scan', [
            'bed' => $bed,
            'bombero' => $bombero,
            'withinGuardiaHours' => $withinHours,
        ]);
    }

    /**
     * Procesa el RUT ingresado
     */
    public function processRut(Request $request, string $bedId)
    {
        $bedId = $request->route('bedId') ?? $bedId;

        $bed = Bed::query()->findOrFail((int) $bedId);

        if (!$this->isWithinGuardiaHours(Carbon::now($this->scheduleTimezone()))) {
            return redirect()->route('camas.scan.form', ['bedId' => $bedId]);
        }

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
                'rut' => 'El RUT no es válido',
            ]);
        }

        if (!$this->isBomberoInActiveGuardiaShift($bombero)) {
            $request->session()->put('bed_qr_bombero_id', (int) $bombero->id);
            return redirect()->route('camas.scan.not_in_guardia', ['bedId' => $bedId]);
        }

        // Guardar bombero en sesión
        $request->session()->put('bed_qr_bombero_id', (int) $bombero->id);

        return redirect()->route('camas.scan.assign', ['bedId' => $bedId]);
    }

    /**
     * Muestra página cuando el bombero NO está en la guardia activa
     */
    public function notInGuardia(Request $request, string $bedId)
    {
        $bedId = $request->route('bedId') ?? $bedId;

        $bed = Bed::query()->findOrFail((int) $bedId);

        $bombero = null;
        $bomberoId = $request->session()->get('bed_qr_bombero_id');
        if ($bomberoId) {
            $bombero = Bombero::query()->where('id', (int) $bomberoId)->first();
        }

        return view('camas.not_in_guardia', [
            'bed' => $bed,
            'bombero' => $bombero,
        ]);
    }

    /**
     * Muestra confirmación para asignar cama
     */
    public function assignForm(Request $request, string $bedId)
    {
        $bedId = $request->route('bedId') ?? $bedId;

        $bed = Bed::query()->findOrFail((int) $bedId);

        if (!$this->isWithinGuardiaHours(Carbon::now($this->scheduleTimezone()))) {
            return redirect()->route('camas.scan.form', ['bedId' => $bedId]);
        }

        $bomberoId = $request->session()->get('bed_qr_bombero_id');
        if (!$bomberoId) {
            return redirect()->route('camas.scan.form', ['bedId' => $bedId]);
        }

        $bombero = Bombero::query()->where('id', (int) $bomberoId)->first();
        if (!$bombero) {
            $request->session()->forget('bed_qr_bombero_id');
            return redirect()->route('camas.scan.form', ['bedId' => $bedId]);
        }

        if (!$this->isBomberoInActiveGuardiaShift($bombero)) {
            return redirect()->route('camas.scan.not_in_guardia', ['bedId' => $bedId]);
        }

        // Verificar si la cama ya está ocupada
        $currentAssignment = BedAssignment::query()
            ->where('bed_id', $bed->id)
            ->whereNull('ended_at')
            ->first();

        return view('camas.assign', [
            'bed' => $bed,
            'bombero' => $bombero,
            'currentAssignment' => $currentAssignment,
        ]);
    }

    /**
     * Asigna la cama al bombero
     */
    public function assignStore(Request $request, string $bedId)
    {
        $bedId = $request->route('bedId') ?? $bedId;

        $bed = Bed::query()->findOrFail((int) $bedId);

        if (!$this->isWithinGuardiaHours(Carbon::now($this->scheduleTimezone()))) {
            return redirect()->route('camas.scan.form', ['bedId' => $bedId]);
        }

        $bomberoId = $request->session()->get('bed_qr_bombero_id');
        if (!$bomberoId) {
            return redirect()->route('camas.scan.form', ['bedId' => $bedId]);
        }

        $bombero = Bombero::query()->where('id', (int) $bomberoId)->first();
        if (!$bombero) {
            $request->session()->forget('bed_qr_bombero_id');
            return redirect()->route('camas.scan.form', ['bedId' => $bedId]);
        }

        // Liberar cama si está ocupada
        $currentAssignment = BedAssignment::query()
            ->where('bed_id', $bed->id)
            ->whereNull('ended_at')
            ->first();

        if ($currentAssignment) {
            $currentAssignment->update([
                'ended_at' => now(),
                'released_at' => now(),
            ]);
            $bed->update(['status' => 'available']);
        }

        // Verificar si el bombero ya tiene otra cama asignada
        $existingAssignment = BedAssignment::query()
            ->where('firefighter_id', $bombero->id)
            ->whereNull('ended_at')
            ->with('bed')
            ->first();

        if ($existingAssignment) {
            // Liberar la cama anterior
            $existingAssignment->update([
                'ended_at' => now(),
                'released_at' => now(),
            ]);
            if ($existingAssignment->bed) {
                $existingAssignment->bed->update(['status' => 'available']);
            }
        }

        // Crear nueva asignación
        BedAssignment::create([
            'bed_id' => $bed->id,
            'firefighter_id' => $bombero->id,
            'started_at' => now(),
            'assigned_at' => now(),
            'notes' => 'Asignado vía QR escaneado',
            'assigned_source' => 'qr',
            'assigned_ip' => (string) ($request->ip() ?? ''),
            'assigned_user_agent' => (string) $request->userAgent(),
        ]);

        // Marcar la cama como ocupada
        $bed->update(['status' => 'occupied']);

        // Enviar notificación de cama asignada vía QR
        \App\Services\NotificationService::bedAssigned(
            auth()->user(), 
            $bombero, 
            $bed->name ?? $bed->number ?? $bed->id, 
            $bombero->guardia_id ? \App\Models\Guardia::find($bombero->guardia_id) : null, 
            'QR'
        );

        // Limpiar sesión del bombero después de asignar
        $request->session()->forget('bed_qr_bombero_id');

        return redirect()->route('camas.scan.success', ['bedId' => $bedId])
            ->with('success', '¡Cama asignada correctamente!');
    }

    /**
     * Página de éxito
     */
    public function success(Request $request, string $bedId)
    {
        $bedId = $request->route('bedId') ?? $bedId;

        $bed = Bed::query()->findOrFail((int) $bedId);

        $bombero = null;
        $bomberoId = $request->session()->get('bed_qr_bombero_id');
        if ($bomberoId) {
            $bombero = Bombero::query()->where('id', (int) $bomberoId)->first();
        }

        return view('camas.success', [
            'bed' => $bed,
            'bombero' => $bombero,
        ]);
    }

    /**
     * Muestra la página de impresión del QR de una cama
     */
    public function printQr(Request $request, string $bedId)
    {
        $bedId = $request->route('bedId') ?? $bedId;

        $bed = Bed::query()->findOrFail((int) $bedId);
        
        return view('camas.qr-print', compact('bed'));
    }

    /**
     * Obtiene la guardia activa buscando shift_users sin end_time (activos)
     */
    private function getActiveGuardiaForToday(): ?Guardia
    {
        $scheduleTz = SystemSetting::getValue('guardia_schedule_tz', env('GUARDIA_SCHEDULE_TZ', 'America/Santiago'));
        $now = Carbon::now($scheduleTz);
        
        // Buscar cualquier shift_user activo (sin end_time) en últimas 48 horas
        $twoDaysAgo = $now->copy()->subDays(2)->startOfDay();
        
        $shiftUser = ShiftUser::query()
            ->where('start_time', '>=', $twoDaysAgo)
            ->whereNotNull('guardia_id')
            ->whereNull('end_time')  // Solo activos
            ->orderBy('start_time', 'desc')
            ->first();

        if ($shiftUser && $shiftUser->guardia) {
            return $shiftUser->guardia;
        }

        return null;
    }

    /**
     * Verifica si un bombero está en una guardia específica
     */
    private function isBomberoInGuardia(int $bomberoId, int $guardiaId): bool
    {
        return ShiftUser::query()
            ->where('guardia_id', $guardiaId)
            ->where(function ($query) use ($bomberoId) {
                $query->where('firefighter_id', $bomberoId)
                    ->orWhereHas('firefighter', function ($q) use ($bomberoId) {
                        $q->where('id', $bomberoId);
                    });
            })
            ->exists();
    }
}
