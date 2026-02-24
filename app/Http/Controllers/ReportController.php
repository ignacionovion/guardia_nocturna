<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exports\ReplacementsReportExport;
use App\Models\Guardia;
use App\Models\GuardiaCalendarDay;
use App\Models\StaffEvent;
use App\Models\ShiftUser;
use App\Models\Bombero;
use App\Models\ReemplazoBombero;
use App\Models\PreventiveEvent;
use App\Models\PreventiveShift;
use App\Models\PreventiveShiftAssignment;
use App\Models\PreventiveShiftAttendance;
use App\Models\Emergency;
use App\Services\ReplacementService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    private function resolveShiftDay(Carbon $dateTime): Carbon
    {
        $scheduleTz = env('GUARDIA_SCHEDULE_TZ', 'America/Santiago');
        $local = $dateTime->copy()->setTimezone($scheduleTz);

        // El "día" del reporte corresponde al turno nocturno.
        // Ventana: domingo 22:00 -> 07:00, resto de días 23:00 -> 07:00.
        // Para asignar el día de turno:
        // - Si la hora es < 07:00 => pertenece al turno del día anterior.
        // - Si la hora es >= 07:00 y < hora de inicio del turno (22/23) => pertenece al día anterior.
        // - Si la hora es >= hora de inicio del turno => pertenece al mismo día.

        $day = $local->copy()->startOfDay();
        $scheduleHour = $local->isSunday() ? 22 : 23;
        $hour = (int) $local->hour;

        if ($hour < 7) {
            $day->subDay();
        } elseif ($hour < $scheduleHour) {
            $day->subDay();
        }

        return $day;
    }

    private function resolveActiveGuardia($now)
    {
        $weekStart = $now->copy()->startOfWeek(Carbon::SUNDAY);

        $calendarDay = GuardiaCalendarDay::with('guardia')
            ->where('date', $weekStart->toDateString())
            ->first();

        if (!$calendarDay) {
            $calendarDay = GuardiaCalendarDay::with('guardia')
                ->where('date', $now->toDateString())
                ->first();
        }

        if ($calendarDay && $calendarDay->guardia) {
            return $calendarDay->guardia;
        }

        return Guardia::where('is_active_week', true)->first();
    }

    public function preventivas(Request $request)
    {
        $user = auth()->user();
        if ($user->role !== 'super_admin') {
            abort(403, 'No autorizado.');
        }

        $fromStr = (string) $request->input('from', now()->subDays(14)->toDateString());
        $toStr = (string) $request->input('to', now()->toDateString());
        $eventId = $request->input('event_id');

        try {
            $from = Carbon::parse($fromStr)->startOfDay();
        } catch (\Throwable $e) {
            $from = now()->subDays(14)->startOfDay();
        }
        try {
            $to = Carbon::parse($toStr)->endOfDay();
        } catch (\Throwable $e) {
            $to = now()->endOfDay();
        }

        $events = PreventiveEvent::query()->latest()->get(['id', 'title', 'start_date', 'end_date', 'status']);

        $shiftsQ = PreventiveShift::query()
            ->with(['event'])
            ->whereBetween('shift_date', [$from->toDateString(), $to->toDateString()])
            ->when($eventId, fn ($q) => $q->where('preventive_event_id', (int) $eventId))
            ->orderBy('shift_date')
            ->orderBy('sort_order');

        $shifts = $shiftsQ->get();
        $shiftIds = $shifts->pluck('id')->values();

        $assignments = $shiftIds->isEmpty()
            ? collect()
            : PreventiveShiftAssignment::query()
                ->whereIn('preventive_shift_id', $shiftIds)
                ->with(['shift.event', 'firefighter', 'attendance'])
                ->get();

        $totalAssignments = $assignments->count();
        $presentAssignments = $assignments->filter(fn ($a) => (bool) $a->attendance)->count();
        $pendingAssignments = $totalAssignments - $presentAssignments;

        $byEvent = $assignments
            ->groupBy(fn ($a) => $a->shift?->event?->title ?? '—')
            ->map(function ($items, $title) {
                $total = $items->count();
                $present = $items->filter(fn ($a) => (bool) $a->attendance)->count();
                return [
                    'event' => $title,
                    'total' => $total,
                    'present' => $present,
                    'pending' => $total - $present,
                ];
            })
            ->values()
            ->sortByDesc('total')
            ->values();

        $rows = $assignments
            ->sortBy(function ($a) {
                $d = $a->shift?->shift_date?->toDateString() ?? '';
                $o = (int) ($a->shift?->sort_order ?? 0);
                $ln = (string) ($a->firefighter?->apellido_paterno ?? '');
                return sprintf('%s-%02d-%s', $d, $o, $ln);
            })
            ->values();

        $kpis = [
            'total_assignments' => (int) $totalAssignments,
            'present' => (int) $presentAssignments,
            'pending' => (int) $pendingAssignments,
            'range_label' => $from->format('d-m-Y') . ' → ' . $to->format('d-m-Y'),
        ];

        return view('admin.reports.preventivas', [
            'events' => $events,
            'eventId' => $eventId,
            'from' => $from,
            'to' => $to,
            'kpis' => $kpis,
            'byEvent' => $byEvent,
            'rows' => $rows,
        ]);
    }

    public function index(Request $request)
    {
        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);

        $monthLabels = collect(range(1, 12))
            ->map(fn ($m) => ucfirst(Carbon::create()->month($m)->locale('es')->monthName))
            ->values();

        $hasShiftUsersGuardiaId = Schema::hasColumn('shift_users', 'guardia_id');
        $hasShiftUsersAttendanceStatus = Schema::hasColumn('shift_users', 'attendance_status');
        $hasShiftUsersFirefighterId = Schema::hasColumn('shift_users', 'firefighter_id');

        $shiftUsersForYear = ShiftUser::whereYear('start_time', $year)
            ->whereNotNull('start_time')
            ->with(['firefighter', 'user'])
            ->get();

        $resolvedForYear = $shiftUsersForYear
            ->map(function ($r) use ($hasShiftUsersGuardiaId, $hasShiftUsersAttendanceStatus) {
                if (!$r->start_time) {
                    return null;
                }

                $r->shift_day = $this->resolveShiftDay($r->start_time);

                $guardiaId = $hasShiftUsersGuardiaId ? $r->guardia_id : null;
                if (!$guardiaId) {
                    $guardiaId = $r->firefighter?->guardia_id;
                }
                if (!$guardiaId) {
                    $guardiaId = $r->user?->guardia_id;
                }
                $r->resolved_guardia_id = $guardiaId ? (int) $guardiaId : null;

                $status = $hasShiftUsersAttendanceStatus ? ($r->attendance_status ?: 'sin_dato') : null;
                $r->resolved_status = $status;

                return $r;
            })
            ->filter()
            ->values();

        $presentShiftsByMonth = array_fill(1, 12, 0);
        foreach ($resolvedForYear as $r) {
            $isPresent = $hasShiftUsersAttendanceStatus
                ? in_array($r->attendance_status, ['constituye', 'reemplazo'], true)
                : (bool) $r->present;
            if (!$isPresent) {
                continue;
            }
            $m = (int) $r->shift_day->month;
            $presentShiftsByMonth[$m] += 1;
        }

        $statusKeys = ['constituye', 'reemplazo', 'permiso', 'ausente', 'licencia', 'falta', 'sin_dato'];
        $statusCountsByMonth = [];
        foreach ($statusKeys as $k) {
            $statusCountsByMonth[$k] = array_fill(1, 12, 0);
        }

        if ($hasShiftUsersAttendanceStatus) {
            foreach ($resolvedForYear as $r) {
                $status = $r->resolved_status ?: 'sin_dato';
                if (!in_array($status, $statusKeys, true)) {
                    $status = 'sin_dato';
                }
                $m = (int) $r->shift_day->month;
                $statusCountsByMonth[$status][$m] += 1;
            }
        }

        $presentCountByMonthGuardiaDay = [];
        foreach ($resolvedForYear as $r) {
            $shiftDay = $r->shift_day;

            $guardiaId = $r->resolved_guardia_id;
            if (!$guardiaId) {
                continue;
            }

            $isPresent = $hasShiftUsersAttendanceStatus
                ? in_array($r->attendance_status, ['constituye', 'reemplazo'], true)
                : (bool) $r->present;

            if (!$isPresent) {
                continue;
            }

            $m = (int) $shiftDay->month;
            $day = $shiftDay->toDateString();
            $key = $guardiaId . '|' . $day;

            if (!isset($presentCountByMonthGuardiaDay[$m])) {
                $presentCountByMonthGuardiaDay[$m] = [];
            }
            $presentCountByMonthGuardiaDay[$m][$key] = ($presentCountByMonthGuardiaDay[$m][$key] ?? 0) + 1;
        }

        $avgFirefightersPerGuardiaDayByMonth = array_fill(1, 12, 0.0);
        foreach (range(1, 12) as $m) {
            $map = $presentCountByMonthGuardiaDay[$m] ?? [];
            if (count($map) === 0) {
                $avgFirefightersPerGuardiaDayByMonth[$m] = 0.0;
                continue;
            }
            $total = array_sum($map);
            $avgFirefightersPerGuardiaDayByMonth[$m] = $total / count($map);
        }

        $selectedMonthPresentShifts = (int) ($presentShiftsByMonth[$month] ?? 0);

        $resolvedForMonth = $resolvedForYear
            ->filter(fn ($r) => (int) $r->shift_day->month === (int) $month)
            ->values();

        $dailyStatusCounts = $resolvedForMonth
            ->groupBy(fn ($r) => $r->shift_day->toDateString())
            ->map(function ($items) use ($hasShiftUsersAttendanceStatus, $statusKeys) {
                $base = [];
                foreach ($statusKeys as $k) {
                    $base[$k] = 0;
                }
                foreach ($items as $r) {
                    $status = $hasShiftUsersAttendanceStatus ? ($r->resolved_status ?: 'sin_dato') : 'sin_dato';
                    if (!in_array($status, $statusKeys, true)) {
                        $status = 'sin_dato';
                    }
                    $base[$status] += 1;
                }
                return $base;
            })
            ->sortKeys();

        $guardiaNamesById = Guardia::query()->pluck('name', 'id')->map(fn ($v) => (string) $v)->all();

        $guardiaStatusCounts = $resolvedForMonth
            ->filter(fn ($r) => (bool) ($r->resolved_guardia_id ?? null))
            ->groupBy(fn ($r) => (int) $r->resolved_guardia_id)
            ->map(function ($items) use ($hasShiftUsersAttendanceStatus, $statusKeys, $guardiaNamesById) {
                $base = [];
                foreach ($statusKeys as $k) {
                    $base[$k] = 0;
                }
                foreach ($items as $r) {
                    $status = $hasShiftUsersAttendanceStatus ? ($r->resolved_status ?: 'sin_dato') : 'sin_dato';
                    if (!in_array($status, $statusKeys, true)) {
                        $status = 'sin_dato';
                    }
                    $base[$status] += 1;
                }

                $guardiaId = (int) ($items->first()->resolved_guardia_id ?? 0);
                return [
                    'guardia_id' => $guardiaId,
                    'guardia_name' => $guardiaNamesById[$guardiaId] ?? ('Guardia #' . $guardiaId),
                    'counts' => $base,
                ];
            })
            ->sortBy(fn ($row) => $row['guardia_name'] ?? '')
            ->values();

        $disabledCountsByMonth = array_fill(1, 12, 0);
        $disabledEventsForYear = StaffEvent::query()
            ->where('type', 'service_status')
            ->where('status', 'approved')
            ->where('description', 'inhabilitado')
            ->whereYear('start_date', $year)
            ->get(['start_date']);

        foreach ($disabledEventsForYear as $e) {
            if (!$e->start_date) {
                continue;
            }
            $disabledCountsByMonth[(int) $e->start_date->month] += 1;
        }

        $selectedMonthDisabled = (int) ($disabledCountsByMonth[$month] ?? 0);

        $selectedMonthKpis = [
            'constituye' => (int) ($statusCountsByMonth['constituye'][$month] ?? 0),
            'reemplazo' => (int) ($statusCountsByMonth['reemplazo'][$month] ?? 0),
            'permiso' => (int) ($statusCountsByMonth['permiso'][$month] ?? 0),
            'ausente' => (int) ($statusCountsByMonth['ausente'][$month] ?? 0),
            'licencia' => (int) ($statusCountsByMonth['licencia'][$month] ?? 0),
            'present_shifts' => (int) $selectedMonthPresentShifts,
            'disabled' => (int) $selectedMonthDisabled,
        ];

        $charts = [
            'labels' => $monthLabels,
            'avg_firefighters_per_guardia_day' => collect($avgFirefightersPerGuardiaDayByMonth)->values(),
            'present_shifts' => collect($presentShiftsByMonth)->values(),
            'status_counts' => collect($statusCountsByMonth)
                ->map(fn ($arr) => collect($arr)->values())
                ->all(),
            'disabled_counts' => collect($disabledCountsByMonth)->values(),
        ];

        // 1. Guardias con su dotación (Bomberos)
        $guardias = Guardia::with(['bomberos' => function ($query) {
            $query->orderBy('apellido_paterno')->orderBy('nombres');
        }])->get();

        // 2. Asistencias cerradas del año agrupadas por bombero (o por user legacy)
        $attendanceKey = $hasShiftUsersFirefighterId ? 'firefighter_id' : 'user_id';
        $allAttendances = ShiftUser::whereYear('start_time', $year)
            ->whereNotNull('start_time')
            ->get()
            ->groupBy($attendanceKey);

        // 3. Procesar datos por bombero
        foreach ($guardias as $guardia) {
            foreach ($guardia->bomberos as $user) {
                $userRecords = $allAttendances->get($user->id, collect());

                $userRecords = $userRecords
                    ->filter(fn ($r) => (bool) ($r->start_time ?? null))
                    ->map(function ($r) use ($hasShiftUsersAttendanceStatus) {
                        $r->shift_day = $this->resolveShiftDay($r->start_time);
                        $r->resolved_present = $hasShiftUsersAttendanceStatus
                            ? in_array($r->attendance_status, ['constituye', 'reemplazo'], true)
                            : (bool) $r->present;
                        return $r;
                    })
                    ->filter(fn ($r) => (bool) ($r->resolved_present ?? false));

                // --- ANUAL ---
                $user->year_days = $userRecords->map(fn ($r) => $r->shift_day->format('Y-m-d'))->unique()->count();
                $user->year_shifts = $userRecords->count();

                // --- MENSUAL ---
                $monthRecords = $userRecords->filter(fn ($r) => (int) $r->shift_day->month === (int) $month);
                $user->month_days = $monthRecords->map(fn ($r) => $r->shift_day->format('Y-m-d'))->unique()->count();
                $user->month_shifts = $monthRecords->count();

                $user->weekly_stats = $monthRecords->groupBy(function ($r) {
                    return $r->shift_day->weekOfYear;
                })->map(function ($weekRecords) {
                    return [
                        'days' => $weekRecords->map(fn ($r) => $r->shift_day->format('Y-m-d'))->unique()->count(),
                        'shifts' => $weekRecords->count(),
                    ];
                })->sortKeys();
            }
        }

        // Determinar las semanas que tiene este mes para las columnas de la tabla
        $weeksInMonth = [];
        $startOfMonth = Carbon::createFromDate($year, $month, 1);
        $endOfMonth = $startOfMonth->copy()->endOfMonth();
        
        $currentDate = $startOfMonth->copy();
        while ($currentDate <= $endOfMonth) {
            $weekNum = $currentDate->weekOfYear;
            if (!in_array($weekNum, $weeksInMonth)) {
                $weeksInMonth[] = $weekNum;
            }
            $currentDate->addDay();
        }

        return view('admin.reports.index', compact(
            'guardias',
            'month',
            'year',
            'weeksInMonth',
            'charts',
            'selectedMonthKpis',
            'dailyStatusCounts',
            'guardiaStatusCounts',
            'selectedMonthDisabled'
        ));
    }

    /**
     * Reporte de Asistencia - Dashboard con estadísticas por guardia y general
     */
    public function attendance(Request $request)
    {
        $user = auth()->user();
        if ($user->role !== 'super_admin') {
            abort(403, 'No autorizado.');
        }

        $guardiaId   = $request->input('guardia_id');
        $currentView = $request->input('view', 'guardias');

        try {
            $from = $request->input('from') ? Carbon::parse($request->input('from'))->startOfDay() : now()->subDays(30)->startOfDay();
        } catch (\Exception $e) {
            $from = now()->subDays(30)->startOfDay();
        }
        try {
            $to = $request->input('to') ? Carbon::parse($request->input('to'))->endOfDay() : now()->endOfDay();
        } catch (\Exception $e) {
            $to = now()->endOfDay();
        }

        if ($currentView === 'general') {
            $guardiaId = null;
        }

        $guardias      = Guardia::orderBy('name')->get();
        $activeGuardia = $guardiaId ? Guardia::find($guardiaId) : $this->resolveActiveGuardia(now());
        $filterGid     = $currentView === 'general' ? null : $activeGuardia?->id;

        // Últimas 8 semanas reales (Dom 22:00 → Dom 07:00)
        $weeklyStats = [];
        for ($i = 7; $i >= 0; $i--) {
            $wStart = now()->subWeeks($i)->startOfWeek(Carbon::SUNDAY)->setTime(22, 0);
            $wEnd   = $wStart->copy()->addDays(7)->setTime(7, 0);
            $wData  = $this->calculateWeekStats($wStart, $wEnd, $filterGid);
            $weeklyStats[] = [
                'week'       => 'S' . $wStart->format('W'),
                'year'       => $wStart->year,
                'percentage' => $wData['percentage'],
                'shifts'     => $wData['total_shifts'],
                'active'     => $i === 0,
            ];
        }

        $generalStats     = $this->calculateGeneralStats($from, $to, $filterGid);
        $firefighterStats = collect($this->calculateFirefighterStats($from, $to, $filterGid));
        $rankings         = $this->calculateRankings($firefighterStats);
        $dailyHistory     = $this->calculateDailyHistory($from, $to, $filterGid);

        $guardiaComparison = [];
        if ($currentView === 'general') {
            foreach ($guardias as $g) {
                $gs = $this->calculateGeneralStats($from, $to, $g->id);
                if ($gs['total_personnel'] > 0 || $gs['fulfilled'] > 0) {
                    $guardiaComparison[] = [
                        'name'       => $g->name,
                        'percentage' => $gs['percentage'],
                        'fulfilled'  => $gs['fulfilled'],
                        'total'      => $gs['fulfilled'] + $gs['absences'] + $gs['permissions'] + $gs['licenses'],
                        'personnel'  => $gs['total_personnel'],
                    ];
                }
            }
            usort($guardiaComparison, fn ($a, $b) => $b['percentage'] <=> $a['percentage']);
        }

        return view('admin.reports.attendance', [
            'guardias'          => $guardias,
            'guardiaId'         => $guardiaId,
            'activeGuardia'     => $activeGuardia,
            'currentView'       => $currentView,
            'from'              => $from,
            'to'                => $to,
            'stats'             => [
                'fulfilled'      => $generalStats['fulfilled'] ?? 0,
                'absences'       => $generalStats['absences'] ?? 0,
                'permissions'    => $generalStats['permissions'] ?? 0,
                'licenses'       => $generalStats['licenses'] ?? 0,
                'disabled'       => $generalStats['disabled'] ?? 0,
                'replacements'   => $generalStats['replacements'] ?? 0,
                'reinforcements' => $generalStats['reinforcements'] ?? 0,
            ],
            'generalPercentage' => $generalStats['percentage'] ?? 0,
            'totalPersonnel'    => $generalStats['total_personnel'] ?? 0,
            'stabilityIndex'    => $generalStats['stability_index'] ?? 0,
            'guardiaStats'      => $generalStats['by_status'] ?? [],
            'weeklyStats'       => $weeklyStats,
            'firefighterStats'  => $firefighterStats,
            'rankings'          => $rankings,
            'dailyHistory'      => $dailyHistory,
            'guardiaComparison' => $guardiaComparison,
        ]);
    }
    private function calculateWeekStats(Carbon $weekStart, Carbon $weekEnd, ?int $guardiaId): array
    {
        $query = ShiftUser::query()
            ->whereBetween('start_time', [$weekStart, $weekEnd])
            ->when($guardiaId, fn ($q) => $q->whereHas('firefighter', fn ($q2) => $q2->where('guardia_id', $guardiaId)));

        $total = $query->count();
        if ($total === 0) {
            return ['percentage' => 0, 'total_shifts' => 0];
        }

        $fulfilled = (clone $query)->whereIn('attendance_status', ['constituye', 'reemplazo'])->count();

        return [
            'percentage'   => round(($fulfilled / $total) * 100),
            'total_shifts' => $total,
        ];
    }

    private function calculateGeneralStats(Carbon $from, Carbon $to, ?int $guardiaId): array
    {
        $empty = [
            'fulfilled' => 0, 'absences' => 0, 'permissions' => 0, 'licenses' => 0,
            'replacements' => 0, 'reinforcements' => 0, 'disabled' => 0,
            'percentage' => 0, 'total_personnel' => 0, 'stability_index' => 0, 'by_status' => [],
        ];

        $query = ShiftUser::query()
            ->whereBetween('start_time', [$from, $to])
            ->when($guardiaId, fn ($q) => $q->whereHas('firefighter', fn ($q2) => $q2->where('guardia_id', $guardiaId)));

        $total = $query->count();
        if ($total === 0) {
            return $empty;
        }

        $fulfilled    = (clone $query)->whereIn('attendance_status', ['constituye', 'reemplazo'])->count();
        $absences     = (clone $query)->where('attendance_status', 'ausente')->count();
        $permissions  = (clone $query)->where('attendance_status', 'permiso')->count();
        $licenses     = (clone $query)->where('attendance_status', 'licencia')->count();
        $replacements = (clone $query)->where('attendance_status', 'reemplazo')->count();
        $disabled     = (clone $query)->where('attendance_status', 'inhabilitado')->count();

        $reinforcements = (clone $query)->where(function ($q) {
            $q->where('assignment_type', 'refuerzo')
              ->orWhereHas('firefighter', fn ($q2) => $q2->where('es_refuerzo', true));
        })->count();

        $byStatus = [
            ['label' => 'Cumplidos',    'value' => round(($fulfilled   / $total) * 100, 1), 'color' => 'emerald', 'count' => $fulfilled],
            ['label' => 'Ausencias',    'value' => round(($absences    / $total) * 100, 1), 'color' => 'rose',    'count' => $absences],
            ['label' => 'Permisos',     'value' => round(($permissions / $total) * 100, 1), 'color' => 'amber',   'count' => $permissions],
            ['label' => 'Licencias',    'value' => round(($licenses    / $total) * 100, 1), 'color' => 'blue',    'count' => $licenses],
            ['label' => 'Inhabilitados','value' => round(($disabled    / $total) * 100, 1), 'color' => 'slate',   'count' => $disabled],
        ];

        $totalPersonnel = Bombero::query()
            ->when($guardiaId, fn ($q) => $q->where('guardia_id', $guardiaId))
            ->where(fn ($q) => $q->whereNull('fuera_de_servicio')->orWhere('fuera_de_servicio', false))
            ->count();

        $titularFulfilled = $fulfilled - $replacements;
        $stabilityIndex   = $fulfilled > 0
            ? max(0, round(($titularFulfilled / $fulfilled) * 100))
            : 0;

        return [
            'fulfilled'       => $fulfilled,
            'absences'        => $absences,
            'permissions'     => $permissions,
            'licenses'        => $licenses,
            'replacements'    => $replacements,
            'reinforcements'  => $reinforcements,
            'disabled'        => $disabled,
            'percentage'      => round(($fulfilled / $total) * 100),
            'total_personnel' => $totalPersonnel,
            'stability_index' => $stabilityIndex,
            'by_status'       => $byStatus,
        ];
    }

    private function calculateFirefighterStats(Carbon $from, Carbon $to, ?int $guardiaId): array
    {
        // Obtener bomberos con sus flags de tipo
        $firefighters = Bombero::query()
            ->when($guardiaId, fn ($q) => $q->where('guardia_id', $guardiaId))
            ->with([
                'guardia',
                'shiftUsers' => fn ($q) => $q->whereBetween('start_time', [$from, $to]),
            ])
            ->get();

        // Reemplazos hechos: turnos donde este bombero actuó como reemplazante
        // Ahora buscamos por attendance_status = 'reemplazo' O por assignment_type = 'reemplazo'
        $replacementsMadeMap = ShiftUser::query()
            ->whereBetween('start_time', [$from, $to])
            ->where(function($q) {
                $q->where('attendance_status', 'reemplazo')
                  ->orWhere('assignment_type', 'reemplazo')
                  ->orWhereHas('firefighter', fn($q2) => $q2->where('estado_asistencia', 'reemplazo'));
            })
            ->whereNotNull('firefighter_id')
            ->selectRaw('firefighter_id, COUNT(*) as cnt')
            ->groupBy('firefighter_id')
            ->pluck('cnt', 'firefighter_id');

        // También buscar reemplazos legacy en reemplazos_bomberos
        $legacyReplacementsMadeMap = collect([]);
        if (Schema::hasTable('reemplazos_bomberos')) {
            $legacyReplacementsMadeMap = \App\Models\ReemplazoBombero::query()
                ->whereBetween('inicio', [$from, $to])
                ->whereNotNull('bombero_reemplazante_id')
                ->selectRaw('bombero_reemplazante_id as firefighter_id, COUNT(*) as cnt')
                ->groupBy('bombero_reemplazante_id')
                ->pluck('cnt', 'firefighter_id');
        }

        // Reemplazos recibidos: turnos donde este bombero fue reemplazado
        $replacementsReceivedMap = ShiftUser::query()
            ->whereBetween('start_time', [$from, $to])
            ->whereNotNull('replaced_firefighter_id')
            ->selectRaw('replaced_firefighter_id, COUNT(*) as cnt')
            ->groupBy('replaced_firefighter_id')
            ->pluck('cnt', 'replaced_firefighter_id');

        // También buscar reemplazos recibidos legacy en reemplazos_bomberos
        $legacyReplacementsReceivedMap = collect([]);
        if (Schema::hasTable('reemplazos_bomberos')) {
            $legacyReplacementsReceivedMap = \App\Models\ReemplazoBombero::query()
                ->whereBetween('inicio', [$from, $to])
                ->whereNotNull('bombero_titular_id')
                ->selectRaw('bombero_titular_id as firefighter_id, COUNT(*) as cnt')
                ->groupBy('bombero_titular_id')
                ->pluck('cnt', 'firefighter_id');
        }

        // Refuerzos: turnos con assignment_type = 'refuerzo' o es_refuerzo = true
        $reinforcementsMap = ShiftUser::query()
            ->whereBetween('start_time', [$from, $to])
            ->where(function($q) {
                $q->where('assignment_type', 'refuerzo')
                  ->orWhereHas('firefighter', fn($q2) => $q2->where('es_refuerzo', true));
            })
            ->whereNotNull('firefighter_id')
            ->selectRaw('firefighter_id, COUNT(*) as cnt')
            ->groupBy('firefighter_id')
            ->pluck('cnt', 'firefighter_id');

        $stats = [];
        foreach ($firefighters as $ff) {
            $shiftUsers = $ff->shiftUsers;
            $total      = $shiftUsers->count();
            if ($total === 0) continue;

            $fulfilled   = $shiftUsers->whereIn('attendance_status', ['constituye', 'reemplazo'])->count();
            $absences    = $shiftUsers->where('attendance_status', 'ausente')->count();
            $permissions = $shiftUsers->where('attendance_status', 'permiso')->count();
            $licenses    = $shiftUsers->where('attendance_status', 'licencia')->count();
            $disabled    = $shiftUsers->where('attendance_status', 'inhabilitado')->count();

            // Determinar tipo de bombero basado en flags
            $isTitular = $ff->es_titular ?? false;
            $isReemplazo = ($ff->estado_asistencia === 'reemplazo') || 
                           ($replacementsMadeMap[$ff->id] ?? 0) > 0 ||
                           ($replacementsReceivedMap[$ff->id] ?? 0) > 0 ||
                           ($legacyReplacementsMadeMap[$ff->id] ?? 0) > 0 ||
                           ($legacyReplacementsReceivedMap[$ff->id] ?? 0) > 0;
            $isRefuerzo = $ff->es_refuerzo ?? false;

            $stats[] = [
                'id'                    => $ff->id,
                'code'                  => strtoupper(substr($ff->nombres ?? '', 0, 1) . substr($ff->apellido_paterno ?? '', 0, 1)),
                'name'                  => strtoupper(trim(($ff->nombres ?? '') . ' ' . ($ff->apellido_paterno ?? ''))),
                'guardia_name'          => $ff->guardia?->name ?? '',
                'shift'                 => $total,
                'fulfilled'             => $fulfilled,
                'absences'              => $absences,
                'permissions'           => $permissions,
                'licenses'              => $licenses,
                'disabled'              => $disabled,
                'replacements_made'     => (int) (($replacementsMadeMap[$ff->id] ?? 0) + ($legacyReplacementsMadeMap[$ff->id] ?? 0)),
                'replacements_received' => (int) (($replacementsReceivedMap[$ff->id] ?? 0) + ($legacyReplacementsReceivedMap[$ff->id] ?? 0)),
                'reinforcements'        => (int) ($reinforcementsMap[$ff->id] ?? 0),
                'percentage'            => round(($fulfilled / $total) * 100),
                'is_titular'            => $isTitular,
                'is_reemplazo'          => $isReemplazo,
                'is_refuerzo'           => $isRefuerzo,
                'tipo'                  => $isRefuerzo ? 'refuerzo' : ($isReemplazo ? 'reemplazo' : 'titular'),
            ];
        }

        return collect($stats)->sortByDesc('percentage')->values()->all();
    }

    private function calculateRankings(\Illuminate\Support\Collection $firefighterStats): array
    {
        // Si no hay datos, retornar array vacío
        if ($firefighterStats->isEmpty()) {
            return [];
        }

        $rankings = [];

        // 1. Más cumplidor (mejor porcentaje de asistencia)
        $topCumplidor = $firefighterStats->sortByDesc('percentage')->first();
        $rankings[] = [
            'emoji' => '🏆',
            'label' => 'Más cumplidor',
            'name'  => $topCumplidor['name'] ?? '—',
            'value' => ($topCumplidor['percentage'] ?? 0) . '%',
            'unit'  => 'asistencia',
            'color' => 'emerald',
        ];

        // 2. Más reemplazos hechos
        $topReemplazante = $firefighterStats->sortByDesc('replacements_made')->first();
        $val = $topReemplazante['replacements_made'] ?? 0;
        $rankings[] = [
            'emoji' => '�',
            'label' => 'Más reemplazos',
            'name'  => $val > 0 ? ($topReemplazante['name'] ?? '—') : 'Sin reemplazos',
            'value' => $val,
            'unit'  => 'reemplazos',
            'color' => 'purple',
        ];

        // 3. Más ausencias
        $topAusente = $firefighterStats->sortByDesc('absences')->first();
        $val = $topAusente['absences'] ?? 0;
        $rankings[] = [
            'emoji' => '⚠️',
            'label' => 'Más ausencias',
            'name'  => $val > 0 ? ($topAusente['name'] ?? '—') : 'Sin ausencias',
            'value' => $val,
            'unit'  => 'ausencias',
            'color' => 'rose',
        ];

        // 4. Más permisos
        $topPermisos = $firefighterStats->sortByDesc('permissions')->first();
        $val = $topPermisos['permissions'] ?? 0;
        $rankings[] = [
            'emoji' => '�',
            'label' => 'Más permisos',
            'name'  => $val > 0 ? ($topPermisos['name'] ?? '—') : 'Sin permisos',
            'value' => $val,
            'unit'  => 'permisos',
            'color' => 'amber',
        ];

        // 5. Mejor refuerzo
        $topRefuerzo = $firefighterStats->where('reinforcements', '>', 0)->sortByDesc('reinforcements')->first();
        $val = $topRefuerzo['reinforcements'] ?? 0;
        $rankings[] = [
            'emoji' => '💪',
            'label' => 'Mejor refuerzo',
            'name'  => $val > 0 ? ($topRefuerzo['name'] ?? '—') : 'Sin refuerzos',
            'value' => $val,
            'unit'  => 'refuerzos',
            'color' => 'sky',
        ];

        return $rankings;
    }

    private function calculateDailyHistory(Carbon $from, Carbon $to, ?int $guardiaId): array
    {
        $records = ShiftUser::query()
            ->whereBetween('start_time', [$from, $to])
            ->when($guardiaId, fn ($q) => $q->whereHas('firefighter', fn ($q2) => $q2->where('guardia_id', $guardiaId)))
            ->with(['firefighter.guardia'])
            ->orderBy('start_time')
            ->get();

        $byDay = [];
        foreach ($records as $r) {
            if (!$r->start_time) continue;
            $day = $this->resolveShiftDay($r->start_time)->toDateString();
            $byDay[$day][] = $r;
        }

        $history = [];
        foreach ($byDay as $date => $items) {
            $items       = collect($items);
            $constituyen = $items->whereIn('attendance_status', ['constituye', 'reemplazo'])->count();
            $total       = $items->count();
            $coverage    = $total > 0 ? round(($constituyen / $total) * 100) : 0;

            $absentNames = $items->where('attendance_status', 'ausente')
                ->map(fn ($r) => trim(($r->firefighter?->nombres ?? '') . ' ' . ($r->firefighter?->apellido_paterno ?? '')))
                ->filter()
                ->implode(', ');

            $replacementNames = $items->where('attendance_status', 'reemplazo')
                ->map(fn ($r) => trim(($r->firefighter?->nombres ?? '') . ' ' . ($r->firefighter?->apellido_paterno ?? '')))
                ->filter()
                ->implode(', ');

            $guardiaName = $items->first()?->firefighter?->guardia?->name ?? '—';

            $history[] = [
                'date'             => \Carbon\Carbon::parse($date)->locale('es')->isoFormat('ddd D MMM'),
                'guardia'          => $guardiaName,
                'constituyen'      => $constituyen,
                'coverage'         => $coverage,
                'absent_names'     => $absentNames,
                'replacement_names'=> $replacementNames,
            ];
        }

        return array_reverse($history);
    }

    public function drivers(Request $request)
    {
        $user = auth()->user();
        if ($user->role !== 'super_admin') {
            abort(403, 'No autorizado.');
        }

        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);

        $hasShiftUsersAttendanceStatus = Schema::hasColumn('shift_users', 'attendance_status');
        $hasShiftUsersFirefighterId = Schema::hasColumn('shift_users', 'firefighter_id');

        $drivers = Bombero::query()
            ->with('guardia')
            ->where('es_conductor', true)
            ->orderBy('apellido_paterno')
            ->orderBy('nombres')
            ->get();

        $driverIds = $drivers->pluck('id')->map(fn ($v) => (int) $v)->values()->toArray();
        $rows = [];

        if (!empty($driverIds) && $hasShiftUsersFirefighterId) {
            $shiftUsers = ShiftUser::query()
                ->whereYear('start_time', $year)
                ->whereNotNull('start_time')
                ->whereIn('firefighter_id', $driverIds)
                ->get(['firefighter_id', 'start_time', 'attendance_status', 'present']);

            $resolved = $shiftUsers
                ->map(function ($r) use ($hasShiftUsersAttendanceStatus) {
                    if (!$r->start_time) {
                        return null;
                    }
                    $r->shift_day = $this->resolveShiftDay($r->start_time);

                    $status = $hasShiftUsersAttendanceStatus ? ($r->attendance_status ?: 'sin_dato') : null;
                    $r->resolved_status = $status;

                    $r->resolved_present = $hasShiftUsersAttendanceStatus
                        ? in_array($r->attendance_status, ['constituye', 'reemplazo'], true)
                        : (bool) $r->present;

                    return $r;
                })
                ->filter()
                ->filter(fn ($r) => (int) $r->shift_day->month === (int) $month)
                ->values();

            $byDriver = $resolved->groupBy(fn ($r) => (int) $r->firefighter_id);

            foreach ($drivers as $d) {
                $items = $byDriver->get((int) $d->id, collect());
                $present = $items->filter(fn ($r) => (bool) $r->resolved_present)->count();
                $days = $items->map(fn ($r) => $r->shift_day->toDateString())->unique()->count();

                $rows[] = [
                    'firefighter_id' => (int) $d->id,
                    'name' => trim((string) ($d->nombres ?? '') . ' ' . (string) ($d->apellido_paterno ?? '')),
                    'guardia' => $d->guardia?->name ?? 'Sin Asignar',
                    'present_shifts' => (int) $present,
                    'unique_days' => (int) $days,
                ];
            }
        }

        $topDrivers = collect($rows)
            ->sortByDesc('present_shifts')
            ->values()
            ->take(20);

        return view('admin.reports.drivers', compact('month', 'year', 'topDrivers'));
    }

    public function emergencies(Request $request)
    {
        $user = auth()->user();
        if ($user->role !== 'super_admin') {
            abort(403, 'No autorizado.');
        }

        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);
        $guardiaId = $request->input('guardia_id');

        $guardias = Guardia::orderBy('name')->get();

        // Base query para emergencias
        $emergenciesQuery = Emergency::query()
            ->with(['guardia', 'emergencyKey', 'vehicle'])
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month);

        if ($guardiaId) {
            $emergenciesQuery->where('guardia_id', $guardiaId);
        }

        $emergencies = $emergenciesQuery->get();

        // Estadísticas generales
        $totalEmergencies = $emergencies->count();

        // Vehículos más utilizados (ordenado de menor a mayor)
        $vehiclesUsed = $emergencies->groupBy('vehicle_id')
            ->map(function ($items) {
                $vehicle = $items->first()?->vehicle;
                return [
                    'vehicle' => $vehicle?->name ?? 'Sin vehículo',
                    'total' => $items->count(),
                ];
            })
            ->sortBy('total') // Ordenado de menor a mayor como pidió
            ->values();

        // Claves más concurridas (top 5)
        $topKeys = $emergencies->groupBy('emergency_key_id')
            ->map(function ($items) {
                $key = $items->first()?->emergencyKey;
                return [
                    'key' => $key?->code ?? 'Sin clave',
                    'description' => $key?->description ?? '',
                    'total' => $items->count(),
                ];
            })
            ->sortByDesc('total')
            ->take(5)
            ->values();

        // Sistema de puntos por tipo de emergencia
        // 10-0-1 = 1 punto, 10-4-1 = 2 puntos, etc.
        $pointsMap = [
            '10-0-1' => 1,
            '10-4-1' => 2,
            // Puedes agregar más mapeos aquí
        ];

        $pointsByKey = $emergencies->groupBy(function ($e) {
            return $e->emergencyKey?->code ?? 'Sin clave';
        })->map(function ($items) use ($pointsMap) {
            $key = $items->first()?->emergencyKey;
            $keyCode = $key?->code ?? 'Sin clave';
            $points = $pointsMap[$keyCode] ?? 1; // Default 1 punto si no está mapeado
            return [
                'key' => $keyCode,
                'description' => $key?->description ?? '',
                'total' => $items->count(),
                'points_per_emergency' => $points,
                'total_points' => $items->count() * $points,
            ];
        })->sortByDesc('total_points')->values();

        // Horarios que más salen emergencias (agrupado por hora)
        $emergenciesByHour = $emergencies->groupBy(function ($e) {
            return $e->created_at?->format('H:00') ?? '00:00';
        })->map(function ($items, $hour) {
            return [
                'hour' => $hour,
                'total' => $items->count(),
            ];
        })->sortBy('hour')->values();

        // Estadísticas por guardia
        $statsByGuardia = $emergencies->groupBy(function ($e) {
            return $e->guardia?->name ?? 'Sin Guardia';
        })->map(function ($items, $guardiaName) {
            return [
                'guardia' => $guardiaName,
                'total' => $items->count(),
            ];
        })->sortByDesc('total')->values();

        // Estadísticas mensuales (para el año seleccionado)
        $monthlyStats = [];
        for ($m = 1; $m <= 12; $m++) {
            $monthQuery = Emergency::query()
                ->whereYear('created_at', $year)
                ->whereMonth('created_at', $m);

            if ($guardiaId) {
                $monthQuery->where('guardia_id', $guardiaId);
            }

            $monthlyStats[] = [
                'month' => $m,
                'month_name' => \Carbon\Carbon::create()->month($m)->locale('es')->monthName,
                'total' => $monthQuery->count(),
            ];
        }

        // Charts data
        $charts = [
            'by_hour' => [
                'labels' => $emergenciesByHour->pluck('hour')->values(),
                'data' => $emergenciesByHour->pluck('total')->map(fn ($v) => (int) $v)->values(),
            ],
            'by_guardia' => [
                'labels' => $statsByGuardia->pluck('guardia')->values(),
                'data' => $statsByGuardia->pluck('total')->map(fn ($v) => (int) $v)->values(),
            ],
            'monthly' => [
                'labels' => collect($monthlyStats)->pluck('month_name')->values(),
                'data' => collect($monthlyStats)->pluck('total')->map(fn ($v) => (int) $v)->values(),
            ],
            'top_keys' => [
                'labels' => $topKeys->pluck('key')->values(),
                'data' => $topKeys->pluck('total')->map(fn ($v) => (int) $v)->values(),
            ],
        ];

        $kpis = [
            'total_emergencies' => $totalEmergencies,
            'month' => $month,
            'year' => $year,
            'guardia_filter' => $guardiaId ? Guardia::find($guardiaId)?->name : 'Todas',
        ];

        return view('admin.reports.emergencies', compact(
            'month',
            'year',
            'guardiaId',
            'guardias',
            'emergencies',
            'vehiclesUsed',
            'topKeys',
            'pointsByKey',
            'emergenciesByHour',
            'statsByGuardia',
            'monthlyStats',
            'charts',
            'kpis'
        ));
    }

    public function replacements(Request $request)
    {
        ReplacementService::expire();

        $user = auth()->user();
        if ($user->role !== 'super_admin') {
            abort(403, 'No autorizado.');
        }

        $guardias = Guardia::orderBy('name')->get();
        $activeGuardia = $this->resolveActiveGuardia(now());

        [$from, $to, $guardiaId] = $this->parseReplacementsFilters($request);
        $base = $this->replacementsBaseQuery($from, $to, $guardiaId);

        $useLegacyReemplazos = Schema::hasTable('reemplazos_bomberos') && (clone $base)->count() === 0;

        if ($useLegacyReemplazos) {
            $legacyBase = ReemplazoBombero::query()
                ->when($guardiaId, fn ($q) => $q->where('guardia_id', $guardiaId))
                ->whereBetween('inicio', [$from, $to]);

            $legacyAllTime = ReemplazoBombero::query()
                ->when($guardiaId, fn ($q) => $q->where('guardia_id', $guardiaId));

            $legacyEvents = (clone $legacyBase)
                ->with(['originalFirefighter.guardia', 'replacementFirefighter', 'guardia'])
                ->orderByDesc('inicio')
                ->limit(250)
                ->get();

            $events = $legacyEvents->map(function ($r) {
                $o = new \stdClass();
                $o->start_date = $r->inicio;
                $o->end_date = $r->fin;
                $o->firefighter = $r->originalFirefighter;
                $o->replacementFirefighter = $r->replacementFirefighter;
                $o->user = null;
                $o->replacementUser = null;
                $o->status = $r->estado;
                $o->description = $r->notas;
                return $o;
            });

            $totalReplacements = (clone $legacyBase)->count();
            $totalReplacementsAllTime = (clone $legacyAllTime)->count();
            $uniqueReplacers = (clone $legacyBase)->distinct('bombero_reemplazante_id')->count('bombero_reemplazante_id');
            $uniqueReplaced = (clone $legacyBase)->distinct('bombero_titular_id')->count('bombero_titular_id');

            $replacementsByDay = (clone $legacyBase)
                ->selectRaw('DATE(inicio) as day, COUNT(*) as total')
                ->groupBy('day')
                ->orderBy('day')
                ->get();
        } else {
            $baseAllTime = StaffEvent::query()
                ->where('type', 'replacement')
                ->where('status', 'approved')
                ->when($guardiaId, function ($q) use ($guardiaId) {
                    $useFirefighterIds = Schema::hasColumn('staff_events', 'firefighter_id');
                    if ($useFirefighterIds) {
                        $q->whereIn('firefighter_id', function ($q2) use ($guardiaId) {
                            $q2->select('id')->from('bomberos')->where('guardia_id', $guardiaId);
                        });
                    } else {
                        $q->whereIn('user_id', function ($q2) use ($guardiaId) {
                            $q2->select('id')->from('users')->where('guardia_id', $guardiaId);
                        });
                    }
                });

            $useFirefighterIds = Schema::hasColumn('staff_events', 'firefighter_id');

            $events = (clone $base)
                ->with([
                    'firefighter.guardia',
                    'replacementFirefighter',
                    'user.guardia',
                    'replacementUser',
                ])
                ->orderByDesc('start_date')
                ->limit(250)
                ->get();

            $totalReplacements = (clone $base)->count();
            $totalReplacementsAllTime = (clone $baseAllTime)->count();

            $uniqueReplacers = $useFirefighterIds
                ? (clone $base)->whereNotNull('replacement_firefighter_id')->distinct('replacement_firefighter_id')->count('replacement_firefighter_id')
                : (clone $base)->whereNotNull('replacement_user_id')->distinct('replacement_user_id')->count('replacement_user_id');

            $uniqueReplaced = $useFirefighterIds
                ? (clone $base)->distinct('firefighter_id')->count('firefighter_id')
                : (clone $base)->distinct('user_id')->count('user_id');

            $replacementsByDay = DB::table('staff_events')
                ->selectRaw('DATE(start_date) as day, COUNT(*) as total')
                ->where('type', 'replacement')
                ->where('status', 'approved')
                ->whereBetween('start_date', [$from, $to])
                ->when($guardiaId, function ($q) use ($guardiaId, $useFirefighterIds) {
                    if ($useFirefighterIds) {
                        $q->whereIn('firefighter_id', function ($q2) use ($guardiaId) {
                            $q2->select('id')->from('bomberos')->where('guardia_id', $guardiaId);
                        });
                    } else {
                        $q->whereIn('user_id', function ($q2) use ($guardiaId) {
                            $q2->select('id')->from('users')->where('guardia_id', $guardiaId);
                        });
                    }
                })
                ->groupBy('day')
                ->orderBy('day')
                ->get();
        }

        $period = [];
        $cursor = $from->copy()->startOfDay();
        $endCursor = $to->copy()->startOfDay();
        while ($cursor <= $endCursor) {
            $period[$cursor->toDateString()] = 0;
            $cursor->addDay();
        }
        foreach ($replacementsByDay as $row) {
            $period[$row->day] = (int) $row->total;
        }

        if ($useLegacyReemplazos ?? false) {
            $replacementsByGuardia = ReemplazoBombero::query()
                ->with('guardia')
                ->whereBetween('inicio', [$from, $to])
                ->when($guardiaId, fn ($q) => $q->where('guardia_id', $guardiaId))
                ->get()
                ->groupBy(fn ($r) => $r->guardia?->name ?? 'Sin Asignar')
                ->map(fn ($items, $name) => (object) ['guardia_name' => $name, 'total' => $items->count()])
                ->values()
                ->sortByDesc('total')
                ->values();
        } else {
            $replacementsByGuardia = DB::table('staff_events')
                ->when($useFirefighterIds, function ($q) {
                    $q->join('bomberos as originals', 'staff_events.firefighter_id', '=', 'originals.id')
                      ->leftJoin('guardias', 'originals.guardia_id', '=', 'guardias.id');
                }, function ($q) {
                    $q->join('users as originals', 'staff_events.user_id', '=', 'originals.id')
                      ->leftJoin('guardias', 'originals.guardia_id', '=', 'guardias.id');
                })
                ->selectRaw('COALESCE(guardias.name, "Sin Asignar") as guardia_name, COUNT(*) as total')
                ->where('staff_events.type', 'replacement')
                ->where('staff_events.status', 'approved')
                ->whereBetween('staff_events.start_date', [$from, $to])
                ->when($guardiaId, fn ($q) => $q->where('originals.guardia_id', $guardiaId))
                ->groupBy('guardia_name')
                ->orderByDesc('total')
                ->get();
        }

        $peakGuardiaRow = $replacementsByGuardia->first();
        $peakGuardia = $peakGuardiaRow ? (string) $peakGuardiaRow->guardia_name : '—';
        $peakGuardiaTotal = $peakGuardiaRow ? (int) $peakGuardiaRow->total : 0;

        if ($useLegacyReemplazos ?? false) {
            $replacementsByMonth = ReemplazoBombero::query()
                ->selectRaw('DATE_FORMAT(inicio, "%Y-%m") as ym, COUNT(*) as total')
                ->whereBetween('inicio', [$from, $to])
                ->when($guardiaId, fn ($q) => $q->where('guardia_id', $guardiaId))
                ->groupBy('ym')
                ->orderBy('ym')
                ->get();
        } else {
            $replacementsByMonth = DB::table('staff_events')
                ->selectRaw('DATE_FORMAT(start_date, "%Y-%m") as ym, COUNT(*) as total')
                ->where('type', 'replacement')
                ->where('status', 'approved')
                ->whereBetween('start_date', [$from, $to])
                ->when($guardiaId, function ($q) use ($guardiaId, $useFirefighterIds) {
                    if ($useFirefighterIds) {
                        $q->whereIn('firefighter_id', function ($q2) use ($guardiaId) {
                            $q2->select('id')->from('bomberos')->where('guardia_id', $guardiaId);
                        });
                    } else {
                        $q->whereIn('user_id', function ($q2) use ($guardiaId) {
                            $q2->select('id')->from('users')->where('guardia_id', $guardiaId);
                        });
                    }
                })
                ->groupBy('ym')
                ->orderBy('ym')
                ->get();
        }

        $peakMonthRow = $replacementsByMonth->sortByDesc('total')->first();
        $peakMonth = $peakMonthRow ? (string) $peakMonthRow->ym : '—';
        $peakMonthTotal = $peakMonthRow ? (int) $peakMonthRow->total : 0;

        if ($useLegacyReemplazos ?? false) {
            $topReplacersRaw = ReemplazoBombero::query()
                ->with('replacementFirefighter')
                ->whereBetween('inicio', [$from, $to])
                ->when($guardiaId, fn ($q) => $q->where('guardia_id', $guardiaId))
                ->get()
                ->groupBy('bombero_reemplazante_id')
                ->map(function ($items) {
                    $ff = $items->first()?->replacementFirefighter;
                    return (object) [
                        'user_id' => (int) ($items->first()?->bombero_reemplazante_id ?? 0),
                        'name' => $ff?->nombres,
                        'last_name_paternal' => $ff?->apellido_paterno,
                        'total' => $items->count(),
                    ];
                })
                ->values()
                ->sortByDesc('total')
                ->take(10)
                ->values();
        } else {
            $topReplacersRaw = DB::table('staff_events')
                ->when($useFirefighterIds, function ($q) {
                    $q->join('bomberos as replacers', 'staff_events.replacement_firefighter_id', '=', 'replacers.id')
                      ->join('bomberos as originals', 'staff_events.firefighter_id', '=', 'originals.id')
                      ->selectRaw('replacers.id as user_id, replacers.nombres as name, replacers.apellido_paterno as last_name_paternal, COUNT(*) as total')
                      ->whereNotNull('staff_events.replacement_firefighter_id');
                }, function ($q) {
                    $q->join('users as replacers', 'staff_events.replacement_user_id', '=', 'replacers.id')
                      ->join('users as originals', 'staff_events.user_id', '=', 'originals.id')
                      ->selectRaw('replacers.id as user_id, replacers.name, replacers.last_name_paternal, COUNT(*) as total')
                      ->whereNotNull('staff_events.replacement_user_id');
                })
                ->where('staff_events.type', 'replacement')
                ->where('staff_events.status', 'approved')
                ->whereBetween('staff_events.start_date', [$from, $to])
                ->when($guardiaId, fn ($q) => $q->where('originals.guardia_id', $guardiaId))
                ->when($useFirefighterIds, function ($q) {
                    $q->groupBy('replacers.id', 'replacers.nombres', 'replacers.apellido_paterno');
                }, function ($q) {
                    $q->groupBy('replacers.id', 'replacers.name', 'replacers.last_name_paternal');
                })
                ->orderByDesc('total')
                ->limit(10)
                ->get();
        }

        $topReplacers = $topReplacersRaw->map(function ($r) {
            return [
                'user_id' => (int) $r->user_id,
                'name' => trim((string) ($r->name ?? '') . ' ' . (string) ($r->last_name_paternal ?? '')),
                'total' => (int) $r->total,
            ];
        });

        if ($useLegacyReemplazos ?? false) {
            $topReplacersByGuardiaRaw = ReemplazoBombero::query()
                ->with(['guardia', 'replacementFirefighter'])
                ->whereBetween('inicio', [$from, $to])
                ->when($guardiaId, fn ($q) => $q->where('guardia_id', $guardiaId))
                ->get()
                ->groupBy(fn ($r) => $r->guardia?->name ?? 'Sin Asignar')
                ->flatMap(function ($items, $guardiaName) {
                    return $items
                        ->groupBy('bombero_reemplazante_id')
                        ->map(function ($rows) use ($guardiaName) {
                            $ff = $rows->first()?->replacementFirefighter;
                            return (object) [
                                'guardia_name' => $guardiaName,
                                'user_id' => (int) ($rows->first()?->bombero_reemplazante_id ?? 0),
                                'name' => $ff?->nombres,
                                'last_name_paternal' => $ff?->apellido_paterno,
                                'total' => $rows->count(),
                            ];
                        })
                        ->values();
                })
                ->values()
                ->sortBy('guardia_name')
                ->sortByDesc('total')
                ->values();
        } else {
            $topReplacersByGuardiaRaw = DB::table('staff_events')
                ->when($useFirefighterIds, function ($q) {
                    $q->join('bomberos as originals', 'staff_events.firefighter_id', '=', 'originals.id')
                      ->leftJoin('guardias', 'originals.guardia_id', '=', 'guardias.id')
                      ->join('bomberos as replacers', 'staff_events.replacement_firefighter_id', '=', 'replacers.id')
                      ->selectRaw('COALESCE(guardias.name, "Sin Asignar") as guardia_name, replacers.id as user_id, replacers.nombres as name, replacers.apellido_paterno as last_name_paternal, COUNT(*) as total')
                      ->whereNotNull('staff_events.replacement_firefighter_id');
                }, function ($q) {
                    $q->join('users as originals', 'staff_events.user_id', '=', 'originals.id')
                      ->leftJoin('guardias', 'originals.guardia_id', '=', 'guardias.id')
                      ->join('users as replacers', 'staff_events.replacement_user_id', '=', 'replacers.id')
                      ->selectRaw('COALESCE(guardias.name, "Sin Asignar") as guardia_name, replacers.id as user_id, replacers.name, replacers.last_name_paternal, COUNT(*) as total')
                      ->whereNotNull('staff_events.replacement_user_id');
                })
                ->where('staff_events.type', 'replacement')
                ->where('staff_events.status', 'approved')
                ->whereBetween('staff_events.start_date', [$from, $to])
                ->when($guardiaId, fn ($q) => $q->where('originals.guardia_id', $guardiaId))
                ->when($useFirefighterIds, function ($q) {
                    $q->groupBy('guardia_name', 'replacers.id', 'replacers.nombres', 'replacers.apellido_paterno');
                }, function ($q) {
                    $q->groupBy('guardia_name', 'replacers.id', 'replacers.name', 'replacers.last_name_paternal');
                })
                ->orderBy('guardia_name')
                ->orderByDesc('total')
                ->get();
        }

        $topReplacersByGuardia = $topReplacersByGuardiaRaw
            ->groupBy('guardia_name')
            ->map(function ($items) {
                return $items->take(5)->values()->map(function ($r) {
                    return [
                        'user_id' => (int) $r->user_id,
                        'name' => trim((string) ($r->name ?? '') . ' ' . (string) ($r->last_name_paternal ?? '')),
                        'total' => (int) $r->total,
                    ];
                });
            });

        $drivers = Bombero::with('guardia')
            ->whereNotNull('guardia_id')
            ->where('es_conductor', true)
            ->orderBy('guardia_id')
            ->orderBy('apellido_paterno')
            ->orderBy('nombres')
            ->get();

        $driversByGuardia = $drivers->groupBy(fn ($u) => $u->guardia?->name ?? 'Sin Asignar')
            ->map(function ($items) {
                return $items->groupBy(function ($u) {
                    return $u->estado_asistencia ?: 'sin_estado';
                });
            });

        $charts = [
            'timeline' => [
                'labels' => array_keys($period),
                'data' => array_values($period),
            ],
            'by_month' => [
                'labels' => $replacementsByMonth->pluck('ym')->values(),
                'data' => $replacementsByMonth->pluck('total')->map(fn ($v) => (int) $v)->values(),
            ],
            'by_guardia' => [
                'labels' => $replacementsByGuardia->pluck('guardia_name')->values(),
                'data' => $replacementsByGuardia->pluck('total')->map(fn ($v) => (int) $v)->values(),
            ],
            'top_replacers' => [
                'labels' => $topReplacers->pluck('name')->values(),
                'data' => $topReplacers->pluck('total')->values(),
            ],
        ];

        $kpis = [
            'total_replacements' => (int) $totalReplacements,
            'total_replacements_all_time' => (int) $totalReplacementsAllTime,
            'unique_replacers' => (int) $uniqueReplacers,
            'unique_replaced' => (int) $uniqueReplaced,
            'peak_month' => $peakMonth,
            'peak_month_total' => (int) $peakMonthTotal,
            'peak_guardia' => $peakGuardia,
            'peak_guardia_total' => (int) $peakGuardiaTotal,
            'range_label' => $from->format('d-m-Y') . ' → ' . $to->format('d-m-Y'),
        ];

        return view('admin.reports.replacements', compact(
            'guardias',
            'from',
            'to',
            'guardiaId',
            'activeGuardia',
            'kpis',
            'events',
            'topReplacers',
            'topReplacersByGuardia',
            'driversByGuardia',
            'charts'
        ));
    }

    public function replacementsExport(Request $request)
    {
        ReplacementService::expire();

        $user = auth()->user();
        if ($user->role !== 'super_admin') {
            abort(403, 'No autorizado.');
        }

        [$from, $to, $guardiaId] = $this->parseReplacementsFilters($request);
        $base = $this->replacementsBaseQuery($from, $to, $guardiaId);

        $useLegacyReemplazos = Schema::hasTable('reemplazos_bomberos') && (clone $base)->count() === 0;

        if ($useLegacyReemplazos) {
            $legacyBase = ReemplazoBombero::query()
                ->when($guardiaId, fn ($q) => $q->where('guardia_id', $guardiaId))
                ->whereBetween('inicio', [$from, $to]);

            $events = (clone $legacyBase)
                ->with(['originalFirefighter.guardia', 'replacementFirefighter', 'guardia'])
                ->orderBy('inicio', 'asc')
                ->limit(10000)
                ->get()
                ->map(function ($r) {
                    $o = new \stdClass();
                    $o->start_date = $r->inicio;
                    $o->end_date = $r->fin;
                    $o->firefighter = $r->originalFirefighter;
                    $o->replacementFirefighter = $r->replacementFirefighter;
                    $o->user = null;
                    $o->replacementUser = null;
                    $o->status = $r->estado;
                    $o->description = $r->notas;
                    return $o;
                });
        } else {
            $events = (clone $base)
                ->with(['firefighter.guardia', 'replacementFirefighter', 'user.guardia', 'replacementUser'])
                ->orderBy('start_date', 'asc')
                ->limit(10000)
                ->get();
        }

        $headings = [
            'Inicio',
            'Fin',
            'Guardia',
            'Reemplazado',
            'Reemplazante',
            'Estado',
            'Descripción',
        ];

        $rows = $events->map(function ($e) {
            $guardiaName = $e->firefighter?->guardia?->name ?? $e->user?->guardia?->name ?? 'Sin Asignar';
            $originalName = $e->firefighter
                ? trim(($e->firefighter->nombres ?? '') . ' ' . ($e->firefighter->apellido_paterno ?? ''))
                : trim(($e->user?->name ?? '') . ' ' . ($e->user?->last_name_paternal ?? ''));
            $replacerName = $e->replacementFirefighter
                ? trim(($e->replacementFirefighter->nombres ?? '') . ' ' . ($e->replacementFirefighter->apellido_paterno ?? ''))
                : trim(($e->replacementUser?->name ?? '') . ' ' . ($e->replacementUser?->last_name_paternal ?? ''));

            return [
                optional($e->start_date)->format('Y-m-d H:i'),
                optional($e->end_date)->format('Y-m-d H:i'),
                $guardiaName,
                $originalName,
                $replacerName,
                $e->status,
                $e->description,
            ];
        })->toArray();

        $suffix = $guardiaId ? ('_guardia_' . $guardiaId) : '';
        $filename = 'reemplazos_' . $from->format('Ymd') . '_' . $to->format('Ymd') . $suffix . '.xlsx';

        return Excel::download(new ReplacementsReportExport($rows, $headings), $filename);
    }

    public function replacementsPrint(Request $request)
    {
        ReplacementService::expire();

        $user = auth()->user();
        if ($user->role !== 'super_admin') {
            abort(403, 'No autorizado.');
        }

        $guardias = Guardia::orderBy('name')->get();
        $activeGuardia = $this->resolveActiveGuardia(now());
        [$from, $to, $guardiaId] = $this->parseReplacementsFilters($request);
        $base = $this->replacementsBaseQuery($from, $to, $guardiaId);

        $useLegacyReemplazos = Schema::hasTable('reemplazos_bomberos') && (clone $base)->count() === 0;

        if ($useLegacyReemplazos) {
            $legacyBase = ReemplazoBombero::query()
                ->when($guardiaId, fn ($q) => $q->where('guardia_id', $guardiaId))
                ->whereBetween('inicio', [$from, $to]);

            $events = (clone $legacyBase)
                ->with(['originalFirefighter.guardia', 'replacementFirefighter', 'guardia'])
                ->orderByDesc('inicio')
                ->limit(1500)
                ->get()
                ->map(function ($r) {
                    $o = new \stdClass();
                    $o->start_date = $r->inicio;
                    $o->end_date = $r->fin;
                    $o->firefighter = $r->originalFirefighter;
                    $o->replacementFirefighter = $r->replacementFirefighter;
                    $o->user = null;
                    $o->replacementUser = null;
                    $o->status = $r->estado;
                    $o->description = $r->notas;
                    return $o;
                });

            $totalReplacements = (clone $legacyBase)->count();
            $uniqueReplacers = (clone $legacyBase)->distinct('bombero_reemplazante_id')->count('bombero_reemplazante_id');
            $uniqueReplaced = (clone $legacyBase)->distinct('bombero_titular_id')->count('bombero_titular_id');
        } else {
            $useFirefighterIds = Schema::hasColumn('staff_events', 'firefighter_id');

            $events = (clone $base)
                ->with(['firefighter.guardia', 'replacementFirefighter', 'user.guardia', 'replacementUser'])
                ->orderByDesc('start_date')
                ->limit(1500)
                ->get();

            $totalReplacements = (clone $base)->count();
            $uniqueReplacers = $useFirefighterIds
                ? (clone $base)->whereNotNull('replacement_firefighter_id')->distinct('replacement_firefighter_id')->count('replacement_firefighter_id')
                : (clone $base)->whereNotNull('replacement_user_id')->distinct('replacement_user_id')->count('replacement_user_id');
            $uniqueReplaced = $useFirefighterIds
                ? (clone $base)->distinct('firefighter_id')->count('firefighter_id')
                : (clone $base)->distinct('user_id')->count('user_id');
        }

        $kpis = [
            'total_replacements' => (int) $totalReplacements,
            'unique_replacers' => (int) $uniqueReplacers,
            'unique_replaced' => (int) $uniqueReplaced,
            'range_label' => $from->format('d-m-Y') . ' → ' . $to->format('d-m-Y'),
        ];

        $topReplacersRaw = DB::table('staff_events')
            ->when($useFirefighterIds, function ($q) {
                $q->join('bomberos as replacers', 'staff_events.replacement_firefighter_id', '=', 'replacers.id')
                  ->join('bomberos as originals', 'staff_events.firefighter_id', '=', 'originals.id')
                  ->selectRaw('replacers.id as user_id, replacers.nombres as name, replacers.apellido_paterno as last_name_paternal, COUNT(*) as total')
                  ->whereNotNull('staff_events.replacement_firefighter_id');
            }, function ($q) {
                $q->join('users as replacers', 'staff_events.replacement_user_id', '=', 'replacers.id')
                  ->join('users as originals', 'staff_events.user_id', '=', 'originals.id')
                  ->selectRaw('replacers.id as user_id, replacers.name, replacers.last_name_paternal, COUNT(*) as total')
                  ->whereNotNull('staff_events.replacement_user_id');
            })
            ->where('staff_events.type', 'replacement')
            ->where('staff_events.status', 'approved')
            ->whereBetween('staff_events.start_date', [$from, $to])
            ->when($guardiaId, fn ($q) => $q->where('originals.guardia_id', $guardiaId))
            ->when($useFirefighterIds, function ($q) {
                $q->groupBy('replacers.id', 'replacers.nombres', 'replacers.apellido_paterno');
            }, function ($q) {
                $q->groupBy('replacers.id', 'replacers.name', 'replacers.last_name_paternal');
            })
            ->orderByDesc('total')
            ->limit(15)
            ->get();

        $topReplacers = $topReplacersRaw->map(function ($r) {
            return [
                'user_id' => (int) $r->user_id,
                'name' => trim($r->name . ' ' . $r->last_name_paternal),
                'total' => (int) $r->total,
            ];
        });

        $replacementsByGuardia = DB::table('staff_events')
            ->when($useFirefighterIds, function ($q) {
                $q->join('bomberos as originals', 'staff_events.firefighter_id', '=', 'originals.id')
                  ->leftJoin('guardias', 'originals.guardia_id', '=', 'guardias.id');
            }, function ($q) {
                $q->join('users as originals', 'staff_events.user_id', '=', 'originals.id')
                  ->leftJoin('guardias', 'originals.guardia_id', '=', 'guardias.id');
            })
            ->selectRaw('COALESCE(guardias.name, "Sin Asignar") as guardia_name, COUNT(*) as total')
            ->where('staff_events.type', 'replacement')
            ->where('staff_events.status', 'approved')
            ->whereBetween('staff_events.start_date', [$from, $to])
            ->when($guardiaId, fn ($q) => $q->where('originals.guardia_id', $guardiaId))
            ->groupBy('guardia_name')
            ->orderByDesc('total')
            ->get();

        $topReplacersByGuardiaRaw = DB::table('staff_events')
            ->when($useFirefighterIds, function ($q) {
                $q->join('bomberos as originals', 'staff_events.firefighter_id', '=', 'originals.id')
                  ->leftJoin('guardias', 'originals.guardia_id', '=', 'guardias.id')
                  ->join('bomberos as replacers', 'staff_events.replacement_firefighter_id', '=', 'replacers.id')
                  ->selectRaw('COALESCE(guardias.name, "Sin Asignar") as guardia_name, replacers.id as user_id, replacers.nombres as name, replacers.apellido_paterno as last_name_paternal, COUNT(*) as total')
                  ->whereNotNull('staff_events.replacement_firefighter_id');
            }, function ($q) {
                $q->join('users as originals', 'staff_events.user_id', '=', 'originals.id')
                  ->leftJoin('guardias', 'originals.guardia_id', '=', 'guardias.id')
                  ->join('users as replacers', 'staff_events.replacement_user_id', '=', 'replacers.id')
                  ->selectRaw('COALESCE(guardias.name, "Sin Asignar") as guardia_name, replacers.id as user_id, replacers.name, replacers.last_name_paternal, COUNT(*) as total')
                  ->whereNotNull('staff_events.replacement_user_id');
            })
            ->where('staff_events.type', 'replacement')
            ->where('staff_events.status', 'approved')
            ->whereBetween('staff_events.start_date', [$from, $to])
            ->when($guardiaId, fn ($q) => $q->where('originals.guardia_id', $guardiaId))
            ->when($useFirefighterIds, function ($q) {
                $q->groupBy('guardia_name', 'replacers.id', 'replacers.nombres', 'replacers.apellido_paterno');
            }, function ($q) {
                $q->groupBy('guardia_name', 'replacers.id', 'replacers.name', 'replacers.last_name_paternal');
            })
            ->orderBy('guardia_name')
            ->orderByDesc('total')
            ->get();

        $topReplacersByGuardia = $topReplacersByGuardiaRaw
            ->groupBy('guardia_name')
            ->map(function ($items) {
                return $items->take(5)->values()->map(function ($r) {
                    return [
                        'user_id' => (int) $r->user_id,
                        'name' => trim($r->name . ' ' . $r->last_name_paternal),
                        'total' => (int) $r->total,
                    ];
                });
            });

        return view('admin.reports.replacements_print', compact(
            'guardias',
            'from',
            'to',
            'guardiaId',
            'activeGuardia',
            'kpis',
            'events',
            'topReplacers',
            'replacementsByGuardia',
            'topReplacersByGuardia'
        ));
    }

    private function parseReplacementsFilters(Request $request): array
    {
        $from = $request->input('from')
            ? Carbon::parse($request->input('from'))->startOfDay()
            : now()->startOfMonth()->startOfDay();
        $to = $request->input('to')
            ? Carbon::parse($request->input('to'))->endOfDay()
            : now()->endOfMonth()->endOfDay();

        if ($to->lessThan($from)) {
            [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
        }

        $guardiaId = $request->input('guardia_id');
        $guardiaId = $guardiaId !== null && $guardiaId !== '' ? (int) $guardiaId : null;

        return [$from, $to, $guardiaId];
    }

    private function replacementsBaseQuery(Carbon $from, Carbon $to, ?int $guardiaId)
    {
        $base = StaffEvent::query()
            ->where('type', 'replacement')
            ->where('status', 'approved')
            ->whereBetween('start_date', [$from, $to]);

        if ($guardiaId) {
            if (Schema::hasColumn('staff_events', 'firefighter_id')) {
                $base->whereIn('firefighter_id', function ($q) use ($guardiaId) {
                    $q->select('id')->from('bomberos')->where('guardia_id', $guardiaId);
                });
            } else {
                $base->whereIn('user_id', function ($q) use ($guardiaId) {
                    $q->select('id')->from('users')->where('guardia_id', $guardiaId);
                });
            }
        }

        return $base;
    }

    private function formatMinutes($minutes)
    {
        $hours = floor($minutes / 60);
        $mins = $minutes % 60;
        return sprintf('%dh %02dm', $hours, $mins);
    }
}
