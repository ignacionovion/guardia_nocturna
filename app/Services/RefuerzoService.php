<?php

namespace App\Services;

use App\Models\Bombero;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RefuerzoService
{
    /**
     * Expira refuerzos que han pasado su tiempo límite (07:00 AM del día siguiente)
     * y los devuelve a su guardia anterior.
     */
    public static function expire(?Carbon $now = null): int
    {
        if (!Schema::hasTable('bomberos')) {
            return 0;
        }

        $nowApp = ($now ?: now())->copy()->setTimezone(config('app.timezone'));

        // Buscar bomberos que son refuerzos y tienen refuerzo_guardia_anterior_id
        // Los refuerzos deben expirar a las 07:00 AM del día siguiente
        $scheduleTz = env('GUARDIA_SCHEDULE_TZ', 'America/Santiago');
        $nowLocal = $nowApp->copy()->setTimezone($scheduleTz);
        
        // Si es antes de las 07:00, no expirar refuerzos del turno actual
        if ($nowLocal->hour < 7) {
            return 0;
        }

        // Buscar refuerzos activos (es_refuerzo = true y tienen guardia asignada)
        $refuerzos = Bombero::query()
            ->where('es_refuerzo', true)
            ->whereNotNull('guardia_id')
            ->whereNotNull('refuerzo_guardia_anterior_id')
            ->get();

        if ($refuerzos->isEmpty()) {
            return 0;
        }

        foreach ($refuerzos as $refuerzo) {
            DB::transaction(function () use ($refuerzo) {
                $prevGuardiaId = $refuerzo->refuerzo_guardia_anterior_id;

                $refuerzo->update([
                    'guardia_id' => $prevGuardiaId,
                    'es_refuerzo' => false,
                    'refuerzo_guardia_anterior_id' => null,
                    'estado_asistencia' => 'constituye',
                    'es_jefe_guardia' => false,
                    'es_cambio' => false,
                    'es_sancion' => false,
                ]);
            });
        }

        return $refuerzos->count();
    }
}
