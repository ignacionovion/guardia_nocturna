@extends('layouts.modern')

@section('title', 'Reportes - ' . branding()->nombre_empresa)
@section('page-title', 'Reportes')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Reportes y Estadísticas</h1>
            <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">Análisis de operaciones y métricas del cuartel</p>
        </div>
        <div class="flex items-center gap-3">
            <select class="px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-slate-900 dark:focus:ring-white">
                <option value="mes">Este mes</option>
                <option value="semana">Esta semana</option>
                <option value="trimestre">Este trimestre</option>
                <option value="año">Este año</option>
            </select>
            <button class="px-4 py-2.5 rounded-xl bg-white dark:bg-slate-900 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-sm font-medium hover:bg-white dark:hover:bg-slate-800 dark:hover:bg-slate-700 transition-colors">
                <i class="fas fa-download mr-2"></i>
                Exportar
            </button>
        </div>
    </div>

    {{-- Key Metrics --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl p-5 text-white">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center">
                    <i class="fas fa-shield-halved"></i>
                </div>
                <span class="text-xs font-medium bg-white/20 px-2 py-1 rounded-lg">+12%</span>
            </div>
            <p class="text-3xl font-bold">{{ $guardiasCompletadas ?? 28 }}</p>
            <p class="text-blue-100 text-sm mt-1">Guardias completadas</p>
        </div>
        
        <div class="bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-2xl p-5 text-white">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center">
                    <i class="fas fa-user-check"></i>
                </div>
                <span class="text-xs font-medium bg-white/20 px-2 py-1 rounded-lg">96%</span>
            </div>
            <p class="text-3xl font-bold">{{ $asistenciaPromedio ?? 96 }}%</p>
            <p class="text-emerald-100 text-sm mt-1">Asistencia promedio</p>
        </div>
        
        <div class="bg-gradient-to-br from-red-500 to-red-600 rounded-2xl p-5 text-white">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center">
                    <i class="fas fa-truck-medical"></i>
                </div>
                <span class="text-xs font-medium bg-white/20 px-2 py-1 rounded-lg">-8%</span>
            </div>
            <p class="text-3xl font-bold">{{ $emergenciasAtendidas ?? 47 }}</p>
            <p class="text-red-100 text-sm mt-1">Emergencias atendidas</p>
        </div>
        
        <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl p-5 text-white">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center">
                    <i class="fas fa-bed"></i>
                </div>
                <span class="text-xs font-medium bg-white/20 px-2 py-1 rounded-lg">85%</span>
            </div>
            <p class="text-3xl font-bold">{{ $ocupacionCamas ?? 85 }}%</p>
            <p class="text-purple-100 text-sm mt-1">Ocupación de camas</p>
        </div>
    </div>

    {{-- Charts Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Asistencia por Guardia --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-700 dark:border-slate-800 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 dark:border-slate-800 flex items-center justify-between">
                <div>
                    <h2 class="font-semibold text-slate-900 dark:text-white">Asistencia por Guardia</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Últimos 30 días</p>
                </div>
                <div class="flex items-center gap-4 text-xs">
                    <div class="flex items-center gap-1.5">
                        <span class="w-3 h-3 rounded-full bg-red-500"></span>
                        <span class="text-slate-500 dark:text-slate-400">G1</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="w-3 h-3 rounded-full bg-blue-500"></span>
                        <span class="text-slate-500 dark:text-slate-400">G2</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
                        <span class="text-slate-500 dark:text-slate-400">G3</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="w-3 h-3 rounded-full bg-purple-500"></span>
                        <span class="text-slate-500 dark:text-slate-400">G4</span>
                    </div>
                </div>
            </div>
            <div class="p-6">
                {{-- Chart Placeholder --}}
                <div class="h-64 flex items-end justify-between gap-2">
                    @php
                    $guardiaData = [
                        ['g1' => 95, 'g2' => 88, 'g3' => 92, 'g4' => 90],
                        ['g1' => 98, 'g2' => 92, 'g3' => 88, 'g4' => 94],
                        ['g1' => 92, 'g2' => 95, 'g3' => 90, 'g4' => 88],
                        ['g1' => 96, 'g2' => 90, 'g3' => 94, 'g4' => 92],
                        ['g1' => 94, 'g2' => 93, 'g3' => 96, 'g4' => 90],
                        ['g1' => 97, 'g2' => 91, 'g3' => 93, 'g4' => 95],
                    ];
                    $semanas = ['S1', 'S2', 'S3', 'S4', 'S5', 'S6'];
                    @endphp
                    @foreach($guardiaData as $i => $data)
                    <div class="flex-1 flex flex-col items-center gap-1">
                        <div class="w-full flex items-end justify-center gap-0.5 h-52">
                            <div class="w-2 bg-red-500 rounded-t" style="height: {{ $data['g1'] * 0.52 }}%"></div>
                            <div class="w-2 bg-blue-500 rounded-t" style="height: {{ $data['g2'] * 0.52 }}%"></div>
                            <div class="w-2 bg-emerald-500 rounded-t" style="height: {{ $data['g3'] * 0.52 }}%"></div>
                            <div class="w-2 bg-purple-500 rounded-t" style="height: {{ $data['g4'] * 0.52 }}%"></div>
                        </div>
                        <span class="text-[10px] text-slate-400 dark:text-slate-500 dark:text-slate-400">{{ $semanas[$i] }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Emergencias por Tipo --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-700 dark:border-slate-800 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 dark:border-slate-800">
                <h2 class="font-semibold text-slate-900 dark:text-white">Emergencias por Tipo</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Distribución este mes</p>
            </div>
            <div class="p-6">
                <div class="flex items-center justify-center h-64">
                    {{-- Donut Chart Placeholder --}}
                    <div class="relative w-48 h-48">
                        <svg class="w-full h-full transform -rotate-90" viewBox="0 0 100 100">
                            <circle cx="50" cy="50" r="40" fill="none" stroke="#e2e8f0" stroke-width="12" class="dark:stroke-slate-700"/>
                            <circle cx="50" cy="50" r="40" fill="none" stroke="#ef4444" stroke-width="12" stroke-dasharray="100.53 251.33" stroke-linecap="round"/>
                            <circle cx="50" cy="50" r="40" fill="none" stroke="#f59e0b" stroke-width="12" stroke-dasharray="62.83 251.33" stroke-dashoffset="-100.53" stroke-linecap="round"/>
                            <circle cx="50" cy="50" r="40" fill="none" stroke="#3b82f6" stroke-width="12" stroke-dasharray="50.27 251.33" stroke-dashoffset="-163.36" stroke-linecap="round"/>
                            <circle cx="50" cy="50" r="40" fill="none" stroke="#10b981" stroke-width="12" stroke-dasharray="37.70 251.33" stroke-dashoffset="-213.63" stroke-linecap="round"/>
                        </svg>
                        <div class="absolute inset-0 flex flex-col items-center justify-center">
                            <span class="text-3xl font-bold text-slate-900 dark:text-white">47</span>
                            <span class="text-xs text-slate-500 dark:text-slate-400">Total</span>
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3 mt-4">
                    <div class="flex items-center gap-2 p-2 rounded-lg bg-white dark:bg-slate-800/50">
                        <span class="w-3 h-3 rounded-full bg-red-500"></span>
                        <span class="text-sm text-slate-600 dark:text-slate-400">Incendios</span>
                        <span class="ml-auto font-semibold text-slate-900 dark:text-white">19</span>
                    </div>
                    <div class="flex items-center gap-2 p-2 rounded-lg bg-white dark:bg-slate-800/50">
                        <span class="w-3 h-3 rounded-full bg-amber-500"></span>
                        <span class="text-sm text-slate-600 dark:text-slate-400">Rescates</span>
                        <span class="ml-auto font-semibold text-slate-900 dark:text-white">12</span>
                    </div>
                    <div class="flex items-center gap-2 p-2 rounded-lg bg-white dark:bg-slate-800/50">
                        <span class="w-3 h-3 rounded-full bg-blue-500"></span>
                        <span class="text-sm text-slate-600 dark:text-slate-400">Accidentes</span>
                        <span class="ml-auto font-semibold text-slate-900 dark:text-white">9</span>
                    </div>
                    <div class="flex items-center gap-2 p-2 rounded-lg bg-white dark:bg-slate-800/50">
                        <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
                        <span class="text-sm text-slate-600 dark:text-slate-400">Otros</span>
                        <span class="ml-auto font-semibold text-slate-900 dark:text-white">7</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Activity & Stats --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Actividad Mensual --}}
        <div class="lg:col-span-2 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-700 dark:border-slate-800 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 dark:border-slate-800">
                <h2 class="font-semibold text-slate-900 dark:text-white">Actividad Mensual</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Guardias y emergencias por día</p>
            </div>
            <div class="p-6">
                {{-- Line Chart Placeholder --}}
                <div class="h-48 flex items-end gap-1">
                    @php
                    $actividadDiaria = [3, 5, 2, 4, 6, 3, 2, 4, 5, 3, 2, 4, 6, 5, 3, 4, 2, 5, 3, 4, 6, 3, 2, 5, 4, 3, 5, 4, 3, 2];
                    @endphp
                    @foreach($actividadDiaria as $i => $valor)
                    <div class="flex-1 flex flex-col items-center">
                        <div class="w-full bg-gradient-to-t from-blue-500 to-blue-400 rounded-t opacity-80 hover:opacity-100 transition-opacity cursor-pointer" 
                             style="height: {{ $valor * 15 }}px"
                             title="Día {{ $i + 1 }}: {{ $valor }} eventos"></div>
                    </div>
                    @endforeach
                </div>
                <div class="flex items-center justify-between mt-4 text-xs text-slate-400 dark:text-slate-500 dark:text-slate-400">
                    <span>1</span>
                    <span>5</span>
                    <span>10</span>
                    <span>15</span>
                    <span>20</span>
                    <span>25</span>
                    <span>30</span>
                </div>
            </div>
        </div>

        {{-- Top Voluntarios --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-700 dark:border-slate-800 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 dark:border-slate-800">
                <h2 class="font-semibold text-slate-900 dark:text-white">Top Voluntarios</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Mayor participación este mes</p>
            </div>
            <div class="p-4">
                <div class="space-y-3">
                    @php
                    $topVoluntarios = [
                        ['nombre' => 'Juan Pérez', 'numero' => '15', 'guardias' => 8, 'emergencias' => 12],
                        ['nombre' => 'María González', 'numero' => '23', 'guardias' => 7, 'emergencias' => 10],
                        ['nombre' => 'Carlos Muñoz', 'numero' => '45', 'guardias' => 7, 'emergencias' => 8],
                        ['nombre' => 'Ana Silva', 'numero' => '67', 'guardias' => 6, 'emergencias' => 9],
                        ['nombre' => 'Roberto Díaz', 'numero' => '12', 'guardias' => 6, 'emergencias' => 7],
                    ];
                    $medals = ['🥇', '🥈', '🥉', '', ''];
                    @endphp
                    @foreach($topVoluntarios as $i => $v)
                    <div class="flex items-center gap-3 p-3 rounded-xl {{ $i < 3 ? 'bg-white dark:bg-slate-800/50' : '' }}">
                        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-slate-700 to-slate-900 dark:from-slate-200 dark:to-slate-400 flex items-center justify-center text-white dark:text-slate-900 text-xs font-bold">
                            {{ strtoupper(substr($v['nombre'], 0, 1)) }}{{ strtoupper(substr(explode(' ', $v['nombre'])[1], 0, 1)) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-1">
                                <span class="font-medium text-slate-900 dark:text-white text-sm truncate">{{ $v['nombre'] }}</span>
                                @if($medals[$i])
                                <span class="text-sm">{{ $medals[$i] }}</span>
                                @endif
                            </div>
                            <div class="text-xs text-slate-500 dark:text-slate-400">
                                {{ $v['guardias'] }} guardias · {{ $v['emergencias'] }} emergencias
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-lg font-bold text-slate-900 dark:text-white">{{ $v['guardias'] + $v['emergencias'] }}</div>
                            <div class="text-[10px] text-slate-400 uppercase">puntos</div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Detailed Stats Table --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-700 dark:border-slate-800 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 dark:border-slate-800 flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-slate-900 dark:text-white">Resumen por Guardia</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Estadísticas detalladas del mes</p>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-white dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-700">
                        <th class="px-6 py-3 text-left text-[10px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Guardia</th>
                        <th class="px-6 py-3 text-center text-[10px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Turnos</th>
                        <th class="px-6 py-3 text-center text-[10px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Asistencia</th>
                        <th class="px-6 py-3 text-center text-[10px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Emergencias</th>
                        <th class="px-6 py-3 text-center text-[10px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Reemplazos</th>
                        <th class="px-6 py-3 text-center text-[10px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Permisos</th>
                        <th class="px-6 py-3 text-center text-[10px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Rendimiento</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @php
                    $guardias = [
                        ['nombre' => 'Guardia 1', 'color' => 'red', 'turnos' => 7, 'asistencia' => 96, 'emergencias' => 14, 'reemplazos' => 2, 'permisos' => 3, 'rendimiento' => 'excelente'],
                        ['nombre' => 'Guardia 2', 'color' => 'blue', 'turnos' => 7, 'asistencia' => 92, 'emergencias' => 11, 'reemplazos' => 4, 'permisos' => 2, 'rendimiento' => 'bueno'],
                        ['nombre' => 'Guardia 3', 'color' => 'emerald', 'turnos' => 7, 'asistencia' => 94, 'emergencias' => 12, 'reemplazos' => 3, 'permisos' => 4, 'rendimiento' => 'bueno'],
                        ['nombre' => 'Guardia 4', 'color' => 'purple', 'turnos' => 7, 'asistencia' => 91, 'emergencias' => 10, 'reemplazos' => 5, 'permisos' => 3, 'rendimiento' => 'regular'],
                    ];
                    $rendimientoConfig = [
                        'excelente' => ['color' => 'emerald', 'label' => 'Excelente'],
                        'bueno' => ['color' => 'blue', 'label' => 'Bueno'],
                        'regular' => ['color' => 'amber', 'label' => 'Regular'],
                    ];
                    @endphp
                    @foreach($guardias as $g)
                    @php $rc = $rendimientoConfig[$g['rendimiento']]; @endphp
                    <tr class="hover:bg-white dark:hover:bg-slate-800 dark:hover:bg-slate-800/50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <span class="w-3 h-3 rounded-full bg-{{ $g['color'] }}-500"></span>
                                <span class="font-medium text-slate-900 dark:text-white">{{ $g['nombre'] }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-center text-slate-700 dark:text-slate-300">{{ $g['turnos'] }}</td>
                        <td class="px-6 py-4 text-center">
                            <span class="font-semibold {{ $g['asistencia'] >= 95 ? 'text-emerald-600 dark:text-emerald-400' : ($g['asistencia'] >= 90 ? 'text-blue-600 dark:text-blue-400' : 'text-amber-600 dark:text-amber-400') }}">
                                {{ $g['asistencia'] }}%
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center text-slate-700 dark:text-slate-300">{{ $g['emergencias'] }}</td>
                        <td class="px-6 py-4 text-center text-slate-700 dark:text-slate-300">{{ $g['reemplazos'] }}</td>
                        <td class="px-6 py-4 text-center text-slate-700 dark:text-slate-300">{{ $g['permisos'] }}</td>
                        <td class="px-6 py-4 text-center">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-{{ $rc['color'] }}-100 dark:bg-{{ $rc['color'] }}-900/30 text-{{ $rc['color'] }}-700 dark:text-{{ $rc['color'] }}-400">
                                {{ $rc['label'] }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
