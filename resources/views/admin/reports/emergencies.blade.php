@extends('layouts.app')

@section('title', 'Reporte de Emergencias')

@section('content')
<div class="container mx-auto px-4 py-6 max-w-7xl">

    {{-- HEADER --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-800 flex items-center uppercase">
            <i class="fas fa-chart-line mr-3 text-red-600"></i> Reportes
        </h1>
        <p class="text-slate-500 mt-1 text-sm">Estadísticas de asistencia, permisos, reemplazos y conductores</p>
    </div>

    {{-- NAVEGACIÓN PRINCIPAL --}}
    <div class="bg-white rounded-t-lg border border-slate-200">
        <div class="flex overflow-x-auto">
            <a href="{{ route('admin.reports.attendance') }}"
               class="flex items-center gap-2 px-6 py-4 text-sm font-semibold whitespace-nowrap border-b-2 border-transparent text-slate-600 hover:text-slate-800 transition-colors">
                <i class="fas fa-calendar-check"></i> Asistencia
            </a>
            <a href="{{ route('admin.reports.attendance', ['tab' => 'permisos']) }}"
               class="flex items-center gap-2 px-6 py-4 text-sm font-semibold whitespace-nowrap border-b-2 border-transparent text-slate-600 hover:text-slate-800 transition-colors">
                <i class="fas fa-calendar-alt"></i> Permisos
            </a>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.reports.emergencies.export', ['format' => 'excel'] + request()->all()) }}" 
                   class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 font-extrabold text-xs uppercase tracking-widest transition-all shadow-sm hover:shadow-md">
                    <i class="fas fa-file-excel text-emerald-600"></i> Excel
                </a>
                <a href="{{ route('admin.reports.emergencies.export', ['format' => 'pdf'] + request()->all()) }}" target="_blank"
                   class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 font-extrabold text-xs uppercase tracking-widest transition-all shadow-sm hover:shadow-md">
                    <i class="fas fa-file-pdf text-rose-600"></i> PDF
                </a>
            </div>
            <a href="{{ route('admin.reports.emergencies') }}"
               class="flex items-center gap-2 px-6 py-4 text-sm font-semibold whitespace-nowrap border-b-2 border-red-600 text-red-600 bg-red-50 transition-colors">
                <i class="fas fa-ambulance"></i> Emergencias
            </a>
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
            <a href="{{ route('admin.reports.replacements') }}"
               class="flex items-center gap-2 px-6 py-4 text-sm font-semibold whitespace-nowrap border-b-2 border-transparent text-slate-600 hover:text-slate-800 transition-colors">
                <i class="fas fa-exchange-alt"></i> Reemplazos
            </a>
            <a href="{{ route('admin.reports.drivers') }}"
               class="flex items-center gap-2 px-6 py-4 text-sm font-semibold whitespace-nowrap border-b-2 border-transparent text-slate-600 hover:text-slate-800 transition-colors">
                <i class="fas fa-truck"></i> Conductores
            </a>
            <a href="{{ route('admin.reports.emergencies') }}"
               class="flex items-center gap-2 px-6 py-4 text-sm font-semibold whitespace-nowrap border-b-2 border-red-600 text-red-600 bg-red-50 transition-colors">
                <i class="fas fa-ambulance"></i> Emergencias
            </a>
        </div>
    </div>

    {{-- HEADER DE EMERGENCIAS --}}
    <div class="bg-white p-5 border border-t-0 border-slate-200 mb-6 rounded-b-lg shadow-sm">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-slate-800 flex items-center">
                    <i class="fas fa-ambulance mr-3 text-red-600"></i> Reporte de Emergencias
                </h2>
                <p class="text-slate-500 mt-1 text-sm">Estadísticas de emergencias atendidas por las guardias</p>
            </div>
            
            {{-- Botones de Exportación Profesionales --}}
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.reports.emergencies.export', ['format' => 'excel'] + request()->all()) }}" 
                   class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 font-extrabold text-xs uppercase tracking-widest transition-all shadow-sm hover:shadow-md">
                    <i class="fas fa-file-excel text-emerald-600"></i> Excel
                </a>
                <a href="{{ route('admin.reports.emergencies.export', ['format' => 'pdf'] + request()->all()) }}" target="_blank"
                   class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 font-extrabold text-xs uppercase tracking-widest transition-all shadow-sm hover:shadow-md">
                    <i class="fas fa-file-pdf text-rose-600"></i> PDF
                </a>
            </div>
        </div>
    </div>
            <form method="GET" class="flex flex-wrap items-center gap-3">
                <select name="month" onchange="this.form.submit()" class="px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg text-sm font-semibold text-slate-700 focus:ring-2 focus:ring-red-500/20 focus:border-red-500">
                    @foreach(range(1, 12) as $m)
                        <option value="{{ $m }}" {{ $m == $month ? 'selected' : '' }}>
                            {{ Carbon\Carbon::create()->month($m)->locale('es')->monthName }}
                        </option>
                    @endforeach
                </select>
                <select name="year" onchange="this.form.submit()" class="px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg text-sm font-semibold text-slate-700 focus:ring-2 focus:ring-red-500/20 focus:border-red-500">
                    @foreach(range(now()->year - 2, now()->year + 1) as $y)
                        <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
                <select name="guardia_id" onchange="this.form.submit()" class="px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg text-sm font-semibold text-slate-700 focus:ring-2 focus:ring-red-500/20 focus:border-red-500">
                    <option value="">Todas las Guardias</option>
                    @foreach($guardias as $guardia)
                        <option value="{{ $guardia->id }}" {{ $guardiaId == $guardia->id ? 'selected' : '' }}>
                            {{ $guardia->name }}
                        </option>
                    @endforeach
                </select>
            </form>
        </div>
    </div>

    {{-- KPIs --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        {{-- Total Emergencias --}}
        <div class="bg-white rounded-xl border-l-4 border-red-500 shadow-sm p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0 w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-ambulance text-xl text-red-600"></i>
                </div>
                <div class="ml-4">
                    <p class="text-xs font-black text-slate-500 uppercase tracking-wider">Total Emergencias</p>
                    <p class="text-2xl font-bold text-slate-800">{{ $kpis['total_emergencies'] }}</p>
                </div>
            </div>
        </div>

        {{-- Período --}}
        <div class="bg-white rounded-xl border-l-4 border-blue-500 shadow-sm p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0 w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-calendar text-xl text-blue-600"></i>
                </div>
                <div class="ml-4">
                    <p class="text-xs font-black text-slate-500 uppercase tracking-wider">Período</p>
                    <p class="text-lg font-bold text-slate-800">{{ Carbon\Carbon::create()->month($month)->locale('es')->monthName }} {{ $year }}</p>
                </div>
            </div>
        </div>

        {{-- Guardia Filtrada --}}
        <div class="bg-white rounded-xl border-l-4 border-cyan-500 shadow-sm p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0 w-12 h-12 bg-cyan-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-shield-alt text-xl text-cyan-600"></i>
                </div>
                <div class="ml-4">
                    <p class="text-xs font-black text-slate-500 uppercase tracking-wider">Guardia</p>
                    <p class="text-lg font-bold text-slate-800">{{ $kpis['guardia_filter'] }}</p>
                </div>
            </div>
        </div>

        {{-- Claves Distintas --}}
        <div class="bg-white rounded-xl border-l-4 border-amber-500 shadow-sm p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0 w-12 h-12 bg-amber-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-key text-xl text-amber-600"></i>
                </div>
                <div class="ml-4">
                    <p class="text-xs font-black text-slate-500 uppercase tracking-wider">Claves Distintas</p>
                    <p class="text-2xl font-bold text-slate-800">{{ $topKeys->count() }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- GRÁFICOS --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        {{-- Emergencias por Guardia --}}
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-200 bg-slate-50">
                <h3 class="text-sm font-bold text-slate-800 flex items-center">
                    <i class="fas fa-chart-bar mr-2 text-slate-500"></i> Emergencias por Guardia
                </h3>
            </div>
            <div class="p-5">
                <canvas id="chartByGuardia" height="250"></canvas>
            </div>
        </div>

        {{-- Emergencias Mensuales --}}
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-200 bg-slate-50">
                <h3 class="text-sm font-bold text-slate-800 flex items-center">
                    <i class="fas fa-chart-line mr-2 text-slate-500"></i> Emergencias Mensuales ({{ $year }})
                </h3>
            </div>
            <div class="p-5">
                <canvas id="chartMonthly" height="250"></canvas>
            </div>
        </div>
    </div>

    {{-- GRÁFICOS 2 --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        {{-- Emergencias por Hora --}}
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-200 bg-slate-50">
                <h3 class="text-sm font-bold text-slate-800 flex items-center">
                    <i class="fas fa-clock mr-2 text-slate-500"></i> Emergencias por Hora del Día
                </h3>
            </div>
            <div class="p-5">
                <canvas id="chartByHour" height="250"></canvas>
            </div>
        </div>

        {{-- Top 5 Claves --}}
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-200 bg-slate-50">
                <h3 class="text-sm font-bold text-slate-800 flex items-center">
                    <i class="fas fa-key mr-2 text-slate-500"></i> Top 5 Claves Más Concurridas
                </h3>
            </div>
            <div class="p-5">
                <canvas id="chartTopKeys" height="250"></canvas>
            </div>
        </div>
    </div>

    {{-- TABLAS --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        {{-- Vehículos más utilizados --}}
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-200 bg-slate-50">
                <h3 class="text-sm font-bold text-slate-800 flex items-center">
                    <i class="fas fa-car mr-2 text-slate-500"></i> Vehículos Más Utilizados
                </h3>
                <p class="text-xs text-slate-500 mt-1">Ordenado de menor a mayor</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">Vehículo</th>
                            <th class="px-4 py-3 text-center text-xs font-bold text-slate-600 uppercase tracking-wider">Total</th>
                            <th class="px-4 py-3 text-center text-xs font-bold text-slate-600 uppercase tracking-wider">%</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @forelse($vehiclesUsed as $vehicle)
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-3 font-medium text-slate-700">{{ $vehicle['vehicle'] }}</td>
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-800">
                                        {{ $vehicle['total'] }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center text-slate-500">
                                    @if($kpis['total_emergencies'] > 0)
                                        {{ round(($vehicle['total'] / $kpis['total_emergencies']) * 100, 1) }}%
                                    @else
                                        0%
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-4 py-8 text-center text-slate-500">Sin datos</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Sistema de Puntos --}}
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-200 bg-amber-50">
                <h3 class="text-sm font-bold text-amber-800 flex items-center">
                    <i class="fas fa-star mr-2 text-amber-600"></i> Sistema de Puntos
                </h3>
                <p class="text-xs text-amber-700 mt-1">10-0-1 = 1 punto | 10-4-1 = 2 puntos</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-3 py-3 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">Clave</th>
                            <th class="px-3 py-3 text-center text-xs font-bold text-slate-600 uppercase tracking-wider">Cant</th>
                            <th class="px-3 py-3 text-center text-xs font-bold text-slate-600 uppercase tracking-wider">Pts</th>
                            <th class="px-3 py-3 text-center text-xs font-bold text-slate-600 uppercase tracking-wider">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @forelse($pointsByKey->take(10) as $key)
                            <tr class="hover:bg-slate-50">
                                <td class="px-3 py-3">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-red-100 text-red-800">
                                        {{ $key['key'] }}
                                    </span>
                                    <p class="text-xs text-slate-500 mt-1 truncate max-w-[120px]">{{ $key['description'] }}</p>
                                </td>
                                <td class="px-3 py-3 text-center">{{ $key['total'] }}</td>
                                <td class="px-3 py-3 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-amber-100 text-amber-800">
                                        {{ $key['points_per_emergency'] }}
                                    </span>
                                </td>
                                <td class="px-3 py-3 text-center font-bold text-green-600">{{ $key['total_points'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-8 text-center text-slate-500">Sin datos</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Top Claves --}}
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-200 bg-slate-50">
                <h3 class="text-sm font-bold text-slate-800 flex items-center">
                    <i class="fas fa-list-ol mr-2 text-slate-500"></i> Top 5 Claves
                </h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-center text-xs font-bold text-slate-600 uppercase tracking-wider">#</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">Clave</th>
                            <th class="px-4 py-3 text-center text-xs font-bold text-slate-600 uppercase tracking-wider">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @forelse($topKeys as $index => $key)
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-3 text-center">
                                    @if($index < 3)
                                        <i class="fas fa-medal text-lg {{ ['text-yellow-500', 'text-slate-400', 'text-amber-700'][$index] }}"></i>
                                    @else
                                        <span class="text-slate-500 font-medium">{{ $index + 1 }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-red-100 text-red-800">
                                        {{ $key['key'] }}
                                    </span>
                                    <p class="text-xs text-slate-500 mt-1 truncate max-w-[150px]">{{ $key['description'] }}</p>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-800">
                                        {{ $key['total'] }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-4 py-8 text-center text-slate-500">Sin datos</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Detalle por Guardia --}}
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden mb-6">
        <div class="px-5 py-4 border-b border-slate-200 bg-slate-50">
            <h3 class="text-sm font-bold text-slate-800 flex items-center">
                <i class="fas fa-shield-alt mr-2 text-slate-500"></i> Detalle por Guardia
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-5 py-4 text-left text-xs font-bold text-slate-600 uppercase tracking-wider">Guardia</th>
                        <th class="px-5 py-4 text-center text-xs font-bold text-slate-600 uppercase tracking-wider">Total Emergencias</th>
                        <th class="px-5 py-4 text-center text-xs font-bold text-slate-600 uppercase tracking-wider">% del Total</th>
                        <th class="px-5 py-4 text-left text-xs font-bold text-slate-600 uppercase tracking-wider" style="width: 30%;">Progreso</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($statsByGuardia as $stat)
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-4">
                                <div class="flex items-center">
                                    <i class="fas fa-shield-alt text-cyan-500 mr-3"></i>
                                    <span class="font-medium text-slate-700">{{ $stat['guardia'] }}</span>
                                </div>
                            </td>
                            <td class="px-5 py-4 text-center">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-red-100 text-red-800">
                                    {{ $stat['total'] }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-center">
                                @if($kpis['total_emergencies'] > 0)
                                    <span class="text-slate-600 font-medium">{{ round(($stat['total'] / $kpis['total_emergencies']) * 100, 1) }}%</span>
                                @else
                                    <span class="text-slate-500">0%</span>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                @if($kpis['total_emergencies'] > 0)
                                    @php
                                        $percentage = round(($stat['total'] / $kpis['total_emergencies']) * 100);
                                        $colorClass = $percentage > 50 ? 'bg-red-500' : ($percentage > 25 ? 'bg-amber-500' : 'bg-green-500');
                                    @endphp
                                    <div class="w-full bg-slate-200 rounded-full h-2.5">
                                        <div class="{{ $colorClass }} h-2.5 rounded-full" style="width: {{ $percentage }}%"></div>
                                    </div>
                                @else
                                    <div class="w-full bg-slate-200 rounded-full h-2.5">
                                        <div class="bg-slate-400 h-2.5 rounded-full" style="width: 0%"></div>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-5 py-8 text-center text-slate-500">Sin datos</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    Chart.defaults.font.family = "'Nunito', 'Inter', 'Segoe UI', sans-serif";
    Chart.defaults.color = '#64748b';

    // Chart: Emergencies by Guardia
    new Chart(document.getElementById('chartByGuardia'), {
        type: 'bar',
        data: {
            labels: {!! json_encode($charts['by_guardia']['labels']) !!},
            datasets: [{
                label: 'Emergencias',
                data: {!! json_encode($charts['by_guardia']['data']) !!},
                backgroundColor: 'rgba(239, 68, 68, 0.8)',
                borderColor: 'rgba(239, 68, 68, 1)',
                borderWidth: 1,
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1 } }
            }
        }
    });

    // Chart: Monthly
    new Chart(document.getElementById('chartMonthly'), {
        type: 'line',
        data: {
            labels: {!! json_encode($charts['monthly']['labels']) !!},
            datasets: [{
                label: 'Emergencias',
                data: {!! json_encode($charts['monthly']['data']) !!},
                borderColor: 'rgba(59, 130, 246, 1)',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                fill: true,
                tension: 0.4,
                pointRadius: 4,
                pointBackgroundColor: 'rgba(59, 130, 246, 1)'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1 } }
            }
        }
    });

    // Chart: By Hour
    new Chart(document.getElementById('chartByHour'), {
        type: 'bar',
        data: {
            labels: {!! json_encode($charts['by_hour']['labels']) !!},
            datasets: [{
                label: 'Emergencias',
                data: {!! json_encode($charts['by_hour']['data']) !!},
                backgroundColor: 'rgba(6, 182, 212, 0.8)',
                borderColor: 'rgba(6, 182, 212, 1)',
                borderWidth: 1,
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1 } },
                x: { title: { display: true, text: 'Hora del día' } }
            }
        }
    });

    // Chart: Top Keys
    new Chart(document.getElementById('chartTopKeys'), {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($charts['top_keys']['labels']) !!},
            datasets: [{
                data: {!! json_encode($charts['top_keys']['data']) !!},
                backgroundColor: [
                    'rgba(239, 68, 68, 0.8)',
                    'rgba(245, 158, 11, 0.8)',
                    'rgba(59, 130, 246, 0.8)',
                    'rgba(34, 197, 94, 0.8)',
                    'rgba(139, 92, 246, 0.8)'
                ],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                    labels: { boxWidth: 12, padding: 15 }
                }
            }
        }
    });
</script>
@endpush
