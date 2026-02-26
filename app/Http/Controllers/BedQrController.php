<?php

namespace App\Http\Controllers;

use App\Models\Bed;
use App\Models\BedAssignment;
use App\Models\Bombero;
use App\Models\Guardia;
use App\Models\ShiftUser;
use App\Models\SystemSetting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BedQrController extends Controller
{
    /**
     * Muestra el formulario para escanear QR de cama (pide RUT)
     */
    public function scanForm(Request $request, int $bedId)
    {
        $bed = Bed::query()->findOrFail($bedId);

        // Si viene el parámetro reset, limpiar la sesión del bombero
        if ($request->has('reset')) {
            $request->session()->forget('bed_qr_bombero_id');
        }

        // Verificar si estamos dentro del horario de guardia
        $withinHours = $this->isWithinGuardiaHours();

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
    public function processRut(Request $request, int $bedId)
    {
        $bed = Bed::query()->findOrFail($bedId);

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

        // Guardar bombero en sesión
        $request->session()->put('bed_qr_bombero_id', (int) $bombero->id);

        // Verificar si está en guardia activa hoy
        $today = Carbon::now('America/Santiago')->toDateString();
        $activeGuardia = $this->getActiveGuardiaForToday($today);

        if (!$activeGuardia) {
            $request->session()->forget('bed_qr_bombero_id');
            return back()->with('warning', 'No hay guardia activa en este momento.');
        }

        // Verificar si el bombero está en la guardia activa
        $isInActiveGuardia = $this->isBomberoInGuardia($bombero->id, $activeGuardia->id, $today);

        // Si no está en la guardia activa, limpiar sesión y redirigir
        if (!$isInActiveGuardia) {
            $request->session()->forget('bed_qr_bombero_id');
            return redirect()->route('camas.scan.not_in_guardia', ['bedId' => $bedId])
                ->with('info', 'No estás registrado en la guardia de hoy.');
        }

        // El bombero está en guardia - proceder a asignar cama
        return redirect()->route('camas.scan.assign', ['bedId' => $bedId]);
    }

    /**
     * Muestra página cuando el bombero NO está en la guardia activa
     */
    public function notInGuardia(Request $request, int $bedId)
    {
        $bed = Bed::query()->findOrFail($bedId);

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
    public function assignForm(Request $request, int $bedId)
    {
        $bed = Bed::query()->findOrFail($bedId);

        $bomberoId = $request->session()->get('bed_qr_bombero_id');
        if (!$bomberoId) {
            return redirect()->route('camas.scan.form', ['bedId' => $bedId]);
        }

        $bombero = Bombero::query()->where('id', (int) $bomberoId)->first();
        if (!$bombero) {
            $request->session()->forget('bed_qr_bombero_id');
            return redirect()->route('camas.scan.form', ['bedId' => $bedId]);
        }

        // Verificar si la cama ya está ocupada
        $currentAssignment = BedAssignment::query()
            ->where('bed_id', $bed->id)
            ->whereNull('released_at')
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
    public function assignStore(Request $request, int $bedId)
    {
        $bed = Bed::query()->findOrFail($bedId);

        $bomberoId = $request->session()->get('bed_qr_bombero_id');
        if (!$bomberoId) {
            return redirect()->route('camas.scan.form', ['bedId' => $bedId]);
        }

        $bombero = Bombero::query()->where('id', (int) $bomberoId)->first();
        if (!$bombero) {
            $request->session()->forget('bed_qr_bombero_id');
            return redirect()->route('camas.scan.form', ['bedId' => $bedId]);
        }

        // Verificar nuevamente si está en guardia activa
        $today = Carbon::now('America/Santiago')->toDateString();
        $activeGuardia = $this->getActiveGuardiaForToday($today);

        if (!$activeGuardia || !$this->isBomberoInGuardia($bombero->id, $activeGuardia->id, $today)) {
            return redirect()->route('camas.scan.not_in_guardia', ['bedId' => $bedId]);
        }

        // Liberar cama si está ocupada
        $currentAssignment = BedAssignment::query()
            ->where('bed_id', $bed->id)
            ->whereNull('released_at')
            ->first();

        if ($currentAssignment) {
            $currentAssignment->update([
                'released_at' => now(),
            ]);
        }

        // Verificar si el bombero ya tiene otra cama asignada
        $existingAssignment = BedAssignment::query()
            ->where('firefighter_id', $bombero->id)
            ->whereNull('released_at')
            ->first();

        if ($existingAssignment) {
            // Liberar la cama anterior
            $existingAssignment->update([
                'released_at' => now(),
            ]);
        }

        // Crear nueva asignación
        BedAssignment::create([
            'bed_id' => $bed->id,
            'firefighter_id' => $bombero->id,
            'assigned_at' => now(),
            'notes' => 'Asignado vía QR escaneado',
            'assigned_source' => 'qr',
            'assigned_ip' => (string) ($request->ip() ?? ''),
            'assigned_user_agent' => (string) $request->userAgent(),
        ]);

        // Limpiar sesión del bombero después de asignar
        $request->session()->forget('bed_qr_bombero_id');

        return redirect()->route('camas.scan.success', ['bedId' => $bedId])
            ->with('success', '¡Cama asignada correctamente!');
    }

    /**
     * Página de éxito
     */
    public function success(Request $request, int $bedId)
    {
        $bed = Bed::query()->findOrFail($bedId);

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
     * Vista imprimible del QR de una cama (para pegar físicamente)
     */
    public function printQr(Request $request, int $bedId)
    {
        $bed = Bed::query()->findOrFail($bedId);
        $url = route('camas.scan.form', ['bedId' => $bed->id]);

        $qrSvg = \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')
            ->size(520)
            ->margin(1)
            ->generate($url);

        return view('camas.qr_print', [
            'bed' => $bed,
            'url' => $url,
            'qrSvg' => $qrSvg,
        ]);
    }

    /**
     * Obtiene la guardia activa para hoy (o la de ayer si aún está en horario de guardia)
     */
    private function getActiveGuardiaForToday(string $date): ?Guardia
    {
        $scheduleTz = SystemSetting::getValue('guardia_schedule_tz', env('GUARDIA_SCHEDULE_TZ', config('app.timezone')));
        $now = Carbon::now($scheduleTz);
        
        // Primero buscar guardias que iniciaron hoy
        $shiftUser = ShiftUser::query()
            ->whereDate('start_time', $date)
            ->whereNotNull('guardia_id')
            ->first();

        if ($shiftUser && $shiftUser->guardia) {
            return $shiftUser->guardia;
        }

        // Si no hay guardia hoy y estamos antes de las 07:00, buscar la de ayer
        // (la guardia de ayer podría estar activa hasta las 07:00)
        $yesterday = Carbon::parse($date)->subDay()->toDateString();
        $currentHour = (int) $now->format('H');
        
        if ($currentHour < 7) {
            $shiftUser = ShiftUser::query()
                ->whereDate('start_time', $yesterday)
                ->whereNotNull('guardia_id')
                ->first();

            if ($shiftUser && $shiftUser->guardia) {
                return $shiftUser->guardia;
            }
        }

        return null;
    }

    /**
     * Verifica si un bombero está en una guardia específica
     */
    private function isBomberoInGuardia(int $bomberoId, int $guardiaId, string $date): bool
    {
        $scheduleTz = SystemSetting::getValue('guardia_schedule_tz', env('GUARDIA_SCHEDULE_TZ', config('app.timezone')));
        $now = Carbon::now($scheduleTz);
        $currentHour = (int) $now->format('H');
        
        // Buscar en el día actual
        $existsToday = ShiftUser::query()
            ->whereDate('start_time', $date)
            ->where('guardia_id', $guardiaId)
            ->where(function ($query) use ($bomberoId) {
                $query->where('firefighter_id', $bomberoId)
                    ->orWhereHas('firefighter', function ($q) use ($bomberoId) {
                        $q->where('id', $bomberoId);
                    });
            })
            ->exists();
            
        if ($existsToday) {
            return true;
        }
        
        // Si no está hoy y es antes de las 07:00, buscar en la guardia de ayer
        if ($currentHour < 7) {
            $yesterday = Carbon::parse($date)->subDay()->toDateString();
            $existsYesterday = ShiftUser::query()
                ->whereDate('start_time', $yesterday)
                ->where('guardia_id', $guardiaId)
                ->where(function ($query) use ($bomberoId) {
                    $query->where('firefighter_id', $bomberoId)
                        ->orWhereHas('firefighter', function ($q) use ($bomberoId) {
                            $q->where('id', $bomberoId);
                        });
                })
                ->exists();
                
            if ($existsYesterday) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Verifica si la hora actual está dentro del horario de guardia
     * Domingo-Jueves: 23:00 a 07:00
     * Viernes-Sábado: 22:00 a 07:00
     */
    private function isWithinGuardiaHours(): bool
    {
        $scheduleTz = SystemSetting::getValue('guardia_schedule_tz', env('GUARDIA_SCHEDULE_TZ', config('app.timezone')));
        $now = Carbon::now($scheduleTz);

        // Horarios de inicio según día de la semana
        // 0=Domingo, 5=Viernes, 6=Sábado
        $isWeekendStart = $now->isFriday() || $now->isSaturday();
        
        $startTime = $isWeekendStart 
            ? SystemSetting::getValue('guardia_constitution_sunday_time', '22:00')  // Viernes y sábado inician a las 22:00
            : SystemSetting::getValue('guardia_constitution_weekday_time', '23:00');  // Domingo a jueves inician a las 23:00

        $endTime = SystemSetting::getValue('guardia_daily_end_time', '07:00');

        [$startH, $startM] = array_map('intval', explode(':', (string) $startTime));
        [$endH, $endM] = array_map('intval', explode(':', (string) $endTime));

        $startAt = $now->copy()->startOfDay()->addHours($startH)->addMinutes($startM);
        $endAt = $now->copy()->startOfDay()->addHours($endH)->addMinutes($endM);

        // Si estamos después de la hora de inicio HOY
        if ($now->greaterThanOrEqualTo($startAt)) {
            return true;
        }

        // Si estamos antes de la hora de fin HOY (significa que la guardia de AYER sigue activa)
        if ($now->lessThan($endAt)) {
            return true;
        }

        return false;
    }
}
