<?php

namespace App\Http\Middleware;

use App\Models\Guardia;
use App\Models\GuardiaCalendarDay;
use App\Models\SystemSetting;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureGuardiaOnDuty
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        if ($user->role !== 'guardia') {
            return $next($request);
        }

        if (!$user->guardia_id) {
            if ($request->routeIs('guardia.off_duty')) {
                return $next($request);
            }
            return redirect()->route('guardia.off_duty');
        }

        $isOnDuty = $this->isGuardiaOnDuty((int) $user->guardia_id);

        if ($request->routeIs('guardia.off_duty')) {
            if ($isOnDuty) {
                return redirect()->route('dashboard');
            }
            return $next($request);
        }

        if (!$isOnDuty) {
            return redirect()->route('guardia.off_duty');
        }

        return $next($request);
    }

    private function isGuardiaOnDuty(int $guardiaId): bool
    {
        $shiftDay = $this->resolveShiftDay(Carbon::now());

        $calendarDay = GuardiaCalendarDay::where('date', $shiftDay->toDateString())->first();

        if (!$calendarDay) {
            $calendarDay = GuardiaCalendarDay::where('date', $shiftDay->copy()->startOfWeek(Carbon::SUNDAY)->toDateString())->first();
        }

        if ($calendarDay && $calendarDay->guardia_id) {
            return (int) $calendarDay->guardia_id === $guardiaId;
        }

        return (bool) Guardia::where('id', $guardiaId)->where('is_active_week', true)->exists();
    }

    private function resolveShiftDay(Carbon $dateTime): Carbon
    {
        $scheduleTz = SystemSetting::getValue('guardia_schedule_tz', env('GUARDIA_SCHEDULE_TZ', config('app.timezone')));
        $dailyEndTime = SystemSetting::getValue('guardia_daily_end_time', '07:00');

        [$endH, $endM] = array_map('intval', explode(':', (string) $dailyEndTime));

        $local = $dateTime->copy()->setTimezone($scheduleTz);
        $day = $local->copy()->startOfDay();
        $dayEnd = $day->copy()->setTime($endH, $endM, 0);

        if ($local->lt($dayEnd)) {
            $day->subDay();
        }

        return $day;
    }
}
