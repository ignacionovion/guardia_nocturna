@extends('layouts.modern')

@section('title', 'Emergencias - ' . branding()->nombre_empresa)

@section('content')
<div class="space-y-6">
    @include('admin.reports._header')
    @include('admin.reports._tabs')

    {{-- Filtros --}}
    <x-ui.card>
        <form action="{{ route('admin.reports.emergencias') }}" method="GET" class="flex flex-wrap items-end gap-4">
            
            {{-- Select Mes --}}
            <div class="min-w-[160px]">
                <label class="form-label">Mes</label>
                <div class="relative">
                    <i class="fas fa-calendar absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <select name="month" class="form-select pl-10">
                        @foreach(range(1, 12) as $m)
                            <option value="{{ $m }}" {{ $m == ($month ?? now()->month) ? 'selected' : '' }}>
                                {{ Carbon\Carbon::create()->month($m)->locale('es')->monthName }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Select Año --}}
            <div class="min-w-[140px]">
                <label class="form-label">Año</label>
                <div class="relative">
                    <i class="fas fa-calendar-alt absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <select name="year" class="form-select pl-10">
                        @foreach(range(now()->year - 2, now()->year + 1) as $y)
                            <option value="{{ $y }}" {{ $y == ($year ?? now()->year) ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Select Guardia --}}
            <div class="min-w-[220px]">
                <label class="form-label">Guardia</label>
                <div class="relative">
                    <i class="fas fa-shield-alt absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <select name="guardia_id" class="form-select pl-10">
                        <option value="">Todas las Guardias</option>
                        @foreach($guardias ?? [] as $g)
                            <option value="{{ $g->id }}" {{ ($guardiaId ?? '') == $g->id ? 'selected' : '' }}>{{ $g->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Botón Filtrar --}}
            <div class="flex gap-2">
                <x-ui.button type="submit" variant="primary" size="md" icon="fas fa-filter">
                    Filtrar
                </x-ui.button>
                <x-ui.button variant="secondary" size="md" icon="fas fa-undo" href="{{ route('admin.reports.emergencias') }}" title="Limpiar filtros">
                </x-ui.button>
            </div>

            {{-- Botones de Exportación --}}
            <div class="ml-auto flex gap-2">
                <x-ui.button variant="success" size="md" icon="fas fa-file-excel" href="{{ route('admin.reports.emergencias.export', ['format' => 'excel'] + request()->all()) }}">
                    Excel
                </x-ui.button>
                <x-ui.button variant="danger" size="md" icon="fas fa-file-pdf" href="{{ route('admin.reports.emergencias.export', ['format' => 'pdf'] + request()->all()) }}" target="_blank">
                    PDF
                </x-ui.button>
            </div>
        </form>
    </x-ui.card>

    {{-- KPIs --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <x-ui.stat-card title="Total Emergencias" :value="$kpis['total_emergencies']" icon="fas fa-ambulance" color="red" />
        <x-ui.stat-card title="Período" :value="Carbon\Carbon::create()->month($month)->locale('es')->monthName . ' ' . $year" icon="fas fa-calendar" color="blue" />
        <x-ui.stat-card title="Guardia" :value="$kpis['guardia_filter']" icon="fas fa-shield-alt" color="cyan" />
        <x-ui.stat-card title="Claves Distintas" :value="$topKeys->count()" icon="fas fa-key" color="amber" />
    </div>

    {{-- GRÁFICOS --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        {{-- Emergencias por Guardia --}}
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800">
                <h3 class="text-sm font-bold text-slate-800 dark:text-white flex items-center">
                    <i class="fas fa-chart-bar mr-2 text-slate-500 dark:text-slate-400"></i> Emergencias por Guardia
                </h3>
            </div>
            <div class="p-5" style="height: 280px; position: relative;">
                <canvas id="chartByGuardia" style="max-height: 250px;"></canvas>
            </div>
        </div>

        {{-- Emergencias Mensuales --}}
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800">
                <h3 class="text-sm font-bold text-slate-800 dark:text-white flex items-center">
                    <i class="fas fa-chart-line mr-2 text-slate-500 dark:text-slate-400"></i> Emergencias Mensuales ({{ $year }})
                </h3>
            </div>
            <div class="p-5" style="height: 280px; position: relative;">
                <canvas id="chartMonthly" style="max-height: 250px;"></canvas>
            </div>
        </div>
    </div>

    {{-- GRÁFICOS 2 --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        {{-- Emergencias por Hora --}}
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800">
                <h3 class="text-sm font-bold text-slate-800 dark:text-white flex items-center">
                    <i class="fas fa-clock mr-2 text-slate-500 dark:text-slate-400"></i> Emergencias por Hora del Día
                </h3>
            </div>
            <div class="p-5" style="height: 280px; position: relative;">
                <canvas id="chartByHour" style="max-height: 250px;"></canvas>
            </div>
        </div>

        {{-- Top 5 Claves --}}
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800">
                <h3 class="text-sm font-bold text-slate-800 dark:text-white flex items-center">
                    <i class="fas fa-key mr-2 text-slate-500 dark:text-slate-400"></i> Top 5 Claves Más Concurridas
                </h3>
            </div>
            <div class="p-5" style="height: 280px; position: relative;">
                <canvas id="chartTopKeys" style="max-height: 250px;"></canvas>
            </div>
        </div>
    </div>

    {{-- TABLAS --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        {{-- Vehículos más utilizados --}}
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800">
                <h3 class="text-sm font-bold text-slate-800 dark:text-white flex items-center">
                    <i class="fas fa-car mr-2 text-slate-500 dark:text-slate-400"></i> Unidades Más Utilizados
                </h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Ordenado de menor a mayor</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-800">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Vehículo</th>
                            <th class="px-4 py-3 text-center text-xs font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Total</th>
                            <th class="px-4 py-3 text-center text-xs font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider">%</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @forelse($vehiclesUsed as $vehicle)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800">
                                <td class="px-4 py-3 font-medium text-slate-700 dark:text-slate-300">{{ $vehicle['vehicle'] }}</td>
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-white">
                                        {{ $vehicle['total'] }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center text-slate-500 dark:text-slate-400">
                                    @if($kpis['total_emergencies'] > 0)
                                        {{ round(($vehicle['total'] / $kpis['total_emergencies']) * 100, 1) }}%
                                    @else
                                        0%
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-4 py-8 text-center text-slate-500 dark:text-slate-400">Sin datos</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Sistema de Puntos --}}
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-700 bg-amber-50">
                <h3 class="text-sm font-bold text-amber-800 flex items-center">
                    <i class="fas fa-star mr-2 text-amber-600"></i> Sistema de Puntos
                </h3>
                <p class="text-xs text-amber-700 mt-1">10-0-1 = 1 punto | 10-4-1 = 2 puntos</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-800">
                        <tr>
                            <th class="px-3 py-3 text-left text-xs font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Clave</th>
                            <th class="px-3 py-3 text-center text-xs font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Cant</th>
                            <th class="px-3 py-3 text-center text-xs font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Pts</th>
                            <th class="px-3 py-3 text-center text-xs font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @forelse($pointsByKey->take(10) as $key)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800">
                                <td class="px-3 py-3">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-red-100 text-red-800">
                                        {{ $key['key'] }}
                                    </span>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 truncate max-w-[120px]">{{ $key['description'] }}</p>
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
                                <td colspan="4" class="px-4 py-8 text-center text-slate-500 dark:text-slate-400">Sin datos</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Top Claves --}}
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800">
                <h3 class="text-sm font-bold text-slate-800 dark:text-white flex items-center">
                    <i class="fas fa-list-ol mr-2 text-slate-500 dark:text-slate-400"></i> Top 5 Claves
                </h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-800">
                        <tr>
                            <th class="px-4 py-3 text-center text-xs font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider">#</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Clave</th>
                            <th class="px-4 py-3 text-center text-xs font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @forelse($topKeys as $index => $key)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800">
                                <td class="px-4 py-3 text-center">
                                    @if($index < 3)
                                        <i class="fas fa-medal text-lg {{ ['text-yellow-500', 'text-slate-400', 'text-amber-700'][$index] }}"></i>
                                    @else
                                        <span class="text-slate-500 dark:text-slate-400 font-medium">{{ $index + 1 }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-red-100 text-red-800">
                                        {{ $key['key'] }}
                                    </span>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 truncate max-w-[150px]">{{ $key['description'] }}</p>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-800">
                                        {{ $key['total'] }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-4 py-8 text-center text-slate-500 dark:text-slate-400">Sin datos</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Detalle por Guardia --}}
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden mb-6">
        <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800">
            <h3 class="text-sm font-bold text-slate-800 dark:text-white flex items-center">
                <i class="fas fa-shield-alt mr-2 text-slate-500 dark:text-slate-400"></i> Detalle por Guardia
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 dark:bg-slate-800">
                    <tr>
                        <th class="px-5 py-4 text-left text-xs font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Guardia</th>
                        <th class="px-5 py-4 text-center text-xs font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Total Emergencias</th>
                        <th class="px-5 py-4 text-center text-xs font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider">% del Total</th>
                        <th class="px-5 py-4 text-left text-xs font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider" style="width: 30%;">Progreso</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($statsByGuardia as $stat)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800">
                            <td class="px-5 py-4">
                                <div class="flex items-center">
                                    <i class="fas fa-shield-alt text-cyan-500 mr-3"></i>
                                    <span class="font-medium text-slate-700 dark:text-slate-300">{{ $stat['guardia'] }}</span>
                                </div>
                            </td>
                            <td class="px-5 py-4 text-center">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-red-100 text-red-800">
                                    {{ $stat['total'] }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-center">
                                @if($kpis['total_emergencies'] > 0)
                                    <span class="text-slate-600 dark:text-slate-400 font-medium">{{ round(($stat['total'] / $kpis['total_emergencies']) * 100, 1) }}%</span>
                                @else
                                    <span class="text-slate-500 dark:text-slate-400">0%</span>
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
                            <td colspan="4" class="px-5 py-8 text-center text-slate-500 dark:text-slate-400">Sin datos</td>
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
