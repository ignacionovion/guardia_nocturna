@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6 max-w-7xl">

    {{-- HEADER MODERNO --}}
    <div class="mb-8">
        <div class="flex items-center gap-4 mb-2">
            <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-sky-500 to-blue-600 shadow-lg shadow-sky-500/30 flex items-center justify-center">
                <i class="fas fa-user-plus text-white text-2xl"></i>
            </div>
            <div>
                <h1 class="text-3xl font-black text-slate-800 uppercase tracking-tight">Reporte de Refuerzos</h1>
                <p class="text-slate-500 text-sm font-medium">Análisis completo de refuerzos por guardia, período y voluntario</p>
            </div>
        </div>
    </div>

    {{-- NAVEGACIÓN TIPO TABS MODERNOS --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden mb-6">
        <div class="flex overflow-x-auto scrollbar-hide">
            <a href="{{ route('admin.reports.attendance') }}" class="flex items-center gap-2 px-6 py-4 text-sm font-bold whitespace-nowrap border-b-2 transition-all hover:bg-slate-50 text-slate-600 border-transparent">
                <i class="fas fa-calendar-check"></i> Asistencia
            </a>
            <a href="{{ route('admin.reports.replacements') }}" class="flex items-center gap-2 px-6 py-4 text-sm font-bold whitespace-nowrap border-b-2 transition-all hover:bg-slate-50 text-slate-600 border-transparent">
                <i class="fas fa-exchange-alt"></i> Reemplazos
            </a>
            <button class="flex items-center gap-2 px-6 py-4 text-sm font-bold whitespace-nowrap border-b-2 transition-all bg-sky-50 text-sky-600 border-sky-500 cursor-default">
                <i class="fas fa-user-plus"></i> Refuerzos
            </button>
            <a href="{{ route('admin.reports.drivers') }}" class="flex items-center gap-2 px-6 py-4 text-sm font-bold whitespace-nowrap border-b-2 transition-all hover:bg-slate-50 text-slate-600 border-transparent">
                <i class="fas fa-truck"></i> Conductores
            </a>
            <a href="{{ route('admin.reports.emergencies') }}" class="flex items-center gap-2 px-6 py-4 text-sm font-bold whitespace-nowrap border-b-2 transition-all hover:bg-slate-50 text-slate-600 border-transparent">
                <i class="fas fa-ambulance text-red-500"></i> Emergencias
            </a>
        </div>
    </div>

    {{-- FILTROS PROFESIONALES CON SELECTS ESTANDARIZADOS --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 mb-6">
        <form action="{{ route('admin.reports.refuerzos') }}" method="GET" class="flex flex-wrap items-end gap-4">
            
            {{-- Select Guardia - ESTANDARIZADO --}}
            <div class="min-w-[220px]">
                <label class="block text-xs font-black text-slate-500 uppercase tracking-wider mb-2">Guardia</label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none z-10">
                        <i class="fas fa-shield-alt text-slate-400 group-focus-within:text-sky-500 transition-colors"></i>
                    </div>
                    <select name="guardia_id" class="w-full pl-10 pr-10 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold text-slate-700 focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 appearance-none cursor-pointer hover:bg-white hover:border-slate-300 transition-all shadow-sm">
                        <option value="">Todas las Guardias</option>
                        @foreach($guardias ?? [] as $g)
                            <option value="{{ $g->id }}" {{ ($guardiaId ?? '') == $g->id ? 'selected' : '' }}>{{ $g->name }}</option>
                        @endforeach
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                        <div class="w-6 h-6 rounded-lg bg-slate-100 flex items-center justify-center">
                            <i class="fas fa-chevron-down text-slate-400 text-xs"></i>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Select Período - ESTANDARIZADO --}}
            <div class="min-w-[200px]">
                <label class="block text-xs font-black text-slate-500 uppercase tracking-wider mb-2">Período</label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none z-10">
                        <i class="fas fa-calendar-alt text-slate-400 group-focus-within:text-sky-500 transition-colors"></i>
                    </div>
                    <select name="periodo" class="w-full pl-10 pr-10 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold text-slate-700 focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 appearance-none cursor-pointer hover:bg-white hover:border-slate-300 transition-all shadow-sm">
                        <option value="7" {{ ($periodo ?? 30) == 7 ? 'selected' : '' }}>Últimos 7 días</option>
                        <option value="14" {{ ($periodo ?? 30) == 14 ? 'selected' : '' }}>Últimos 14 días</option>
                        <option value="30" {{ ($periodo ?? 30) == 30 ? 'selected' : '' }}>Últimos 30 días</option>
                        <option value="90" {{ ($periodo ?? 30) == 90 ? 'selected' : '' }}>Últimos 3 meses</option>
                        <option value="180" {{ ($periodo ?? 30) == 180 ? 'selected' : '' }}>Últimos 6 meses</option>
                        <option value="365" {{ ($periodo ?? 30) == 365 ? 'selected' : '' }}>Último año</option>
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                        <div class="w-6 h-6 rounded-lg bg-slate-100 flex items-center justify-center">
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
                        <i class="fas fa-calendar text-slate-400 group-focus-within:text-sky-500 transition-colors"></i>
                    </div>
                    <input type="date" name="from" value="{{ $from->format('Y-m-d') }}" class="w-full pl-10 pr-3 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold text-slate-700 focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 hover:bg-white hover:border-slate-300 transition-all shadow-sm">
                </div>
            </div>

            {{-- Fecha Hasta - ESTANDARIZADO --}}
            <div class="min-w-[160px]">
                <label class="block text-xs font-black text-slate-500 uppercase tracking-wider mb-2">Hasta</label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none z-10">
                        <i class="fas fa-calendar text-slate-400 group-focus-within:text-sky-500 transition-colors"></i>
                    </div>
                    <input type="date" name="to" value="{{ $to->format('Y-m-d') }}" class="w-full pl-10 pr-3 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold text-slate-700 focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 hover:bg-white hover:border-slate-300 transition-all shadow-sm">
                </div>
            </div>

            {{-- Botones de Acción --}}
            <div class="flex gap-2">
                <button type="submit" class="bg-sky-600 hover:bg-sky-700 text-white font-black py-3 px-6 rounded-xl text-sm transition-all shadow-lg shadow-sky-500/30 hover:shadow-sky-500/50 flex items-center gap-2 uppercase tracking-wider">
                    <i class="fas fa-filter"></i> Filtrar
                </button>
                <a href="{{ route('admin.reports.refuerzos') }}" class="bg-slate-100 hover:bg-slate-200 text-slate-600 font-black py-3 px-4 rounded-xl text-sm transition-all flex items-center gap-2" title="Limpiar filtros">
                    <i class="fas fa-rotate-left"></i>
                </a>
            </div>

            {{-- Botones de Exportación --}}
            <div class="ml-auto flex gap-2">
                <a href="{{ route('admin.reports.refuerzos.export', ['format' => 'excel'] + request()->all()) }}" class="bg-emerald-600 hover:bg-emerald-700 text-white font-black py-3 px-4 rounded-xl text-sm transition-all shadow-lg shadow-emerald-500/30 hover:shadow-emerald-500/50 flex items-center gap-2">
                    <i class="fas fa-file-excel"></i> Excel
                </a>
                <a href="{{ route('admin.reports.refuerzos.export', ['format' => 'pdf'] + request()->all()) }}" target="_blank" class="bg-rose-600 hover:bg-rose-700 text-white font-black py-3 px-4 rounded-xl text-sm transition-all shadow-lg shadow-rose-500/30 hover:shadow-rose-500/50 flex items-center gap-2">
                    <i class="fas fa-file-pdf"></i> PDF
                </a>
            </div>
        </form>
    </div>

    {{-- STATS CARDS PREMIUM --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        @php
            $statCards = [
                ['label' => 'Total Refuerzos', 'value' => $totalRefuerzos ?? 0, 'color' => 'sky', 'icon' => 'user-plus', 'trend' => '+12%'],
                ['label' => 'Guardias Activas', 'value' => $guardiasActivas ?? 0, 'color' => 'emerald', 'icon' => 'shield-alt', 'trend' => null],
                ['label' => 'Voluntarios Únicos', 'value' => $voluntariosUnicos ?? 0, 'color' => 'violet', 'icon' => 'users', 'trend' => '+5%'],
                ['label' => 'Promedio/Guardia', 'value' => $promedioPorGuardia ?? 0, 'color' => 'amber', 'icon' => 'chart-line', 'trend' => '-3%'],
            ];
        @endphp
        @foreach($statCards as $card)
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm hover:shadow-md transition-all group">
            <div class="flex items-start justify-between mb-4">
                <div class="w-12 h-12 rounded-xl bg-{{ $card['color'] }}-100 text-{{ $card['color'] }}-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <i class="fas fa-{{ $card['icon'] }} text-xl"></i>
                </div>
                @if($card['trend'])
                <span class="text-xs font-bold {{ str_starts_with($card['trend'], '+') ? 'text-emerald-600' : 'text-rose-600' }} bg-slate-50 px-2 py-1 rounded-lg">
                    {{ $card['trend'] }}
                </span>
                @endif
            </div>
            <div class="text-3xl font-black text-slate-800 mb-1">{{ $card['value'] }}</div>
            <div class="text-xs font-bold text-slate-400 uppercase tracking-wider">{{ $card['label'] }}</div>
        </div>
        @endforeach
    </div>

    {{-- GRÁFICO PRINCIPAL: TENDENCIA DE REFUERZOS --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 mb-8">
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-sky-100 text-sky-600 flex items-center justify-center">
                    <i class="fas fa-chart-area text-lg"></i>
                </div>
                <div>
                    <h3 class="text-lg font-black text-slate-800 uppercase tracking-wide">Tendencia de Refuerzos</h3>
                    <p class="text-xs text-slate-500">Evolución diaria en el período seleccionado</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-xs font-bold text-slate-400 uppercase">Período:</span>
                <span class="text-sm font-bold text-slate-700">{{ $from->format('d/m/Y') }} — {{ $to->format('d/m/Y') }}</span>
            </div>
        </div>
        
        {{-- Gráfico de Líneas Canvas --}}
        <div class="relative h-80 w-full">
            <canvas id="refuerzosChart" class="w-full h-full"></canvas>
        </div>
        
        {{-- Leyenda --}}
        <div class="flex items-center justify-center gap-6 mt-4 pt-4 border-t border-slate-100">
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 rounded bg-sky-500"></div>
                <span class="text-xs font-semibold text-slate-600">Total Refuerzos</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 rounded bg-emerald-400"></div>
                <span class="text-xs font-semibold text-slate-600">Promedio Móvil (7 días)</span>
            </div>
        </div>
    </div>

    {{-- SECCIÓN: ANÁLISIS POR GUARDIA --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        
        {{-- Gráfico: Refuerzos por Guardia --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 rounded-xl bg-violet-100 text-violet-600 flex items-center justify-center">
                    <i class="fas fa-chart-pie text-lg"></i>
                </div>
                <div>
                    <h3 class="text-lg font-black text-slate-800 uppercase tracking-wide">Por Guardia</h3>
                    <p class="text-xs text-slate-500">Distribución de refuerzos</p>
                </div>
            </div>
            <div class="relative h-64">
                <canvas id="guardiasChart"></canvas>
            </div>
        </div>

        {{-- Ranking: Top Refuerzos --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center">
                    <i class="fas fa-trophy text-lg"></i>
                </div>
                <div>
                    <h3 class="text-lg font-black text-slate-800 uppercase tracking-wide">Top Refuerzos</h3>
                    <p class="text-xs text-slate-500">Voluntarios con más refuerzos</p>
                </div>
            </div>
            
            <div class="space-y-3">
                @foreach($topRefuerzos ?? [] as $index => $refuerzo)
                <div class="flex items-center gap-3 p-3 rounded-xl {{ $index < 3 ? 'bg-gradient-to-r from-amber-50 to-transparent border border-amber-100' : 'bg-slate-50' }}">
                    <div class="w-8 h-8 rounded-lg {{ $index == 0 ? 'bg-amber-400 text-white' : ($index == 1 ? 'bg-slate-300 text-white' : ($index == 2 ? 'bg-orange-400 text-white' : 'bg-slate-200 text-slate-600')) }} font-black text-sm flex items-center justify-center">
                        {{ $index + 1 }}
                    </div>
                    <div class="w-10 h-10 rounded-full bg-sky-100 text-sky-700 flex items-center justify-center font-bold text-sm">
                        {{ substr($refuerzo['nombres'] ?? 'NA', 0, 1) }}{{ substr($refuerzo['apellido'] ?? 'A', 0, 1) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="font-bold text-slate-800 text-sm truncate">{{ $refuerzo['nombre'] ?? 'Sin nombre' }}</div>
                        <div class="text-xs text-slate-500">{{ $refuerzo['guardia'] ?? 'Sin guardia' }}</div>
                    </div>
                    <div class="text-right">
                        <div class="text-lg font-black text-sky-600">{{ $refuerzo['total'] ?? 0 }}</div>
                        <div class="text-[10px] text-slate-400 uppercase">refuerzos</div>
                    </div>
                </div>
                @empty
                <div class="text-center py-8 text-slate-400">
                    <i class="fas fa-inbox text-3xl mb-2"></i>
                    <p class="text-sm">No hay datos de refuerzos</p>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- SECCIÓN: DETALLE POR DÍA --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden mb-8">
        <div class="p-6 border-b border-slate-200 bg-gradient-to-r from-sky-50 to-white">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-sky-100 text-sky-600 flex items-center justify-center">
                        <i class="fas fa-list-alt text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-black text-slate-800 uppercase tracking-wide">Detalle por Día</h3>
                        <p class="text-xs text-slate-500">Registro histórico de refuerzos</p>
                    </div>
                </div>
                <span class="text-sm font-bold text-sky-700 bg-sky-100 px-3 py-1 rounded-full">{{ count($detallePorDia ?? []) }} registros</span>
            </div>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="text-left py-4 px-4 font-bold text-slate-600 text-xs uppercase tracking-wider">Fecha</th>
                        <th class="text-left py-4 px-4 font-bold text-slate-600 text-xs uppercase tracking-wider">Guardia</th>
                        <th class="text-left py-4 px-4 font-bold text-slate-600 text-xs uppercase tracking-wider">Voluntario</th>
                        <th class="text-center py-4 px-4 font-bold text-slate-600 text-xs uppercase tracking-wider">Turno</th>
                        <th class="text-center py-4 px-4 font-bold text-slate-600 text-xs uppercase tracking-wider">Estado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($detallePorDia ?? [] as $registro)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="py-4 px-4">
                            <div class="font-bold text-slate-800">{{ $registro['fecha'] ?? 'N/A' }}</div>
                            <div class="text-xs text-slate-500">{{ $registro['dia_semana'] ?? '' }}</div>
                        </td>
                        <td class="py-4 px-4">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-violet-100 text-violet-700 font-bold text-xs">
                                <i class="fas fa-shield-alt text-[10px]"></i>
                                {{ $registro['guardia'] ?? 'N/A' }}
                            </span>
                        </td>
                        <td class="py-4 px-4">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-full bg-sky-100 text-sky-700 flex items-center justify-center font-bold text-xs">
                                    {{ substr($registro['nombres'] ?? 'N', 0, 1) }}{{ substr($registro['apellido'] ?? 'A', 0, 1) }}
                                </div>
                                <div>
                                    <div class="font-bold text-slate-800 text-xs">{{ $registro['nombre'] ?? 'Sin nombre' }}</div>
                                    <div class="text-[10px] text-slate-500">{{ $registro['rut'] ?? '' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="py-4 px-4 text-center">
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 font-bold text-xs">
                                <i class="fas fa-clock text-[10px]"></i>
                                {{ $registro['turno'] ?? 'N/A' }}
                            </span>
                        </td>
                        <td class="py-4 px-4 text-center">
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg {{ ($registro['asistencia'] ?? '') === 'cumplido' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }} font-bold text-xs">
                                <i class="fas {{ ($registro['asistencia'] ?? '') === 'cumplido' ? 'fa-check-circle' : 'fa-question-circle' }} text-[10px]"></i>
                                {{ ucfirst($registro['asistencia'] ?? 'Pendiente') }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-12 text-center text-slate-400">
                            <i class="fas fa-inbox text-4xl mb-3 block"></i>
                            <p class="text-sm font-medium">No hay registros de refuerzos en este período</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        {{-- Paginación --}}
        @if(isset($detallePorDia) && count($detallePorDia) > 0)
        <div class="p-4 border-t border-slate-200 bg-slate-50 flex items-center justify-between">
            <span class="text-xs text-slate-500">Mostrando {{ count($detallePorDia) }} registros</span>
            <div class="flex gap-2">
                <button class="px-3 py-1.5 rounded-lg bg-white border border-slate-200 text-slate-600 text-xs font-bold hover:bg-slate-50 disabled:opacity-50" disabled>
                    <i class="fas fa-chevron-left"></i>
                </button>
                <button class="px-3 py-1.5 rounded-lg bg-white border border-slate-200 text-slate-600 text-xs font-bold hover:bg-slate-50 disabled:opacity-50" disabled>
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>
        @endif
    </div>

    {{-- SECCIÓN: INSIGHTS Y ANÁLISIS --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
        
        {{-- Día con más refuerzos --}}
        <div class="bg-gradient-to-br from-sky-500 to-blue-600 rounded-2xl p-6 text-white shadow-lg shadow-sky-500/30">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center">
                    <i class="fas fa-calendar-star text-lg"></i>
                </div>
                <span class="text-xs font-bold uppercase tracking-wider opacity-80">Día Peak</span>
            </div>
            <div class="text-3xl font-black mb-1">{{ $diaPeak['cantidad'] ?? 0 }}</div>
            <div class="text-sm font-medium opacity-90">{{ $diaPeak['fecha'] ?? 'Sin datos' }}</div>
            <div class="text-xs opacity-70 mt-1">Mayor cantidad de refuerzos en un día</div>
        </div>

        {{-- Guardia más solicitada --}}
        <div class="bg-gradient-to-br from-violet-500 to-purple-600 rounded-2xl p-6 text-white shadow-lg shadow-violet-500/30">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center">
                    <i class="fas fa-shield-heart text-lg"></i>
                </div>
                <span class="text-xs font-bold uppercase tracking-wider opacity-80">Guardia Top</span>
            </div>
            <div class="text-3xl font-black mb-1">{{ $guardiaTop['total'] ?? 0 }}</div>
            <div class="text-sm font-medium opacity-90">{{ $guardiaTop['nombre'] ?? 'Sin datos' }}</div>
            <div class="text-xs opacity-70 mt-1">Guardia con más refuerzos recibidos</div>
        </div>

        {{-- Eficiencia --}}
        <div class="bg-gradient-to-br from-emerald-500 to-teal-600 rounded-2xl p-6 text-white shadow-lg shadow-emerald-500/30">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center">
                    <i class="fas fa-chart-line text-lg"></i>
                </div>
                <span class="text-xs font-bold uppercase tracking-wider opacity-80">Eficiencia</span>
            </div>
            <div class="text-3xl font-black mb-1">{{ $eficienciaRefuerzos ?? 0 }}%</div>
            <div class="text-sm font-medium opacity-90">Tasa de cumplimiento</div>
            <div class="text-xs opacity-70 mt-1">Refuerzos que asistieron vs. total</div>
        </div>
    </div>

</div>

{{-- Chart.js para gráficos dinámicos --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Gráfico de Tendencia (Línea)
    const ctxRefuerzos = document.getElementById('refuerzosChart').getContext('2d');
    new Chart(ctxRefuerzos, {
        type: 'line',
        data: {
            labels: @json($trendLabels ?? []),
            datasets: [{
                label: 'Refuerzos',
                data: @json($trendData ?? []),
                borderColor: '#0ea5e9',
                backgroundColor: 'rgba(14, 165, 233, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointRadius: 4,
                pointBackgroundColor: '#0ea5e9',
                pointBorderColor: '#fff',
                pointBorderWidth: 2
            }, {
                label: 'Promedio Móvil',
                data: @json($trendAvg ?? []),
                borderColor: '#34d399',
                backgroundColor: 'transparent',
                borderWidth: 2,
                borderDash: [5, 5],
                tension: 0.4,
                pointRadius: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: '#f1f5f9' },
                    ticks: { font: { size: 11 }, color: '#94a3b8' }
                },
                x: {
                    grid: { display: false },
                    ticks: { font: { size: 11 }, color: '#94a3b8', maxRotation: 45 }
                }
            }
        }
    });

    // Gráfico por Guardia (Doughnut)
    const ctxGuardias = document.getElementById('guardiasChart').getContext('2d');
    new Chart(ctxGuardias, {
        type: 'doughnut',
        data: {
            labels: @json($guardiaLabels ?? []),
            datasets: [{
                data: @json($guardiaData ?? []),
                backgroundColor: [
                    '#0ea5e9', '#8b5cf6', '#f59e0b', '#10b981', '#ef4444', '#64748b'
                ],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '70%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { usePointStyle: true, padding: 20, font: { size: 11 } }
                }
            }
        }
    });
</script>
@endsection
