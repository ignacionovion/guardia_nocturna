@extends('layouts.app')

@section('content')
    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4 border-b border-slate-200 pb-6">
        <div>
            <h1 class="text-3xl font-extrabold text-slate-800 tracking-tight flex items-center uppercase">
                <i class="fas fa-chart-line mr-3 text-red-700"></i> Reporte de Asistencia
            </h1>
            <p class="text-slate-500 mt-1 font-medium">Desglose de asistencia por Guardia, Semana, Mes y Año</p>
        </div>
        
        <!-- Filtros -->
        <div class="bg-white p-1.5 rounded-lg shadow-sm border border-slate-200">
            <form action="{{ route('admin.reports.index') }}" method="GET" class="flex items-center space-x-2">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-calendar-days text-slate-400"></i>
                    </div>
                    <select name="month" class="pl-10 pr-8 py-2 bg-slate-50 border-transparent focus:border-blue-500 focus:bg-white focus:ring-0 rounded-md text-sm font-medium text-slate-700 cursor-pointer hover:bg-slate-100 transition-colors">
                        @foreach(range(1, 12) as $m)
                            <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                                {{ ucfirst(\Carbon\Carbon::create()->month($m)->locale('es')->monthName) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="relative">
                    <select name="year" class="pl-4 pr-8 py-2 bg-slate-50 border-transparent focus:border-blue-500 focus:bg-white focus:ring-0 rounded-md text-sm font-medium text-slate-700 cursor-pointer hover:bg-slate-100 transition-colors">
                        @foreach(range(now()->year - 2, now()->year) as $y)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>
                                {{ $y }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="bg-slate-800 hover:bg-slate-700 text-white font-bold py-2 px-4 rounded-md text-sm transition shadow-sm flex items-center">
                    <i class="fas fa-filter mr-2"></i> Filtrar
                </button>
            </form>
        </div>
    </div>

    @isset($selectedMonthKpis)
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-4 mb-8">
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Promedio Bomberos / Guardia</div>
                        <div class="text-2xl font-black text-slate-800 mt-1">{{ number_format($selectedMonthKpis['avg_firefighters_per_guardia_day'] ?? 0, 1, ',', '.') }}</div>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center border border-blue-100">
                        <i class="fas fa-users text-lg"></i>
                    </div>
                </div>
                <div class="text-[10px] text-slate-400 mt-2 font-medium">Promedio por guardia/día</div>
            </div>

            <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Horas Bombero</div>
                        <div class="text-2xl font-black text-slate-800 mt-1">{{ $selectedMonthKpis['total_hours_formatted'] ?? '0h 00m' }}</div>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center border border-emerald-100">
                        <i class="fas fa-clock text-lg"></i>
                    </div>
                </div>
                <div class="text-[10px] text-slate-400 mt-2 font-medium">Suma de turnos cerrados</div>
            </div>

            <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Reemplazos</div>
                        <div class="text-2xl font-black text-slate-800 mt-1">{{ $selectedMonthKpis['reemplazo'] ?? 0 }}</div>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-700 flex items-center justify-center border border-purple-100">
                        <i class="fas fa-right-left text-lg"></i>
                    </div>
                </div>
                <div class="text-[10px] text-slate-400 mt-2 font-medium">Conteo mensual</div>
            </div>

            <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Ausentes</div>
                        <div class="text-2xl font-black text-slate-800 mt-1">{{ $selectedMonthKpis['ausente'] ?? 0 }}</div>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-red-50 text-red-700 flex items-center justify-center border border-red-100">
                        <i class="fas fa-user-slash text-lg"></i>
                    </div>
                </div>
                <div class="text-[10px] text-slate-400 mt-2 font-medium">Conteo mensual</div>
            </div>

            <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Permisos</div>
                        <div class="text-2xl font-black text-slate-800 mt-1">{{ $selectedMonthKpis['permiso'] ?? 0 }}</div>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center border border-amber-100">
                        <i class="fas fa-id-badge text-lg"></i>
                    </div>
                </div>
                <div class="text-[10px] text-slate-400 mt-2 font-medium">Conteo mensual</div>
            </div>

            <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Licencias</div>
                        <div class="text-2xl font-black text-slate-800 mt-1">{{ $selectedMonthKpis['licencia'] ?? 0 }}</div>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-slate-50 text-slate-700 flex items-center justify-center border border-slate-200">
                        <i class="fas fa-notes-medical text-lg"></i>
                    </div>
                </div>
                <div class="text-[10px] text-slate-400 mt-2 font-medium">Conteo mensual</div>
            </div>
        </div>
    @endisset

    @isset($charts)
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-10">
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
                <div class="flex items-center justify-between mb-4">
                    <div class="text-sm font-black text-slate-800 uppercase tracking-wide">Promedio & Horas (por Mes)</div>
                    <div class="text-xs font-bold text-slate-400 uppercase tracking-widest">{{ $year }}</div>
                </div>
                <div class="h-[280px]">
                    <canvas id="chartAvgHours"></canvas>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
                <div class="flex items-center justify-between mb-4">
                    <div class="text-sm font-black text-slate-800 uppercase tracking-wide">Estados (por Mes)</div>
                    <div class="text-xs font-bold text-slate-400 uppercase tracking-widest">{{ $year }}</div>
                </div>
                <div class="h-[280px]">
                    <canvas id="chartStatus"></canvas>
                </div>
            </div>
        </div>
    @endisset

    <div class="space-y-8">
        @foreach($guardias as $guardia)
            @if($guardia->users->count() > 0)
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="bg-slate-50 px-6 py-4 border-b border-slate-200 flex justify-between items-center">
                        <h2 class="text-lg font-bold text-slate-800 flex items-center uppercase tracking-wide">
                            <span class="w-2 h-6 bg-red-600 rounded mr-3"></span>
                            {{ $guardia->name }}
                        </h2>
                        <span class="text-xs font-bold bg-white text-slate-600 px-3 py-1 rounded-full border border-slate-200 shadow-sm">
                            {{ $guardia->users->count() }} Voluntarios
                        </span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider sticky left-0 bg-slate-50 z-10 border-r border-slate-200 shadow-[2px_0_5px_-2px_rgba(0,0,0,0.05)]">
                                        Voluntario
                                    </th>
                                    
                                    <!-- Semanas Dinámicas -->
                                    @foreach($weeksInMonth as $index => $weekNum)
                                        <th scope="col" class="px-4 py-3 text-center text-xs font-bold text-slate-500 uppercase tracking-wider border-r border-slate-100 last:border-r-0">
                                            Sem {{ $weekNum }}
                                        </th>
                                    @endforeach

                                    <th scope="col" class="px-6 py-3 text-center text-xs font-bold text-blue-600 uppercase tracking-wider bg-blue-50/50">
                                        Total Mes
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-bold text-slate-600 uppercase tracking-wider bg-slate-100/50">
                                        Total Año
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-slate-100">
                                @foreach($guardia->users as $user)
                                    <tr class="hover:bg-slate-50 transition-colors group">
                                        <td class="px-6 py-3 whitespace-nowrap sticky left-0 bg-white group-hover:bg-slate-50 transition-colors z-10 border-r border-slate-200 shadow-[2px_0_5px_-2px_rgba(0,0,0,0.05)]">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-8 w-8 rounded-lg bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-500 font-bold text-xs">
                                                    {{ substr($user->name, 0, 1) }}{{ substr($user->last_name_paternal, 0, 1) }}
                                                </div>
                                                <div class="ml-3">
                                                    <div class="text-sm font-bold text-slate-700">{{ $user->name }} {{ $user->last_name_paternal }}</div>
                                                    @if($user->company)
                                                        <div class="text-[10px] text-slate-400 font-medium">{{ $user->company }}</div>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>

                                        <!-- Datos Semanales -->
                                        @foreach($weeksInMonth as $weekNum)
                                            @php
                                                $stats = $user->weekly_stats->get($weekNum);
                                            @endphp
                                            <td class="px-4 py-3 whitespace-nowrap text-center border-r border-slate-50 last:border-r-0">
                                                @if($stats && $stats['minutes'] > 0)
                                                    <div class="flex flex-col items-center">
                                                        <span class="text-xs font-bold text-slate-700 bg-emerald-50 text-emerald-700 px-2 py-0.5 rounded border border-emerald-100">
                                                            {{ $stats['formatted'] }}
                                                        </span>
                                                        <span class="text-[10px] text-slate-400 mt-0.5">{{ $stats['days'] }} día(s)</span>
                                                    </div>
                                                @else
                                                    <span class="text-slate-300 text-lg">-</span>
                                                @endif
                                            </td>
                                        @endforeach

                                        <!-- Total Mes -->
                                        <td class="px-6 py-3 whitespace-nowrap text-center bg-blue-50/30">
                                            <div class="flex flex-col items-center">
                                                <span class="text-sm font-black text-blue-700">
                                                    {{ $user->month_hours_formatted }}
                                                </span>
                                                <span class="text-[10px] text-blue-400 font-semibold">{{ $user->month_days }} día(s)</span>
                                            </div>
                                        </td>

                                        <!-- Total Año -->
                                        <td class="px-6 py-3 whitespace-nowrap text-center bg-slate-50/50">
                                            <div class="flex flex-col items-center">
                                                <span class="text-sm font-bold text-slate-700">
                                                    {{ $user->year_hours_formatted }}
                                                </span>
                                                <span class="text-[10px] text-slate-400 font-medium">{{ $user->year_days }} día(s)</span>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        @endforeach

        @if($guardias->isEmpty())
             <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-12 text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-slate-100 mb-4">
                    <i class="fas fa-folder-open text-3xl text-slate-400"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-700 mb-2">Sin Datos</h3>
                <p class="text-slate-500">No se encontraron guardias o registros para el periodo seleccionado.</p>
            </div>
        @endif
    </div>

    @isset($charts)
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const chartsData = @json($charts);
                if (!chartsData || !chartsData.labels) return;

                const labels = chartsData.labels;
                const avg = chartsData.avg_firefighters_per_guardia_day || [];
                const totalMinutes = chartsData.total_minutes || [];
                const totalHours = totalMinutes.map(m => Math.round(((m || 0) / 60) * 10) / 10);

                const elAvgHours = document.getElementById('chartAvgHours');
                if (elAvgHours) {
                    new Chart(elAvgHours, {
                        data: {
                            labels,
                            datasets: [
                                {
                                    type: 'line',
                                    label: 'Promedio bomberos/guardia',
                                    data: avg,
                                    borderColor: '#1d4ed8',
                                    backgroundColor: 'rgba(29, 78, 216, 0.12)',
                                    borderWidth: 3,
                                    tension: 0.35,
                                    pointRadius: 3,
                                    pointHoverRadius: 5,
                                    yAxisID: 'y',
                                },
                                {
                                    type: 'bar',
                                    label: 'Horas totales',
                                    data: totalHours,
                                    backgroundColor: 'rgba(16, 185, 129, 0.35)',
                                    borderColor: '#059669',
                                    borderWidth: 1,
                                    yAxisID: 'y1',
                                    borderRadius: 8,
                                    barThickness: 18,
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            interaction: { mode: 'index', intersect: false },
                            plugins: {
                                legend: { position: 'bottom', labels: { boxWidth: 12, boxHeight: 12 } },
                                tooltip: { enabled: true }
                            },
                            scales: {
                                x: { grid: { display: false } },
                                y: {
                                    position: 'left',
                                    beginAtZero: true,
                                    title: { display: true, text: 'Promedio' },
                                },
                                y1: {
                                    position: 'right',
                                    beginAtZero: true,
                                    grid: { drawOnChartArea: false },
                                    title: { display: true, text: 'Horas' },
                                }
                            }
                        }
                    });
                }

                const statusCounts = chartsData.status_counts || {};
                const elStatus = document.getElementById('chartStatus');
                if (elStatus) {
                    new Chart(elStatus, {
                        type: 'bar',
                        data: {
                            labels,
                            datasets: [
                                {
                                    label: 'Reemplazo',
                                    data: statusCounts.reemplazo || [],
                                    backgroundColor: 'rgba(147, 51, 234, 0.35)',
                                    borderColor: '#7c3aed',
                                    borderWidth: 1,
                                    borderRadius: 8,
                                },
                                {
                                    label: 'Ausente',
                                    data: statusCounts.ausente || [],
                                    backgroundColor: 'rgba(239, 68, 68, 0.35)',
                                    borderColor: '#dc2626',
                                    borderWidth: 1,
                                    borderRadius: 8,
                                },
                                {
                                    label: 'Permiso',
                                    data: statusCounts.permiso || [],
                                    backgroundColor: 'rgba(245, 158, 11, 0.35)',
                                    borderColor: '#d97706',
                                    borderWidth: 1,
                                    borderRadius: 8,
                                },
                                {
                                    label: 'Licencia',
                                    data: statusCounts.licencia || [],
                                    backgroundColor: 'rgba(100, 116, 139, 0.35)',
                                    borderColor: '#475569',
                                    borderWidth: 1,
                                    borderRadius: 8,
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { position: 'bottom', labels: { boxWidth: 12, boxHeight: 12 } },
                                tooltip: { enabled: true }
                            },
                            scales: {
                                x: { stacked: true, grid: { display: false } },
                                y: { stacked: true, beginAtZero: true }
                            }
                        }
                    });
                }
            });
        </script>
    @endisset
@endsection
