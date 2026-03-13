<?php

namespace App\View;

use App\Models\SystemSetting;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

/**
 * Prepara todos los datos necesarios para el dashboard de guardia.
 * Mueve la lógica compleja fuera de Blade.
 */
class GuardiaDashboardData
{
    private string $guardiaTz;
    private Carbon $localNow;
    
    public function __construct()
    {
        $this->guardiaTz = SystemSetting::getValue(
            'guardia_schedule_tz', 
            env('GUARDIA_SCHEDULE_TZ', config('app.timezone'))
        );
        $this->localNow = now()->copy()->setTimezone($this->guardiaTz);
    }

    /**
     * Determina si el panel de guardia debe mostrarse.
     */
    public static function shouldShowPanel($myGuardia): bool
    {
        return Auth::check() 
            && Auth::user()->role === 'guardia' 
            && isset($myGuardia) 
            && $myGuardia;
    }

    /**
     * Verifica si la ventana de asistencia está habilitada.
     */
    public function isAttendanceEnabled(): bool
    {
        $enableTime = SystemSetting::getValue('attendance_enable_time', '22:00');
        $disableTime = SystemSetting::getValue('attendance_disable_time', '07:00');
        
        [$eH, $eM] = array_map('intval', explode(':', (string) $enableTime));
        [$dH, $dM] = array_map('intval', explode(':', (string) $disableTime));
        
        $nowMins = $this->localNow->hour * 60 + $this->localNow->minute;
        $enableMins = $eH * 60 + $eM;
        $disableMins = $dH * 60 + $dM;
        
        if ($enableMins > $disableMins) {
            return $nowMins >= $enableMins || $nowMins < $disableMins;
        }
        
        return $nowMins >= $enableMins && $nowMins < $disableMins;
    }

    /**
     * Verifica si el turno ya cerró para hoy.
     */
    public function isShiftClosedForToday(): bool
    {
        $endTime = SystemSetting::getValue('guardia_daily_end_time', '07:00');
        [$endH, $endM] = array_map('intval', explode(':', (string) $endTime));
        $dailyEndAt = $this->localNow->copy()->setTime($endH, $endM, 0);
        
        return $this->localNow->greaterThanOrEqualTo($dailyEndAt);
    }

    /**
     * Filtra el personal activo (excluye fuera de servicio y reemplazados).
     */
    public function filterActiveStaff(Collection $myStaff, ?Collection $replacementByOriginal): Collection
    {
        return $myStaff->reject(function ($u) use ($replacementByOriginal) {
            $isReplaced = $replacementByOriginal && $replacementByOriginal->has($u->id);
            return ($u->fuera_de_servicio ?? false) || $isReplaced;
        });
    }

    /**
     * Ordena el personal (reemplazos y refuerzos al final).
     */
    public function sortActiveStaff(Collection $activeStaff, ?Collection $replacementByReplacement): Collection
    {
        return $activeStaff
            ->sortBy(function ($u) use ($replacementByReplacement) {
                $isReplacement = $replacementByReplacement && $replacementByReplacement->has($u->id);
                $isRefuerzo = $u->es_refuerzo ?? false;
                $apellido = $u->apellido_paterno ?? '';
                $nombres = $u->nombres ?? '';
                return sprintf('%d-%s-%s', ($isReplacement || $isRefuerzo) ? 1 : 0, $apellido, $nombres);
            })
            ->values();
    }

    /**
     * Filtra personal en servicio (constituye o reemplazo).
     */
    public function filterOnDutyStaff(Collection $activeStaff): Collection
    {
        return $activeStaff->filter(function ($u) {
            return in_array($u->estado_asistencia, ['constituye', 'reemplazo'], true);
        });
    }

    /**
     * Calcula el mensaje y variante del badge de asistencia.
     */
    public function getAttendanceStatus(bool $hasAttendanceSavedToday, bool $attendanceIsStale): array
    {
        $attendanceEnabled = $this->isAttendanceEnabled();
        $shiftClosed = $this->isShiftClosedForToday();
        $isAfter2200 = $this->localNow->hour >= 22;

        if (!$attendanceEnabled) {
            return [
                'message' => 'FUERA DE HORARIO DE REGISTRO',
                'variant' => 'default',
            ];
        }
        
        if ($hasAttendanceSavedToday && $attendanceIsStale) {
            return [
                'message' => 'ASISTENCIA DESACTUALIZADA',
                'variant' => 'warning',
            ];
        }
        
        if ($hasAttendanceSavedToday) {
            return [
                'message' => 'ASISTENCIA REGISTRADA',
                'variant' => 'success',
            ];
        }
        
        if ($shiftClosed) {
            return [
                'message' => 'RECORDAR REGISTRO 22:00',
                'variant' => 'warning',
            ];
        }

        return [
            'message' => $isAfter2200 ? 'GUARDAR ANTES DE IRTE' : 'SIN REGISTRAR',
            'variant' => 'danger',
        ];
    }

    /**
     * Obtiene las clases CSS para el botón de asistencia.
     */
    public function getAttendanceButtonClasses(): array
    {
        $enabled = $this->isAttendanceEnabled();
        
        return [
            'disabled' => !$enabled,
            'buttonClass' => $enabled 
                ? 'bg-slate-800 hover:bg-slate-700 text-white border-slate-700' 
                : 'bg-slate-800/50 text-slate-500 border-slate-800 cursor-not-allowed',
            'iconClass' => $enabled ? 'text-emerald-400' : 'text-slate-500',
        ];
    }

    /**
     * Calcula los años de servicio de un bombero.
     */
    public static function calculateServiceLabel($fechaIngreso): string
    {
        if (!$fechaIngreso) {
            return '—';
        }
        
        $ingreso = Carbon::parse($fechaIngreso);
        $diff = $ingreso->diff(now());
        $years = (int) $diff->y;
        $months = (int) $diff->m;
        
        $yearsLabel = $years . ' ' . ($years === 1 ? 'año' : 'años');
        $monthsLabel = $months . ' m';
        
        return trim($yearsLabel . ' ' . $monthsLabel);
    }

    /**
     * Obtiene los tiempos de habilitación de asistencia.
     */
    public function getAttendanceTimeWindow(): array
    {
        return [
            'enable' => SystemSetting::getValue('attendance_enable_time', '22:00'),
            'disable' => SystemSetting::getValue('attendance_disable_time', '07:00'),
        ];
    }

    /**
     * Verifica si el usuario puede inhabilitar personal.
     */
    public static function canInhabilitar(): bool
    {
        return in_array(Auth::user()->role, ['super_admin', 'capitania', 'guardia'], true);
    }

    /**
     * Obtiene el nombre del mes actual en español.
     */
    public static function getCurrentMonthName(): string
    {
        return mb_strtoupper(Carbon::now()->locale('es')->translatedFormat('F'), 'UTF-8');
    }

    /**
     * Prepara todos los datos para la vista.
     */
    public function prepareViewData(
        $myGuardia,
        Collection $myStaff,
        ?Collection $replacementByOriginal,
        ?Collection $replacementByReplacement,
        ?Collection $outOfServiceStaff,
        array $bedByFirefighter,
        bool $hasAttendanceSavedToday,
        bool $attendanceIsStale,
        bool $isMyGuardiaOnDuty,
        $birthdaysThisMonth,
        $guardiaNovelties,
        $academies,
        int $availableBeds,
        int $totalBeds
    ): array {
        $activeStaff = $this->filterActiveStaff($myStaff, $replacementByOriginal);
        $activeStaff = $this->sortActiveStaff($activeStaff, $replacementByReplacement);
        $onDutyStaff = $this->filterOnDutyStaff($activeStaff);
        
        $attendanceStatus = $this->getAttendanceStatus($hasAttendanceSavedToday, $attendanceIsStale);
        $buttonClasses = $this->getAttendanceButtonClasses();
        $timeWindow = $this->getAttendanceTimeWindow();

        // Agregar service_label a cada staff
        foreach ($activeStaff as $staff) {
            $staff->service_label = self::calculateServiceLabel($staff->fecha_ingreso);
        }

        return [
            // Datos principales
            'myGuardia' => $myGuardia,
            'activeStaff' => $activeStaff,
            'outOfServiceStaff' => $outOfServiceStaff ?? collect(),
            'replacementByReplacement' => $replacementByReplacement ?? collect(),
            'replacementByOriginal' => $replacementByOriginal ?? collect(),
            'bedByFirefighter' => $bedByFirefighter,
            
            // Conteos
            'visibleStaffCount' => $activeStaff->count(),
            'presentStaffCount' => $onDutyStaff->count(),
            
            // Estado de asistencia
            'attendanceEnabled' => $this->isAttendanceEnabled(),
            'attendanceMessage' => $attendanceStatus['message'],
            'attendanceVariant' => $attendanceStatus['variant'],
            'attendanceButtonDisabled' => $buttonClasses['disabled'],
            'attendanceButtonClass' => $buttonClasses['buttonClass'],
            'attendanceIconClass' => $buttonClasses['iconClass'],
            'attendanceEnableTime' => $timeWindow['enable'],
            'attendanceDisableTime' => $timeWindow['disable'],
            
            // Estado de guardia
            'isMyGuardiaOnDuty' => $isMyGuardiaOnDuty,
            
            // Permisos
            'canInhabilitar' => self::canInhabilitar(),
            
            // Widgets
            'birthdaysList' => $birthdaysThisMonth ?? collect(),
            'currentMonthName' => self::getCurrentMonthName(),
            'noveltiesList' => $guardiaNovelties ?? collect(),
            'noveltiesPaginator' => $guardiaNovelties,
            'academiesList' => $academies ?? collect(),
            'availableBeds' => $availableBeds,
            'totalBeds' => $totalBeds,
        ];
    }
}
