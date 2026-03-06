<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\BedAssignment;
use App\Models\Bombero;
use App\Models\Guardia;
use App\Models\ReemplazoBombero;
use App\Models\SystemSetting;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Servicio para manejar el reset automático diario de reemplazos/refuerzos
 * y la liberación de camas asignadas a las 07:00 AM.
 */
class DailyResetService
{
    /**
     * Ejecuta el reset diario para todas las guardias activas.
     * Se debe llamar a las 07:00 AM via cron/console command.
     */
    public function executeDailyReset(?Carbon $now = null): array
    {
        $now = $now ?? now();
        $results = [];

        // Obtener todas las guardias
        $guardias = Guardia::all();

        foreach ($guardias as $guardia) {
            $tz = SystemSetting::getValue('guardia_schedule_tz', env('GUARDIA_SCHEDULE_TZ', 'America/Santiago'));
            $localNow = $now->copy()->setTimezone($tz);
            $dailyEndTime = SystemSetting::getValue('guardia_daily_end_time', '07:00');
            [$endH, $endM] = array_map('intval', explode(':', (string) $dailyEndTime));
            $endAt = $localNow->copy()->setTime($endH, $endM, 0);

            // Solo ejecutar si ya pasamos la hora de cierre
            if ($localNow->greaterThanOrEqualTo($endAt)) {
                $result = $this->executeResetForGuardia($guardia->id, $now);
                $results[$guardia->name] = $result;
            }
        }

        return $results;
    }

    /**
     * Ejecuta el reset para una guardia específica.
     */
    public function executeResetForGuardia(int $guardiaId, ?Carbon $now = null): array
    {
        $now = $now ?? now();
        $tz = SystemSetting::getValue('guardia_schedule_tz', env('GUARDIA_SCHEDULE_TZ', 'America/Santiago'));
        $localNow = $now->copy()->setTimezone($tz);
        $dailyEndTime = SystemSetting::getValue('guardia_daily_end_time', '07:00');
        [$endH, $endM] = array_map('intval', explode(':', (string) $dailyEndTime));
        $endAt = $localNow->copy()->setTime($endH, $endM, 0);

        // Convertir cutoff a UTC para comparar con updated_at
        $cutoffForReset = $endAt->copy()->setTimezone('UTC');

        $stats = [
            'cutoff_time' => $cutoffForReset->toDateTimeString(),
            'bomberos_reseteados' => 0,
            'camas_liberadas' => 0,
            'reemplazos_completados' => 0,
        ];

        DB::transaction(function () use ($guardiaId, $cutoffForReset, $localNow, &$stats) {
            // Obtener IDs de bomberos no titulares que serán reseteados
            $resetBomberoIds = Bombero::query()
                ->where('guardia_id', $guardiaId)
                ->where('es_titular', false)
                ->where('updated_at', '<', $cutoffForReset)
                ->pluck('id')
                ->toArray();

            if (!empty($resetBomberoIds)) {
                $stats['bomberos_reseteados'] = count($resetBomberoIds);

                // Marcar reemplazos como completados
                $reemplazosUpdated = ReemplazoBombero::query()
                    ->where('estado', 'activo')
                    ->where('guardia_id', $guardiaId)
                    ->whereIn('bombero_reemplazante_id', $resetBomberoIds)
                    ->update([
                        'estado' => 'completado',
                        'fin' => $localNow,
                    ]);
                $stats['reemplazos_completados'] = $reemplazosUpdated;

                // Liberar las camas asignadas a estos bomberos
                $assignmentsToRelease = BedAssignment::query()
                    ->whereNull('released_at')
                    ->whereIn('firefighter_id', $resetBomberoIds)
                    ->with('bed')
                    ->get();

                $camasLiberadas = 0;
                foreach ($assignmentsToRelease as $assignment) {
                    $assignment->update(['released_at' => now()]);
                    if ($assignment->bed) {
                        $assignment->bed->update(['status' => 'available']);
                        $camasLiberadas++;
                    }
                }
                $stats['camas_liberadas'] = $camasLiberadas;

                // Resetear los bomberos no titulares (devolverlos a sus guardias originales)
                Bombero::query()
                    ->where('guardia_id', $guardiaId)
                    ->where('es_titular', false)
                    ->where('updated_at', '<', $cutoffForReset)
                    ->get()
                    ->each(function (Bombero $b) use ($guardiaId) {
                        $restoreGuardiaId = null;
                        if ((bool) ($b->es_refuerzo ?? false)) {
                            $restoreGuardiaId = $b->refuerzo_guardia_anterior_id;
                        } else {
                            // Es un reemplazo - buscar en reemplazos_bomberos la guardia original
                            $reemplazo = ReemplazoBombero::where('bombero_reemplazante_id', $b->id)
                                ->where('estado', 'activo')
                                ->where('guardia_id', $guardiaId)
                                ->first();
                            if ($reemplazo) {
                                $restoreGuardiaId = $reemplazo->originalFirefighter?->guardia_id;
                            }
                        }

                        $b->update([
                            'guardia_id' => $restoreGuardiaId,
                            'estado_asistencia' => 'constituye',
                            'es_jefe_guardia' => false,
                            'es_refuerzo' => false,
                            'refuerzo_guardia_anterior_id' => null,
                            'es_cambio' => false,
                            'es_sancion' => false,
                        ]);
                    });
            }

            // Auto-reset de estado_asistencia para TITULARES
            $titularesUpdated = Bombero::query()
                ->where('guardia_id', $guardiaId)
                ->where('es_titular', true)
                ->where('estado_asistencia', '!=', 'constituye')
                ->where('updated_at', '<', $cutoffForReset)
                ->update([
                    'estado_asistencia' => 'constituye',
                    'es_cambio' => false,
                    'es_sancion' => false,
                ]);

            $stats['titulares_reseteados'] = $titularesUpdated;
        });

        Log::info('DailyResetService ejecutado para guardia ' . $guardiaId, $stats);

        return $stats;
    }
}
