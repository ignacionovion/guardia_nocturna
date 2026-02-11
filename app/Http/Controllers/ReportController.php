<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exports\ReplacementsReportExport;
use App\Models\Guardia;
use App\Models\GuardiaCalendarDay;
use App\Models\StaffEvent;
use App\Models\ShiftUser;
use App\Models\Bombero;
use App\Services\ReplacementService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
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

        $totalMinutesByMonth = array_fill(1, 12, 0);
        foreach ($shiftUsersForYear as $r) {
            if (!$r->end_time || !$r->start_time) {
                continue;
            }
            $m = (int) $r->start_time->month;
            $totalMinutesByMonth[$m] += $r->end_time->diffInMinutes($r->start_time);
        }

        $statusKeys = ['reemplazo', 'ausente', 'permiso', 'licencia'];
        $statusCountsByMonth = [];
        foreach ($statusKeys as $k) {
            $statusCountsByMonth[$k] = array_fill(1, 12, 0);
        }

        if ($hasShiftUsersAttendanceStatus) {
            foreach ($shiftUsersForYear as $r) {
                if (!$r->start_time) {
                    continue;
                }
                $status = $r->attendance_status;
                if (!$status || !in_array($status, $statusKeys, true)) {
                    continue;
                }
                $m = (int) $r->start_time->month;
                $statusCountsByMonth[$status][$m] += 1;
            }
        }

        $presentCountByMonthGuardiaDay = [];
        foreach ($shiftUsersForYear as $r) {
            if (!$r->start_time) {
                continue;
            }

            $guardiaId = $hasShiftUsersGuardiaId ? $r->guardia_id : null;
            if (!$guardiaId) {
                $guardiaId = $r->firefighter?->guardia_id;
            }
            if (!$guardiaId) {
                $guardiaId = $r->user?->guardia_id;
            }
            if (!$guardiaId) {
                continue;
            }

            $isPresent = $hasShiftUsersAttendanceStatus
                ? in_array($r->attendance_status, ['constituye', 'reemplazo'], true)
                : (bool) $r->present;

            if (!$isPresent) {
                continue;
            }

            $m = (int) $r->start_time->month;
            $day = $r->start_time->toDateString();
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

        $selectedMonthMinutes = $totalMinutesByMonth[$month] ?? 0;
        $selectedMonthHoursFormatted = $this->formatMinutes($selectedMonthMinutes);

        $selectedMonthKpis = [
            'avg_firefighters_per_guardia_day' => round((float) ($avgFirefightersPerGuardiaDayByMonth[$month] ?? 0), 1),
            'total_minutes' => (int) $selectedMonthMinutes,
            'total_hours_formatted' => $selectedMonthHoursFormatted,
            'reemplazo' => (int) ($statusCountsByMonth['reemplazo'][$month] ?? 0),
            'ausente' => (int) ($statusCountsByMonth['ausente'][$month] ?? 0),
            'permiso' => (int) ($statusCountsByMonth['permiso'][$month] ?? 0),
            'licencia' => (int) ($statusCountsByMonth['licencia'][$month] ?? 0),
        ];

        $charts = [
            'labels' => $monthLabels,
            'avg_firefighters_per_guardia_day' => collect($avgFirefightersPerGuardiaDayByMonth)->values(),
            'total_minutes' => collect($totalMinutesByMonth)->values(),
            'status_counts' => collect($statusCountsByMonth)
                ->map(fn ($arr) => collect($arr)->values())
                ->all(),
        ];

        // 1. Guardias con su dotación (Bomberos)
        $guardias = Guardia::with(['bomberos' => function ($query) {
            $query->orderBy('apellido_paterno')->orderBy('nombres');
        }])->get();

        // 2. Asistencias cerradas del año agrupadas por bombero (o por user legacy)
        $attendanceKey = $hasShiftUsersFirefighterId ? 'firefighter_id' : 'user_id';
        $allAttendances = ShiftUser::whereYear('start_time', $year)
            ->whereNotNull('end_time')
            ->get()
            ->groupBy($attendanceKey);

        // 3. Procesar datos por bombero
        foreach ($guardias as $guardia) {
            foreach ($guardia->bomberos as $user) {
                $userRecords = $allAttendances->get($user->id, collect());

                // --- ANUAL ---
                $user->year_days = $userRecords->map(fn ($r) => $r->start_time->format('Y-m-d'))->unique()->count();
                $user->year_minutes = $userRecords->sum(fn ($r) => $r->end_time->diffInMinutes($r->start_time));
                $user->year_hours_formatted = $this->formatMinutes($user->year_minutes);

                // --- MENSUAL ---
                $monthRecords = $userRecords->filter(fn ($r) => $r->start_time->month == $month);
                $user->month_days = $monthRecords->map(fn ($r) => $r->start_time->format('Y-m-d'))->unique()->count();
                $user->month_minutes = $monthRecords->sum(fn ($r) => $r->end_time->diffInMinutes($r->start_time));
                $user->month_hours_formatted = $this->formatMinutes($user->month_minutes);

                $user->weekly_stats = $monthRecords->groupBy(function ($r) {
                    return $r->start_time->weekOfYear;
                })->map(function ($weekRecords) {
                    $mins = $weekRecords->sum(fn ($r) => $r->end_time->diffInMinutes($r->start_time));
                    return [
                        'days' => $weekRecords->map(fn ($r) => $r->start_time->format('Y-m-d'))->unique()->count(),
                        'minutes' => $mins,
                        'formatted' => $this->formatMinutes($mins),
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

        return view('admin.reports.index', compact('guardias', 'month', 'year', 'weeksInMonth', 'charts', 'selectedMonthKpis'));
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

        $topReplacers = $topReplacersRaw->map(function ($r) {
            return [
                'user_id' => (int) $r->user_id,
                'name' => trim((string) ($r->name ?? '') . ' ' . (string) ($r->last_name_paternal ?? '')),
                'total' => (int) $r->total,
            ];
        });

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
            'unique_replacers' => (int) $uniqueReplacers,
            'unique_replaced' => (int) $uniqueReplaced,
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

        $events = (clone $base)
            ->with(['firefighter.guardia', 'replacementFirefighter', 'user.guardia', 'replacementUser'])
            ->orderBy('start_date', 'asc')
            ->limit(10000)
            ->get();

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
