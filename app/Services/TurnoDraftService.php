<?php

namespace App\Services;

use App\Models\Guardia;
use App\Models\SystemSetting;
use App\Models\TurnoSession;
use Carbon\Carbon;

class TurnoDraftService
{
    public function __construct(
        private ?string $tz = null
    ) {
        $this->tz = $this->tz ?: SystemSetting::getValue('guardia_schedule_tz', env('GUARDIA_SCHEDULE_TZ', config('app.timezone')));
    }

    public function timezone(): string
    {
        return (string) $this->tz;
    }

    /**
     * Devuelve la fecha operativa del turno nocturno bajo la regla fija:
     * Turno = 22:00 -> 07:00 (día siguiente).
     */
    public function resolveOperationalDate(?Carbon $now = null): Carbon
    {
        $now = ($now ?: Carbon::now($this->timezone()))->copy()->setTimezone($this->timezone());

        $day = $now->copy()->startOfDay();
        $hour = (int) $now->hour;

        // Antes de 07:00 o antes de 22:00 => el turno operativo es el día anterior.
        if ($hour < 7) {
            $day->subDay();
        } elseif ($hour < 22) {
            $day->subDay();
        }

        return $day;
    }

    public function windowForOperationalDate(Carbon $operationalDate): array
    {
        $operationalDate = $operationalDate->copy()->setTimezone($this->timezone())->startOfDay();

        $openedAt = $operationalDate->copy()->setTime(22, 0, 0);
        $closeAt = $operationalDate->copy()->addDay()->setTime(7, 0, 0);

        return [$openedAt, $closeAt];
    }

    public function isEditableNow(?Carbon $now = null): bool
    {
        $now = ($now ?: Carbon::now($this->timezone()))->copy()->setTimezone($this->timezone());
        $operationalDate = $this->resolveOperationalDate($now);
        [$openedAt, $closeAt] = $this->windowForOperationalDate($operationalDate);

        return $now->greaterThanOrEqualTo($openedAt) && $now->lessThan($closeAt);
    }

    public function getOrCreateDraftForGuardia(Guardia $guardia, ?int $userId = null): TurnoSession
    {
        $now = Carbon::now($this->timezone());
        $operationalDate = $this->resolveOperationalDate($now);
        [$openedAt, $closeAt] = $this->windowForOperationalDate($operationalDate);

        $session = TurnoSession::query()
            ->where('guardia_id', $guardia->id)
            ->whereDate('operational_date', $operationalDate->toDateString())
            ->first();

        if ($session) {
            return $session;
        }

        $status = ($now->greaterThanOrEqualTo($openedAt) && $now->lessThan($closeAt)) ? 'draft' : 'closed';

        return TurnoSession::create([
            'guardia_id' => $guardia->id,
            'operational_date' => $operationalDate->toDateString(),
            'opened_at' => $openedAt,
            'close_at' => $closeAt,
            'status' => $status,
            'created_by_user_id' => $userId,
            'updated_by_user_id' => $userId,
        ]);
    }
}
