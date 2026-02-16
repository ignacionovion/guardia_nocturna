<?php

namespace App\Services;

use App\Models\Bombero;
use App\Models\ReemplazoBombero;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ReplacementService
{
    public static function calculateReplacementUntil(Carbon $at): Carbon
    {
        $scheduleTz = env('GUARDIA_SCHEDULE_TZ', 'America/Santiago');
        $atLocal = $at->copy()->setTimezone($scheduleTz);

        $scheduleHourToday = $atLocal->isSunday() ? 22 : 23;
        $todayStart = $atLocal->copy()->startOfDay()->addHours($scheduleHourToday);

        if ($atLocal->greaterThanOrEqualTo($todayStart)) {
            $shiftStart = $todayStart;
        } else {
            if ($atLocal->hour < 7) {
                $yesterday = $atLocal->copy()->subDay();
                $scheduleHourYesterday = $yesterday->isSunday() ? 22 : 23;
                $shiftStart = $yesterday->copy()->startOfDay()->addHours($scheduleHourYesterday);
            } else {
                $shiftStart = $todayStart;
            }
        }

        $expiresAtLocal = $shiftStart->copy()->addDay()->startOfDay()->addHours(7);

        while ($expiresAtLocal->isWeekend()) {
            $expiresAtLocal->addDay();
        }

        return $expiresAtLocal->setTimezone(config('app.timezone'));
    }

    public static function expire(?Carbon $now = null): int
    {
        if (!Schema::hasTable('reemplazos_bomberos')) {
            return 0;
        }

        $nowApp = ($now ?: now())->copy()->setTimezone(config('app.timezone'));

        $expired = ReemplazoBombero::query()
            ->where('estado', 'activo')
            ->whereNotNull('fin')
            ->where('fin', '<=', $nowApp)
            ->with(['originalFirefighter', 'replacementFirefighter'])
            ->get();

        if ($expired->isEmpty()) {
            return 0;
        }

        foreach ($expired as $r) {
            DB::transaction(function () use ($r, $nowApp) {
                $original = $r->originalFirefighter;
                $replacer = $r->replacementFirefighter;

                $prevGuardiaId = null;
                if ($r->notas) {
                    $decoded = json_decode((string) $r->notas, true);
                    if (is_array($decoded)) {
                        $prevGuardiaId = $decoded['replacement_previous_guardia_id'] ?? null;
                    }
                }

                $r->update([
                    'estado' => 'cerrado',
                    'fin' => $nowApp,
                ]);

                if ($original instanceof Bombero) {
                    $original->update([
                        'estado_asistencia' => 'constituye',
                        'es_jefe_guardia' => false,
                        'es_cambio' => false,
                        'es_sancion' => false,
                    ]);
                }

                if ($replacer instanceof Bombero) {
                    $replacer->update([
                        'guardia_id' => $prevGuardiaId,
                        'estado_asistencia' => 'constituye',
                        'es_jefe_guardia' => false,
                        'es_cambio' => false,
                        'es_sancion' => false,
                    ]);
                }
            });
        }

        return $expired->count();
    }
}
