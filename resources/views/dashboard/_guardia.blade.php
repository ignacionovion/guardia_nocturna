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
    
    {{-- DEBUG TEMPORAL - DIAGNÓSTICO HORARIO --}}
    <div class="mx-4 md:mx-6 lg:mx-8 mt-4 p-4 bg-yellow-50 dark:bg-yellow-900/20 border-2 border-yellow-500 rounded-lg">
        <div class="text-sm font-mono space-y-1">
            <div class="font-bold text-yellow-900 dark:text-yellow-200 mb-2">🔍 DEBUG TEMPORAL - DIAGNÓSTICO EDICIÓN BLOQUEADA</div>
            
            <div class="grid grid-cols-2 gap-x-4 gap-y-1 text-xs">
                <div><strong>Zona Horaria:</strong> {{ $guardiaTz }}</div>
                <div><strong>Hora Sistema (UTC):</strong> {{ now()->toIso8601String() }}</div>
                
                <div><strong>Hora Local (TZ):</strong> {{ $localNow->toIso8601String() }}</div>
                <div><strong>Hora Actual:</strong> {{ $localNow->format('H:i:s') }}</div>
                
                <div class="col-span-2 border-t border-yellow-300 dark:border-yellow-700 my-1"></div>
                
                <div><strong>Ventana Enable:</strong> {{ $enableTime }}</div>
                <div><strong>Ventana Disable:</strong> {{ $disableTime }}</div>
                
                <div class="col-span-2 border-t border-yellow-300 dark:border-yellow-700 my-1"></div>
                
                <div><strong>Draft Operational Date:</strong> {{ $draftOpDate->toDateString() }}</div>
                <div><strong>Draft Opened At:</strong> {{ $draftOpenedAt->format('Y-m-d H:i:s') }}</div>
                
                <div><strong>Draft Close At:</strong> {{ $draftCloseAt->format('Y-m-d H:i:s') }}</div>
                <div><strong class="{{ $draftEditable ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">__draftEditable:</strong> <span class="font-bold">{{ $draftEditable ? 'TRUE ✓' : 'FALSE ✗' }}</span></div>
                
                <div class="col-span-2 border-t border-yellow-300 dark:border-yellow-700 my-1"></div>
                
                <div><strong>now >= opened:</strong> {{ $localNow->greaterThanOrEqualTo($draftOpenedAt) ? 'TRUE ✓' : 'FALSE ✗' }}</div>
                <div><strong>now < close:</strong> {{ $localNow->lessThan($draftCloseAt) ? 'TRUE ✓' : 'FALSE ✗' }}</div>
                
                <div class="col-span-2 mt-2 p-2 {{ $draftEditable ? 'bg-green-100 dark:bg-green-900/30' : 'bg-red-100 dark:bg-red-900/30' }} rounded">
                    <strong>Resultado:</strong> 
                    @if($draftEditable)
                        <span class="text-green-700 dark:text-green-300">EDITABLE - Debería funcionar</span>
                    @else
                        <span class="text-red-700 dark:text-red-300">BLOQUEADO - Por eso no funciona</span>
                    @endif
                </div>
            </div>
            
            <div class="mt-2 text-xs text-yellow-800 dark:text-yellow-300">
                <strong>JS window.__draftEditable:</strong> <span id="debug-js-editable">Cargando...</span>
            </div>
        </div>
    </div>
    <script>
        setTimeout(() => {
            const elem = document.getElementById('debug-js-editable');
            if (elem) {
                elem.textContent = window.__draftEditable ? 'TRUE ✓' : 'FALSE ✗';
                elem.className = window.__draftEditable ? 'font-bold text-green-600' : 'font-bold text-red-600';
            }
        }, 1000);
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
