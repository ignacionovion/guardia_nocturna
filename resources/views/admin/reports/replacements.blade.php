@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6 max-w-7xl">

    {{-- HEADER --}}
    <div class="mb-6 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 flex items-center uppercase">
                <i class="fas fa-chart-line mr-3 text-red-600"></i> Reportes
            </h1>
            <p class="text-slate-500 mt-1 text-sm">Estadísticas de asistencia, permisos, reemplazos y conductores</p>
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
               class="flex items-center gap-2 px-6 py-4 text-sm font-semibold whitespace-nowrap border-b-2 transition-colors text-red-600 border-red-600 bg-red-50">
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
                        <i class="fas fa-shield-alt text-slate-400 group-focus-within:text-red-500 transition-colors"></i>
                    </div>
                    <select name="guardia_id" class="w-full pl-10 pr-10 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold text-slate-700 focus:ring-2 focus:ring-red-500/20 focus:border-red-500 appearance-none cursor-pointer hover:bg-white hover:border-slate-300 transition-all shadow-sm">
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

            {{-- Fecha Desde - ESTANDARIZADO --}}
            <div class="min-w-[160px]">
                <label class="block text-xs font-black text-slate-500 uppercase tracking-wider mb-2">Desde</label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none z-10">
                        <i class="fas fa-calendar text-slate-400 group-focus-within:text-red-500 transition-colors"></i>
                    </div>
                    <input type="date" name="from" value="{{ $from->format('Y-m-d') }}" class="w-full pl-10 pr-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold text-slate-700 focus:ring-2 focus:ring-red-500/20 focus:border-red-500 hover:bg-white hover:border-slate-300 transition-all shadow-sm">
                </div>
            </div>

            {{-- Fecha Hasta - ESTANDARIZADO --}}
            <div class="min-w-[160px]">
                <label class="block text-xs font-black text-slate-500 uppercase tracking-wider mb-2">Hasta</label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none z-10">
                        <i class="fas fa-calendar text-slate-400 group-focus-within:text-red-500 transition-colors"></i>
                    </div>
                    <input type="date" name="to" value="{{ $to->format('Y-m-d') }}" class="w-full pl-10 pr-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold text-slate-700 focus:ring-2 focus:ring-red-500/20 focus:border-red-500 hover:bg-white hover:border-slate-300 transition-all shadow-sm">
                </div>
            </div>

            {{-- Botón Filtrar --}}
            <div class="flex gap-2">
                <button type="submit" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-slate-950 hover:bg-slate-900 text-white font-extrabold text-xs uppercase tracking-widest transition-all shadow-md hover:shadow-lg">
                    <i class="fas fa-filter"></i> Filtrar
                </button>
                <a href="{{ route('admin.reports.replacements') }}" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-extrabold text-xs uppercase tracking-widest transition-all" title="Limpiar filtros">
                    <i class="fas fa-undo"></i>
                </a>
            </div>

            {{-- Botones de Exportación Profesionales --}}
            <div class="ml-auto flex gap-2">
                <a href="{{ route('admin.reports.replacements.export', request()->query()) }}" 
                   class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 font-extrabold text-xs uppercase tracking-widest transition-all shadow-sm hover:shadow-md">
                    <i class="fas fa-file-excel text-emerald-600"></i> Excel
                </a>
                <a href="{{ route('admin.reports.replacements.print', request()->query()) }}" target="_blank"
                   class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 font-extrabold text-xs uppercase tracking-widest transition-all shadow-sm hover:shadow-md">
                    <i class="fas fa-file-pdf text-rose-600"></i> PDF
                </a>
            </div>
        </form>
    </div>

    {{-- STATS CARDS --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 bg-white border border-slate-200 p-4 mb-6 rounded-lg">
        @php
            $statCards = [
                ['label' => 'Total Reemplazos', 'value' => $kpis['total_replacements'] ?? 0, 'color' => 'purple', 'icon' => 'exchange-alt'],
                ['label' => 'Reemplazantes', 'value' => $kpis['unique_replacers'] ?? 0, 'color' => 'emerald', 'icon' => 'users'],
                ['label' => 'Reemplazados', 'value' => $kpis['unique_replaced'] ?? 0, 'color' => 'amber', 'icon' => 'user-clock'],
                ['label' => 'Histórico', 'value' => $kpis['total_replacements_all_time'] ?? 0, 'color' => 'slate', 'icon' => 'infinity'],
            ];
        @endphp
        @foreach($statCards as $card)
        <div class="bg-slate-50 rounded-lg p-3 border border-slate-100">
            <div class="flex items-center justify-between mb-1">
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">{{ $card['label'] }}</p>
                <i class="fas fa-{{ $card['icon'] }} text-{{ $card['color'] }}-400 text-xs"></i>
            </div>
            <p class="text-2xl font-bold text-{{ $card['color'] }}-600">{{ $card['value'] }}</p>
        </div>
        @endforeach
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
