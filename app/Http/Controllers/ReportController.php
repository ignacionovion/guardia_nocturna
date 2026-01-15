<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Guardia;
use App\Models\ShiftUser;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);

        $monthLabels = collect(range(1, 12))
            ->map(fn ($m) => ucfirst(Carbon::create()->month($m)->locale('es')->monthName))
            ->values();

        $hasShiftUsersGuardiaId = Schema::hasColumn('shift_users', 'guardia_id');
        $hasShiftUsersAttendanceStatus = Schema::hasColumn('shift_users', 'attendance_status');

        $shiftUsersForYear = ShiftUser::whereYear('start_time', $year)
            ->whereNotNull('start_time')
            ->with('user')
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

        // 1. Obtener todas las Guardias con sus Usuarios
        $guardias = Guardia::with(['users' => function($query) {
            $query->orderBy('last_name_paternal')->orderBy('name');
        }])->get();

        // 2. Obtener usuarios sin guardia asignada (opcional, para no perder data)
        $usersWithoutGuardia = User::whereNull('guardia_id')
            ->orderBy('last_name_paternal')
            ->orderBy('name')
            ->get();
            
        if ($usersWithoutGuardia->isNotEmpty()) {
            $noGuardia = new Guardia(['name' => 'Sin Asignar']);
            $noGuardia->id = 0; // ID ficticio
            $noGuardia->setRelation('users', $usersWithoutGuardia);
            $guardias->push($noGuardia);
        }

        // 3. Obtener todas las asistencias del AÑO seleccionado para optimizar memoria
        // Traemos start_time y end_time casteados por Eloquent
        $allAttendances = ShiftUser::whereYear('start_time', $year)
            ->whereNotNull('end_time')
            ->get()
            ->groupBy('user_id');

        // 4. Procesar datos
        foreach ($guardias as $guardia) {
            foreach ($guardia->users as $user) {
                $userRecords = $allAttendances->get($user->id, collect());

                // --- ANUAL ---
                $user->year_days = $userRecords->map(fn($r) => $r->start_time->format('Y-m-d'))->unique()->count();
                $user->year_minutes = $userRecords->sum(fn($r) => $r->end_time->diffInMinutes($r->start_time));
                $user->year_hours_formatted = $this->formatMinutes($user->year_minutes);

                // --- MENSUAL ---
                $monthRecords = $userRecords->filter(fn($r) => $r->start_time->month == $month);
                $user->month_days = $monthRecords->map(fn($r) => $r->start_time->format('Y-m-d'))->unique()->count();
                $user->month_minutes = $monthRecords->sum(fn($r) => $r->end_time->diffInMinutes($r->start_time));
                $user->month_hours_formatted = $this->formatMinutes($user->month_minutes);

                // --- SEMANAL (del mes seleccionado) ---
                // Agrupamos por semana del año para separar las semanas del mes
                $user->weekly_stats = $monthRecords->groupBy(function($r) {
                    return $r->start_time->weekOfYear;
                })->map(function($weekRecords) {
                    $mins = $weekRecords->sum(fn($r) => $r->end_time->diffInMinutes($r->start_time));
                    return [
                        'days' => $weekRecords->map(fn($r) => $r->start_time->format('Y-m-d'))->unique()->count(),
                        'minutes' => $mins,
                        'formatted' => $this->formatMinutes($mins)
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

    private function formatMinutes($minutes)
    {
        $hours = floor($minutes / 60);
        $mins = $minutes % 60;
        return sprintf('%dh %02dm', $hours, $mins);
    }
}
