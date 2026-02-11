<?php

namespace App\Services;

use Carbon\Carbon;
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
        // Reemplazos legacy via users.* ya no se usan en el dominio operativo.
        // La expiración real se maneja via reemplazos_bomberos + actualizaciones de Bombero.
        return 0;
    }
}
