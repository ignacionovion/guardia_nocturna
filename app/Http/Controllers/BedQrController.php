<?php

namespace App\Http\Controllers;

use App\Models\Bed;
use App\Models\BedAssignment;
use App\Models\Bombero;
use App\Models\Guardia;
use App\Models\ShiftUser;
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
            return back()->with('warning', 'No hay guardia activa en este momento.');
        }

        // Verificar si el bombero está en la guardia activa
        $isInActiveGuardia = $this->isBomberoInGuardia($bombero->id, $activeGuardia->id, $today);

        if (!$isInActiveGuardia) {
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
     * Obtiene la guardia activa para hoy
     */
    private function getActiveGuardiaForToday(string $date): ?Guardia
    {
        $shiftUser = ShiftUser::query()
            ->whereDate('start_time', $date)
            ->whereNotNull('guardia_id')
            ->first();

        if ($shiftUser && $shiftUser->guardia) {
            return $shiftUser->guardia;
        }

        return null;
    }

    /**
     * Verifica si un bombero está en una guardia específica
     */
    private function isBomberoInGuardia(int $bomberoId, int $guardiaId, string $date): bool
    {
        return ShiftUser::query()
            ->whereDate('start_time', $date)
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
