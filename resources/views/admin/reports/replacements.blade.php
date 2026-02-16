@extends('layouts.app')

@section('content')
    <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4 border-b border-slate-200 pb-6">
        <div>
            <h1 class="text-3xl font-extrabold text-slate-800 tracking-tight flex items-center uppercase">
                <i class="fas fa-right-left mr-3 text-red-700"></i> Reporte de Reemplazos
            </h1>
            <p class="text-slate-500 mt-1 font-medium">Top reemplazantes, fechas exactas, estadísticas por guardia y conductores</p>
        </div>

        <div class="bg-white p-1.5 rounded-lg shadow-sm border border-slate-200 w-full md:w-auto">
            <form action="{{ route('admin.reports.replacements') }}" method="GET" class="flex flex-col md:flex-row md:items-center gap-2">
                <div class="flex items-center gap-2">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-calendar-days text-slate-400"></i>
                        </div>
                        <input type="date" name="from" value="{{ $from->toDateString() }}" class="pl-10 pr-4 py-2 bg-slate-50 border-transparent focus:border-blue-500 focus:bg-white focus:ring-0 rounded-md text-sm font-medium text-slate-700 cursor-pointer hover:bg-slate-100 transition-colors" />
                    </div>

                    <div class="relative">
                        <input type="date" name="to" value="{{ $to->toDateString() }}" class="pl-4 pr-4 py-2 bg-slate-50 border-transparent focus:border-blue-500 focus:bg-white focus:ring-0 rounded-md text-sm font-medium text-slate-700 cursor-pointer hover:bg-slate-100 transition-colors" />
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <select name="guardia_id" class="pl-4 pr-8 py-2 bg-slate-50 border-transparent focus:border-blue-500 focus:bg-white focus:ring-0 rounded-md text-sm font-medium text-slate-700 cursor-pointer hover:bg-slate-100 transition-colors">
                        <option value="">Todas las Guardias</option>
                        @foreach($guardias as $g)
                            <option value="{{ $g->id }}" {{ (int)($guardiaId ?? 0) === (int)$g->id ? 'selected' : '' }}>
                                {{ $g->name }}
                            </option>
                        @endforeach
                    </select>

                    <button type="submit" class="bg-slate-800 hover:bg-slate-700 text-white font-bold py-2 px-4 rounded-md text-sm transition shadow-sm flex items-center justify-center">
                        <i class="fas fa-filter mr-2"></i> Filtrar
                    </button>
                </div>
            </form>

            <div class="flex flex-wrap gap-2 p-2 pt-1">
                <a href="{{ route('admin.reports.replacements.export', request()->query()) }}" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2 px-4 rounded-md text-sm transition shadow-sm flex items-center">
                    <i class="fas fa-file-excel mr-2"></i> Exportar Excel
                </a>
                <a href="{{ route('admin.reports.replacements.print', request()->query()) }}" target="_blank" rel="noopener" class="bg-slate-700 hover:bg-slate-800 text-white font-bold py-2 px-4 rounded-md text-sm transition shadow-sm flex items-center">
                    <i class="fas fa-print mr-2"></i> Imprimir / PDF
                </a>
            </div>
        </div>
    </div>

    <div class="flex flex-col md:flex-row gap-2 mb-6">
        <a href="{{ route('admin.reports.index') }}" class="px-4 py-2 rounded-lg text-sm font-bold border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 transition flex items-center">
            <i class="fas fa-chart-line mr-2 text-slate-400"></i> Asistencia
        </a>
        <a href="{{ route('admin.reports.preventivas') }}" class="px-4 py-2 rounded-lg text-sm font-bold border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 transition flex items-center">
            <i class="fas fa-clipboard-list mr-2 text-slate-400"></i> Preventivas
        </a>
        <a href="{{ route('admin.reports.replacements') }}" class="px-4 py-2 rounded-lg text-sm font-bold border border-red-200 bg-red-50 text-red-800 hover:bg-red-100 transition flex items-center">
            <i class="fas fa-right-left mr-2 text-red-500"></i> Reemplazos
        </a>
        <a href="{{ route('admin.reports.drivers') }}" class="px-4 py-2 rounded-lg text-sm font-bold border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 transition flex items-center">
            <i class="fas fa-id-card mr-2 text-slate-400"></i> Conductores
        </a>
    </div>

    @isset($topReplacersByGuardia)
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden mb-10">
            <div class="bg-slate-50 px-6 py-4 border-b border-slate-200 flex justify-between items-center">
                <h2 class="text-lg font-bold text-slate-800 flex items-center uppercase tracking-wide">
                    <span class="w-2 h-6 bg-emerald-600 rounded mr-3"></span>
                    Top reemplazantes por guardia
                </h2>
                <span class="text-xs font-bold bg-white text-slate-600 px-3 py-1 rounded-full border border-slate-200 shadow-sm">
                    Top 5 por guardia
                </span>
            </div>

            <div class="p-6 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                @forelse($topReplacersByGuardia as $guardiaName => $rows)
                    <div class="border border-slate-200 rounded-xl overflow-hidden">
                        <div class="bg-slate-900 text-white px-4 py-3 flex items-center justify-between">
                            <div class="font-black uppercase tracking-wide text-sm">{{ $guardiaName }}</div>
                            <div class="text-[10px] text-slate-300 font-bold uppercase tracking-widest">Top {{ $rows->count() }}</div>
                        </div>
                        <div class="p-3 space-y-2">
                            @foreach($rows as $i => $r)
                                <div class="flex items-center justify-between bg-slate-50 border border-slate-200 rounded-lg px-3 py-2">
                                    <div class="flex items-center gap-3">
                                        <div class="w-7 h-7 rounded-lg bg-slate-900 text-white flex items-center justify-center font-black text-[11px]">{{ $i + 1 }}</div>
                                        <div class="text-sm font-bold text-slate-700">{{ $r['name'] ?? '' }}</div>
                                    </div>
                                    <div class="text-xs font-black text-emerald-700 bg-emerald-50 border border-emerald-100 rounded-full px-2 py-1">
                                        {{ $r['total'] ?? 0 }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <div class="text-sm text-slate-400">Sin datos para el rango seleccionado.</div>
                @endforelse
            </div>
        </div>
    @endisset

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-2 gap-4 mb-8">
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Total reemplazos</div>
                    <div class="text-2xl font-black text-slate-800 mt-1">{{ $kpis['total_replacements'] ?? 0 }}</div>
                </div>
                <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-700 flex items-center justify-center border border-purple-100">
                    <i class="fas fa-right-left text-lg"></i>
                </div>
            </div>
            <div class="text-[10px] text-slate-400 mt-2 font-medium">{{ $kpis['range_label'] ?? '' }}</div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Total histórico</div>
                    <div class="text-2xl font-black text-slate-800 mt-1">{{ $kpis['total_replacements_all_time'] ?? 0 }}</div>
                </div>
                <div class="w-10 h-10 rounded-xl bg-slate-900 text-white flex items-center justify-center border border-slate-800">
                    <i class="fas fa-infinity text-lg"></i>
                </div>
            </div>
            <div class="text-[10px] text-slate-400 mt-2 font-medium">Mismo filtro de guardia</div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-10">
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 lg:col-span-2">
            <div class="flex items-center justify-between mb-4">
                <div class="text-sm font-black text-slate-800 uppercase tracking-wide">Reemplazos (por día)</div>
                <div class="text-xs font-bold text-slate-400 uppercase tracking-widest">{{ $kpis['range_label'] ?? '' }}</div>
            </div>
            <div class="h-[280px]">
                <canvas id="chartTimeline"></canvas>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
            <div class="flex items-center justify-between mb-4">
                <div class="text-sm font-black text-slate-800 uppercase tracking-wide">Top reemplazantes</div>
                <div class="text-xs font-bold text-slate-400 uppercase tracking-widest">Top 10</div>
            </div>
            <div class="h-[280px]">
                <canvas id="chartTop"></canvas>
            </div>

            <div class="mt-4 space-y-2">
                @forelse($topReplacers as $i => $r)
                    <div class="flex items-center justify-between bg-slate-50 border border-slate-200 rounded-lg px-3 py-2">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-slate-900 text-white flex items-center justify-center font-black text-xs">{{ $i + 1 }}</div>
                            <div class="text-sm font-bold text-slate-700">{{ $r['name'] ?? '' }}</div>
                        </div>
                        <div class="text-xs font-black text-emerald-700 bg-emerald-50 border border-emerald-100 rounded-full px-2 py-1">
                            {{ $r['total'] ?? 0 }}
                        </div>
                    </div>
                @empty
                    <div class="text-sm text-slate-400">Sin datos para el rango seleccionado.</div>
                @endforelse
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 lg:col-span-3">
            <div class="flex items-center justify-between mb-4">
                <div class="text-sm font-black text-slate-800 uppercase tracking-wide">Reemplazos por guardia</div>
            </div>
            <div class="h-[260px]">
                <canvas id="chartByGuardia"></canvas>
            </div>

            @php
                $guardiaLabels = $charts['by_guardia']['labels'] ?? [];
                $guardiaData = $charts['by_guardia']['data'] ?? [];
            @endphp
            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2">
                @foreach($guardiaLabels as $idx => $label)
                    <div class="flex items-center justify-between bg-slate-50 border border-slate-200 rounded-lg px-3 py-2">
                        <div class="text-sm font-bold text-slate-700">{{ $label }}</div>
                        <div class="text-xs font-black text-blue-700 bg-blue-50 border border-blue-100 rounded-full px-2 py-1">
                            {{ $guardiaData[$idx] ?? 0 }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-10">
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden lg:col-span-3">
            <div class="bg-slate-50 px-6 py-4 border-b border-slate-200 flex justify-between items-center">
                <h2 class="text-lg font-bold text-slate-800 flex items-center uppercase tracking-wide">
                    <span class="w-2 h-6 bg-red-600 rounded mr-3"></span>
                    Detalle (fechas exactas)
                </h2>
                <span class="text-xs font-bold bg-white text-slate-600 px-3 py-1 rounded-full border border-slate-200 shadow-sm">
                    Últimos {{ $events->count() }}
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Día</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Guardia</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Reemplazado</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Reemplazante</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-100">
                        @forelse($events as $e)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-3 whitespace-nowrap text-sm font-bold text-slate-700">
                                    {{ optional($e->start_date)->format('d-m-Y') }}
                                </td>
                                <td class="px-6 py-3 whitespace-nowrap text-sm text-slate-600">
                                    {{ $e->firefighter?->guardia?->name ?? $e->user?->guardia?->name ?? 'Sin Asignar' }}
                                </td>
                                <td class="px-6 py-3 whitespace-nowrap text-sm text-slate-700">
                                    {{ $e->firefighter
                                        ? trim(($e->firefighter?->nombres ?? '') . ' ' . ($e->firefighter?->apellido_paterno ?? ''))
                                        : ($e->user?->name ?? '')
                                    }}
                                </td>
                                <td class="px-6 py-3 whitespace-nowrap text-sm text-slate-700">
                                    {{ $e->replacementFirefighter
                                        ? trim(($e->replacementFirefighter?->nombres ?? '') . ' ' . ($e->replacementFirefighter?->apellido_paterno ?? ''))
                                        : ($e->replacementUser?->name ?? '')
                                    }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-10 text-center text-slate-400">Sin reemplazos en el rango seleccionado.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const chartsData = @json($charts);
            const safe = (chartsData && typeof chartsData === 'object') ? chartsData : {};

            const timeline = safe.timeline && typeof safe.timeline === 'object' ? safe.timeline : { labels: [], data: [] };
            const topReplacers = safe.top_replacers && typeof safe.top_replacers === 'object' ? safe.top_replacers : { labels: [], data: [] };
            const byGuardia = safe.by_guardia && typeof safe.by_guardia === 'object' ? safe.by_guardia : { labels: [], data: [] };

            const elTimeline = document.getElementById('chartTimeline');
            if (elTimeline) {
                new Chart(elTimeline, {
                    type: 'line',
                    data: {
                        labels: timeline.labels || [],
                        datasets: [{
                            label: 'Reemplazos',
                            data: timeline.data || [],
                            borderColor: '#7c3aed',
                            backgroundColor: 'rgba(124, 58, 237, 0.12)',
                            borderWidth: 3,
                            tension: 0.25,
                            pointRadius: 2,
                            pointHoverRadius: 4,
                            fill: true,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'bottom', labels: { boxWidth: 12, boxHeight: 12 } },
                        },
                        scales: {
                            x: { grid: { display: false }, ticks: { maxTicksLimit: 10 } },
                            y: { beginAtZero: true }
                        }
                    }
                });
            }

            const elTop = document.getElementById('chartTop');
            if (elTop) {
                new Chart(elTop, {
                    type: 'bar',
                    data: {
                        labels: topReplacers.labels || [],
                        datasets: [{
                            label: 'Reemplazos',
                            data: topReplacers.data || [],
                            backgroundColor: 'rgba(16, 185, 129, 0.35)',
                            borderColor: '#059669',
                            borderWidth: 1,
                            borderRadius: 10,
                            barThickness: 18,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'bottom', labels: { boxWidth: 12, boxHeight: 12 } },
                        },
                        scales: {
                            x: { grid: { display: false } },
                            y: { beginAtZero: true }
                        }
                    }
                });
            }

            const elByGuardia = document.getElementById('chartByGuardia');
            if (elByGuardia) {
                new Chart(elByGuardia, {
                    type: 'bar',
                    data: {
                        labels: byGuardia.labels || [],
                        datasets: [{
                            label: 'Reemplazos',
                            data: byGuardia.data || [],
                            backgroundColor: 'rgba(59, 130, 246, 0.30)',
                            borderColor: '#2563eb',
                            borderWidth: 1,
                            borderRadius: 10,
                            barThickness: 20,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'bottom', labels: { boxWidth: 12, boxHeight: 12 } },
                        },
                        scales: {
                            x: { grid: { display: false } },
                            y: { beginAtZero: true }
                        }
                    }
                });
            }

        });
    </script>
@endsection
