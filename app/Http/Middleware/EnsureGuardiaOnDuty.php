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
        \Log::info('=== EnsureGuardiaOnDuty MIDDLEWARE START ===');
        \Log::info('Request details', [
            'path' => $request->path(),
            'fullUrl' => $request->fullUrl(),
            'method' => $request->method(),
            'host' => $request->getHost(),
            'route_name' => $request->route() ? $request->route()->getName() : 'NULL',
        ]);

        $user = $request->user();

        if (!$user) {
            \Log::info('No authenticated user - ALLOWING request to continue');
            return $next($request);
        }

        \Log::info('Authenticated user', [
            'user_id' => $user->id,
            'user_role' => $user->role,
            'user_email' => $user->email ?? 'N/A',
            'guardia_id' => $user->guardia_id ?? 'NULL',
        ]);

        $tenant = tenant();
        \Log::info('Tenant context', [
            'tenant_id' => $tenant ? $tenant->id : 'NULL',
        ]);

        if ($user->role !== 'guardia') {
            \Log::info('User is NOT guardia role - ALLOWING request to continue', [
                'user_role' => $user->role,
            ]);
            return $next($request);
        }

        \Log::info('User IS guardia role - checking guardia_id');

        if (!$user->guardia_id) {
            \Log::warning('Guardia user has no guardia_id');
            if ($request->routeIs('guardia.off_duty')) {
                \Log::info('Route is guardia.off_duty - ALLOWING');
                return $next($request);
            }
            \Log::warning('BLOCKING - redirecting to guardia.off_duty (no guardia_id)');
            return redirect()->route('guardia.off_duty');
        }

        \Log::info('Checking if guardia is on duty', ['guardia_id' => $user->guardia_id]);
        $isOnDuty = $this->isGuardiaOnDuty((int) $user->guardia_id);
        \Log::info('On duty check result', ['is_on_duty' => $isOnDuty]);

        if ($request->routeIs('guardia.off_duty')) {
            \Log::info('Route is guardia.off_duty');
            if ($isOnDuty) {
                \Log::info('BLOCKING - guardia is on duty, redirecting to dashboard');
                return redirect()->route('dashboard');
            }
            \Log::info('ALLOWING - guardia is off duty, can access off_duty page');
            return $next($request);
        }

        if (!$isOnDuty) {
            \Log::warning('BLOCKING - guardia is NOT on duty, redirecting to guardia.off_duty');
            return redirect()->route('guardia.off_duty');
        }

        \Log::info('=== EnsureGuardiaOnDuty ALLOWING REQUEST ===');
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
