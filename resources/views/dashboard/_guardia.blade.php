@php
use App\Models\SystemSetting;
    use Carbon\Carbon;
    use App\Services\TurnoDraftService;
    
    // === CONFIGURACIÓN DE ZONA HORARIA ===
    $guardiaTz = SystemSetting::getValue('guardia_schedule_tz', env('GUARDIA_SCHEDULE_TZ', config('app.timezone')));
    $localNow = now()->copy()->setTimezone($guardiaTz);
    
    // === DEBUG DRAFT EDITABLE ===
    $draftService = app(TurnoDraftService::class);
    $draftEditable = $draftService->isEditableNow();
    $draftOpDate = $draftService->resolveOperationalDate($localNow);
    [$draftOpenedAt, $draftCloseAt] = $draftService->windowForOperationalDate($draftOpDate);
    
    // === VENTANA DE ASISTENCIA ===
    $enableTime = SystemSetting::getValue('attendance_enable_time', '22:00');
    $disableTime = SystemSetting::getValue('attendance_disable_time', '07:00');
    [$eH, $eM] = array_map('intval', explode(':', (string) $enableTime));
    [$dH, $dM] = array_map('intval', explode(':', (string) $disableTime));
    $nowMins = $localNow->hour * 60 + $localNow->minute;
    $enableMins = $eH * 60 + $eM;
    $disableMins = $dH * 60 + $dM;
    
    $attendanceEnabled = ($enableMins > $disableMins)
        ? ($nowMins >= $enableMins || $nowMins < $disableMins)
        : ($nowMins >= $enableMins && $nowMins < $disableMins);
    
    // === CIERRE DE TURNO ===
    $endTime = SystemSetting::getValue('guardia_daily_end_time', '07:00');
    [$endH, $endM] = array_map('intval', explode(':', (string) $endTime));
    $dailyEndAt = $localNow->copy()->setTime($endH, $endM, 0);
    $shiftClosedForToday = $localNow->greaterThanOrEqualTo($dailyEndAt);
    
    // === FILTRAR Y ORDENAR PERSONAL ===
    $replacementByOriginal = $replacementByOriginal ?? collect();
    $replacementByReplacement = $replacementByReplacement ?? collect();
    
    $activeStaff = $myStaff->reject(function ($u) use ($replacementByOriginal) {
        $isReplaced = $replacementByOriginal->has($u->id);
        return ($u->fuera_de_servicio ?? false) || $isReplaced;
    });
    
    $activeStaff = $activeStaff
        ->sortBy(function ($u) use ($replacementByReplacement) {
            $isReplacement = $replacementByReplacement->has($u->id);
            $isRefuerzo = $u->es_refuerzo ?? false;
            return sprintf('%d-%s-%s', ($isReplacement || $isRefuerzo) ? 1 : 0, $u->apellido_paterno ?? '', $u->nombres ?? '');
        })
        ->values();
    
    $onDutyStaff = $activeStaff->filter(fn($u) => in_array($u->estado_asistencia, ['constituye', 'reemplazo'], true));
    
    $visibleStaffCount = $activeStaff->count();
    // Only count constituye and reemplazo as present (ausente is a different state)
    $presentStaffCount = $onDutyStaff->count();
    
    // === ESTADO DE ASISTENCIA ===
    $isAfter2200 = $localNow->hour >= 22;
    $hasAttendanceSavedToday = $hasAttendanceSavedToday ?? false;
    $attendanceIsStale = $attendanceIsStale ?? false;
    
    if (!$attendanceEnabled) {
        $attendanceMessage = 'FUERA DE HORARIO DE REGISTRO';
        $attendanceVariant = 'default';
    } elseif ($hasAttendanceSavedToday && $attendanceIsStale) {
        $attendanceMessage = 'ASISTENCIA DESACTUALIZADA';
        $attendanceVariant = 'warning';
    } elseif ($hasAttendanceSavedToday) {
        $attendanceMessage = 'ASISTENCIA REGISTRADA';
        $attendanceVariant = 'success';
    } elseif ($shiftClosedForToday) {
        $attendanceMessage = 'RECORDAR REGISTRO 22:00';
        $attendanceVariant = 'warning';
    } else {
        $attendanceMessage = $isAfter2200 ? 'GUARDAR ANTES DE IRTE' : 'SIN REGISTRAR';
        $attendanceVariant = 'danger';
    }
    
    // === CLASES DEL BOTÓN DE ASISTENCIA ===
    $attendanceButtonDisabled = !$attendanceEnabled;
    $attendanceButtonClass = $attendanceEnabled 
        ? 'bg-slate-800 hover:bg-slate-700 text-white border-slate-700' 
        : 'bg-slate-800/50 text-slate-500 border-slate-800 cursor-not-allowed';
    $attendanceIconClass = $attendanceEnabled ? 'text-emerald-400' : 'text-slate-500';
    
    // === PERMISOS ===
    $canInhabilitar = in_array(Auth::user()->role, ['super_admin', 'capitania', 'guardia'], true);
    
    // === DATOS DE WIDGETS ===
    $isMyGuardiaOnDuty = $isMyGuardiaOnDuty ?? false;
    $outOfServiceStaff = $outOfServiceStaff ?? collect();
    $bedByFirefighter = $bedByFirefighter ?? [];
    $birthdaysList = $birthdaysThisMonth ?? $birthdays ?? collect();
    $currentMonthName = mb_strtoupper(Carbon::now()->locale('es')->translatedFormat('F'), 'UTF-8');
    $noveltiesList = $guardiaNovelties ?? $novelties ?? collect();
    $noveltiesPaginator = $guardiaNovelties ?? null;
    $academiesList = $academies ?? collect();
    $attendanceEnableTime = $attendanceEnableTime ?? $enableTime;
    $attendanceDisableTime = $attendanceDisableTime ?? $disableTime;
@endphp

<div id="guardia-dashboard-root" class="w-full min-h-screen">
    <script>
        window.__draftEditable = @json($draftEditable);
    </script>
    
    @include('dashboard.partials.guardia._header')

    {{-- Main Content Area --}}
    <main class="px-4 md:px-6 lg:px-8 py-6">
        <div class="grid grid-cols-1 xl:grid-cols-[1fr_400px] gap-6">
            @include('dashboard.partials.guardia._staff_grid')
            @include('dashboard.partials.guardia._sidebar')
        </div>
    </main>
</div>
