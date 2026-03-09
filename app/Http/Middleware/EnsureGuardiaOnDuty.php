<?php

namespace App\Http\Middleware;

use App\Models\Guardia;
use App\Models\GuardiaCalendarDay;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureGuardiaOnDuty
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || $user->role !== 'guardia') {
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
        $now = Carbon::now();
        $weekStart = $now->copy()->startOfWeek(Carbon::SUNDAY);

        $calendarDay = GuardiaCalendarDay::where('date', $weekStart->toDateString())->first();

        if (!$calendarDay) {
            $calendarDay = GuardiaCalendarDay::where('date', $now->toDateString())->first();
        }

        if ($calendarDay && $calendarDay->guardia_id) {
            return (int) $calendarDay->guardia_id === $guardiaId;
        }

        return (bool) Guardia::where('id', $guardiaId)->where('is_active_week', true)->exists();
    }
}
