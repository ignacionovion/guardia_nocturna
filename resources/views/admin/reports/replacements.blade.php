@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6 max-w-7xl">

    {{-- HEADER --}}
    <div class="mb-6 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 flex items-center uppercase">
                <i class="fas fa-exchange-alt mr-3 text-violet-600"></i> Reporte de Reemplazos
            </h1>
            <p class="text-slate-500 mt-1 text-sm">Análisis de reemplazos, top reemplazantes y estadísticas por guardia</p>
        </div>
        
        {{-- Botones de Exportación Profesionales --}}
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.reports.replacements.export', request()->query()) }}" 
               class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 font-extrabold text-xs uppercase tracking-widest transition-all shadow-sm hover:shadow-md">
                <i class="fas fa-file-excel text-emerald-600"></i> Excel
            </a>
            <a href="{{ route('admin.reports.replacements.print', request()->query()) }}" target="_blank"
               class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 font-extrabold text-xs uppercase tracking-widest transition-all shadow-sm hover:shadow-md">
                <i class="fas fa-file-pdf text-rose-600"></i> PDF
            </a>
        </div>
    </div>

    {{-- NAVEGACIÓN PRINCIPAL --}}
    <div class="bg-white rounded-t-lg border border-slate-200">
        <div class="flex overflow-x-auto">
            <a href="{{ route('admin.reports.attendance') }}"
               class="flex items-center gap-2 px-6 py-4 text-sm font-semibold whitespace-nowrap border-b-2 border-transparent text-slate-600 hover:text-slate-800 transition-colors">
                <i class="fas fa-calendar-check"></i> Asistencia
            </a>
            <a href="{{ route('admin.reports.preventivas') }}"
               class="flex items-center gap-2 px-6 py-4 text-sm font-semibold whitespace-nowrap border-b-2 border-transparent text-slate-600 hover:text-slate-800 transition-colors">
                <i class="fas fa-clipboard-list"></i> Preventivas
            </a>
            <a href="{{ route('admin.reports.replacements') }}"
               class="flex items-center gap-2 px-6 py-4 text-sm font-semibold whitespace-nowrap border-b-2 transition-colors text-violet-600 border-violet-600 bg-violet-50">
                <i class="fas fa-exchange-alt"></i> Reemplazos
            </a>
            <a href="{{ route('admin.reports.refuerzos') }}"
               class="flex items-center gap-2 px-6 py-4 text-sm font-semibold whitespace-nowrap border-b-2 border-transparent text-slate-600 hover:text-slate-800 transition-colors">
                <i class="fas fa-user-plus"></i> Refuerzos
            </a>
            <a href="{{ route('admin.reports.drivers') }}"
               class="flex items-center gap-2 px-6 py-4 text-sm font-semibold whitespace-nowrap border-b-2 border-transparent text-slate-600 hover:text-slate-800 transition-colors">
                <i class="fas fa-truck"></i> Conductores
            </a>
            <a href="{{ route('admin.reports.emergencies') }}"
               class="flex items-center gap-2 px-6 py-4 text-sm font-semibold whitespace-nowrap border-b-2 border-transparent text-slate-600 hover:text-slate-800 transition-colors">
                <i class="fas fa-ambulance text-red-600"></i> Emergencias
            </a>
        </div>
    </div>

    {{-- FILTROS CON SELECTS ESTANDARIZADOS --}}
    <div class="bg-white p-5 border border-t-0 border-slate-200 mb-6 rounded-b-lg shadow-sm">
        <form action="{{ route('admin.reports.replacements') }}" method="GET" class="flex flex-wrap items-end gap-4">
            
            {{-- Select Guardia - ESTANDARIZADO --}}
            <div class="min-w-[220px]">
                <label class="block text-xs font-black text-slate-500 uppercase tracking-wider mb-2">Guardia</label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none z-10">
                        <i class="fas fa-shield-alt text-slate-400 group-focus-within:text-violet-500 transition-colors"></i>
                    </div>
                    <select name="guardia_id" class="w-full pl-10 pr-10 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold text-slate-700 focus:ring-2 focus:ring-violet-500/20 focus:border-violet-500 appearance-none cursor-pointer hover:bg-white hover:border-slate-300 transition-all shadow-sm">
                        <option value="">Todas las Guardias</option>
                        @foreach($guardias as $g)
                            <option value="{{ $g->id }}" {{ ($guardiaId ?? '') == $g->id ? 'selected' : '' }}>{{ $g->name }}</option>
                        @endforeach
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-2 flex items-center pointer-events-none">
                        <div class="w-7 h-7 rounded-lg bg-slate-100 flex items-center justify-center">
                            <i class="fas fa-chevron-down text-slate-400 text-xs"></i>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Fecha Desde --}}
            <div class="min-w-[160px]">
                <label class="block text-xs font-black text-slate-500 uppercase tracking-wider mb-2">Desde</label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none z-10">
                        <i class="fas fa-calendar text-slate-400 group-focus-within:text-violet-500 transition-colors"></i>
                    </div>
                    <input type="date" name="from" value="{{ $from->format('Y-m-d') }}" class="w-full pl-10 pr-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold text-slate-700 focus:ring-2 focus:ring-violet-500/20 focus:border-violet-500 hover:bg-white hover:border-slate-300 transition-all shadow-sm">
                </div>
            </div>

            {{-- Fecha Hasta --}}
            <div class="min-w-[160px]">
                <label class="block text-xs font-black text-slate-500 uppercase tracking-wider mb-2">Hasta</label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none z-10">
                        <i class="fas fa-calendar text-slate-400 group-focus-within:text-violet-500 transition-colors"></i>
                    </div>
                    <input type="date" name="to" value="{{ $to->format('Y-m-d') }}" class="w-full pl-10 pr-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold text-slate-700 focus:ring-2 focus:ring-violet-500/20 focus:border-violet-500 hover:bg-white hover:border-slate-300 transition-all shadow-sm">
                </div>
            </div>

            {{-- Botones de Acción --}}
            <div class="flex gap-2">
                <button type="submit" class="bg-violet-600 hover:bg-violet-700 text-white font-black py-2.5 px-5 rounded-lg text-sm transition-all shadow-md hover:shadow-lg flex items-center gap-2">
                    <i class="fas fa-filter"></i> Filtrar
                </button>
                <a href="{{ route('admin.reports.replacements') }}" class="bg-slate-100 hover:bg-slate-200 text-slate-600 font-black py-2.5 px-4 rounded-lg text-sm transition-all flex items-center gap-2" title="Limpiar filtros">
                    <i class="fas fa-rotate-left"></i>
                </a>
            </div>
        </form>
    </div>

    {{-- STATS CARDS SIMPLES --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Total Reemplazos</div>
                    <div class="text-2xl font-black text-slate-800 mt-1">{{ $kpis['total_replacements'] ?? 0 }}</div>
                </div>
                <div class="w-10 h-10 rounded-xl bg-violet-100 text-violet-600 flex items-center justify-center">
                    <i class="fas fa-exchange-alt text-lg"></i>
                </div>
            </div>
            <div class="text-[10px] text-slate-400 mt-2 font-medium">{{ $kpis['range_label'] ?? '' }}</div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Reemplazantes Únicos</div>
                    <div class="text-2xl font-black text-slate-800 mt-1">{{ $kpis['unique_replacers'] ?? 0 }}</div>
                </div>
                <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center">
                    <i class="fas fa-users text-lg"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Reemplazados</div>
                    <div class="text-2xl font-black text-slate-800 mt-1">{{ $kpis['unique_replaced'] ?? 0 }}</div>
                </div>
                <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center">
                    <i class="fas fa-user-clock text-lg"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Total Histórico</div>
                    <div class="text-2xl font-black text-slate-800 mt-1">{{ $kpis['total_replacements_all_time'] ?? 0 }}</div>
                </div>
                <div class="w-10 h-10 rounded-xl bg-slate-900 text-white flex items-center justify-center">
                    <i class="fas fa-infinity text-lg"></i>
                </div>
            </div>
            <div class="text-[10px] text-slate-400 mt-2 font-medium">Mismo filtro de guardia</div>
        </div>
    </div>

    {{-- GRÁFICOS Y TOP --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        {{-- Tendencia --}}
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 lg:col-span-2">
            <div class="flex items-center justify-between mb-4">
                <div class="text-sm font-black text-slate-800 uppercase tracking-wide">Reemplazos (por día)</div>
                <div class="text-xs font-bold text-slate-400 uppercase tracking-widest">{{ $kpis['range_label'] ?? '' }}</div>
            </div>
            <div class="h-[280px]">
                <canvas id="chartTimeline"></canvas>
            </div>
        </div>

        {{-- Top Reemplazantes --}}
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
    </div>

    {{-- Por Guardia --}}
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 mb-6">
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

    {{-- TOP POR GUARDIA --}}
    @isset($topReplacersByGuardia)
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden mb-6">
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

    {{-- DETALLE --}}
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
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
                        <th class="px-6 py-3 text-center text-xs font-bold text-slate-500 uppercase tracking-wider">Estado</th>
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
                            <td class="px-6 py-3 whitespace-nowrap text-center">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold {{ ($e->status ?? 'pending') === 'approved' ? 'bg-emerald-100 text-emerald-700 border border-emerald-200' : 'bg-amber-100 text-amber-700 border border-amber-200' }}">
                                    <i class="fas {{ ($e->status ?? 'pending') === 'approved' ? 'fa-check-circle' : 'fa-clock' }} mr-1 text-[10px]"></i>
                                    {{ ucfirst($e->status ?? 'Pendiente') }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-slate-400">Sin reemplazos en el rango seleccionado.</td>
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
