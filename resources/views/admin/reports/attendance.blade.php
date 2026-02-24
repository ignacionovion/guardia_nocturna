@extends('layouts.app')

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
            <a href="{{ route('admin.reports.attendance', request()->except('tab')) }}"
               class="flex items-center gap-2 px-6 py-4 text-sm font-semibold whitespace-nowrap border-b-2 transition-colors
                      {{ (!request('tab') || request('tab') === 'asistencia') ? 'text-red-600 border-red-600 bg-red-50' : 'text-slate-600 border-transparent hover:text-slate-800' }}">
                <i class="fas fa-calendar-check"></i> Asistencia
            </a>
            <a href="{{ route('admin.reports.attendance', array_merge(request()->all(), ['tab' => 'permisos'])) }}"
               class="flex items-center gap-2 px-6 py-4 text-sm font-semibold whitespace-nowrap border-b-2 transition-colors
                      {{ request('tab') === 'permisos' ? 'text-amber-600 border-amber-500 bg-amber-50' : 'text-slate-600 border-transparent hover:text-slate-800' }}">
                <i class="fas fa-calendar-alt"></i> Permisos
            </a>
            <a href="{{ route('admin.reports.replacements') }}"
               class="flex items-center gap-2 px-6 py-4 text-sm font-semibold whitespace-nowrap border-b-2 border-transparent text-slate-600 hover:text-slate-800 transition-colors">
                <i class="fas fa-exchange-alt"></i> Reemplazos
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

    @php $activeTab = request('tab', 'asistencia'); @endphp

    @if($activeTab === 'asistencia')

    {{-- FILTROS PROFESIONALES --}}
    <div class="bg-white p-5 border border-t-0 border-slate-200 mb-6 rounded-b-lg shadow-sm">
        <form action="{{ route('admin.reports.attendance') }}" method="GET" class="flex flex-wrap items-end gap-4">
            <input type="hidden" name="tab" value="asistencia">
            
            {{-- Filtro por Guardia --}}
            <div class="min-w-[180px]">
                <label class="block text-xs font-black text-slate-500 uppercase tracking-wider mb-2">Guardia</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-shield-alt text-slate-400 text-xs"></i>
                    </div>
                    <select name="guardia_id" class="pl-9 pr-8 py-2.5 bg-slate-50 border border-slate-300 rounded-lg text-sm font-semibold text-slate-700 focus:ring-2 focus:ring-red-500/20 focus:border-red-500 w-full appearance-none cursor-pointer hover:bg-slate-100 transition-colors">
                        <option value="">Todas las Guardias</option>
                        @foreach($guardias as $g)
                            <option value="{{ $g->id }}" {{ $guardiaId == $g->id ? 'selected' : '' }}>{{ $g->name }}</option>
                        @endforeach
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                        <i class="fas fa-chevron-down text-slate-400 text-xs"></i>
                    </div>
                </div>
            </div>

            {{-- Filtro por Semana --}}
            <div class="min-w-[180px]">
                <label class="block text-xs font-black text-slate-500 uppercase tracking-wider mb-2">Semana</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-calendar-week text-slate-400 text-xs"></i>
                    </div>
                    <select name="week" class="pl-9 pr-8 py-2.5 bg-slate-50 border border-slate-300 rounded-lg text-sm font-semibold text-slate-700 focus:ring-2 focus:ring-red-500/20 focus:border-red-500 w-full appearance-none cursor-pointer hover:bg-slate-100 transition-colors">
                        <option value="">Todas las semanas</option>
                        @php
                            $currentWeek = now()->weekOfYear;
                            for($w = $currentWeek - 8; $w <= $currentWeek; $w++) {
                                $weekStart = \Carbon\Carbon::now()->setISODate(now()->year, $w, 1);
                                $weekLabel = 'Semana ' . $w . ' (' . $weekStart->format('d/m') . ' - ' . $weekStart->copy()->addDays(6)->format('d/m') . ')';
                                echo '<option value="' . $w . '">' . $weekLabel . '</option>';
                            }
                        @endphp
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                        <i class="fas fa-chevron-down text-slate-400 text-xs"></i>
                    </div>
                </div>
            </div>

            {{-- Fecha Desde --}}
            <div class="min-w-[150px]">
                <label class="block text-xs font-black text-slate-500 uppercase tracking-wider mb-2">Desde</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-calendar text-slate-400 text-xs"></i>
                    </div>
                    <input type="date" name="from" value="{{ $from->format('Y-m-d') }}" class="pl-9 pr-3 py-2.5 bg-slate-50 border border-slate-300 rounded-lg text-sm font-semibold text-slate-700 focus:ring-2 focus:ring-red-500/20 focus:border-red-500 w-full hover:bg-slate-100 transition-colors">
                </div>
            </div>

            {{-- Fecha Hasta --}}
            <div class="min-w-[150px]">
                <label class="block text-xs font-black text-slate-500 uppercase tracking-wider mb-2">Hasta</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-calendar text-slate-400 text-xs"></i>
                    </div>
                    <input type="date" name="to" value="{{ $to->format('Y-m-d') }}" class="pl-9 pr-3 py-2.5 bg-slate-50 border border-slate-300 rounded-lg text-sm font-semibold text-slate-700 focus:ring-2 focus:ring-red-500/20 focus:border-red-500 w-full hover:bg-slate-100 transition-colors">
                </div>
            </div>

            {{-- Botón Filtrar --}}
            <div class="flex gap-2">
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-black py-2.5 px-5 rounded-lg text-sm transition-all shadow-sm hover:shadow-md flex items-center gap-2 uppercase tracking-wider">
                    <i class="fas fa-filter"></i> Filtrar
                </button>
                <a href="{{ route('admin.reports.attendance') }}" class="bg-slate-100 hover:bg-slate-200 text-slate-600 font-black py-2.5 px-4 rounded-lg text-sm transition-all flex items-center gap-2 uppercase tracking-wider" title="Limpiar filtros">
                    <i class="fas fa-undo"></i>
                </a>
            </div>
        </form>
    </div>

    {{-- SUB-TABS: Por Guardia / General --}}
    <div class="bg-white border border-slate-200 rounded-t-lg">
        <div class="flex">
            <a href="{{ route('admin.reports.attendance', array_merge(request()->all(), ['view' => 'guardias'])) }}"
               class="flex items-center gap-2 px-5 py-3 text-sm font-semibold border-b-2 transition-colors
                      {{ $currentView !== 'general' ? 'text-red-600 border-red-600 bg-red-50' : 'text-slate-600 border-transparent hover:text-slate-800' }}">
                <i class="fas fa-shield-alt"></i> Por Guardia
            </a>
            <a href="{{ route('admin.reports.attendance', array_merge(request()->all(), ['view' => 'general'])) }}"
               class="flex items-center gap-2 px-5 py-3 text-sm font-semibold border-b-2 transition-colors
                      {{ $currentView === 'general' ? 'text-red-600 border-red-600 bg-red-50' : 'text-slate-600 border-transparent hover:text-slate-800' }}">
                <i class="fas fa-globe"></i> General
            </a>
        </div>
    </div>

    {{-- STATS CARDS --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 bg-white border border-t-0 border-slate-200 p-4 mb-6">
        @php
            $statCards = [
                ['label' => 'Cumplidos',    'value' => $stats['fulfilled'] ?? 0,    'color' => 'emerald', 'icon' => 'check-circle'],
                ['label' => 'Ausencias',    'value' => $stats['absences'] ?? 0,     'color' => 'rose',    'icon' => 'times-circle'],
                ['label' => 'Permisos',     'value' => $stats['permissions'] ?? 0,  'color' => 'amber',   'icon' => 'calendar-check'],
                ['label' => 'Licencias',    'value' => $stats['licenses'] ?? 0,     'color' => 'blue',    'icon' => 'file-medical'],
                ['label' => 'Inhabilitados','value' => $stats['disabled'] ?? 0,     'color' => 'slate',   'icon' => 'ban'],
                ['label' => 'Reemplazos',   'value' => $stats['replacements'] ?? 0, 'color' => 'purple',  'icon' => 'exchange-alt'],
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

    {{-- INFORME GENERAL --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <div class="lg:col-span-2 bg-white rounded-lg shadow-sm border border-slate-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-bold text-slate-800 uppercase tracking-wide">
                    <i class="fas fa-chart-bar mr-2 text-red-500"></i>
                    {{ $currentView === 'general' ? 'Consolidado General' : ($activeGuardia ? $activeGuardia->name : 'Sin Guardia') }}
                </h3>
                <span class="text-xs text-slate-400">{{ $from->format('d/m/Y') }} — {{ $to->format('d/m/Y') }}</span>
            </div>
            <div class="space-y-3 mb-6">
                @foreach($guardiaStats as $stat)
                <div class="flex items-center gap-3">
                    <div class="w-24 text-xs font-semibold text-slate-600 truncate">{{ $stat['label'] }}</div>
                    <div class="flex-1 h-2.5 bg-slate-100 rounded-full overflow-hidden">
                        <div class="h-full bg-{{ $stat['color'] }}-500 rounded-full" style="width: {{ min($stat['value'], 100) }}%"></div>
                    </div>
                    <div class="w-12 text-right">
                        <span class="text-xs font-bold text-{{ $stat['color'] }}-600">{{ $stat['value'] }}%</span>
                    </div>
                    <div class="w-6 text-right text-xs text-slate-400 font-semibold">{{ $stat['count'] }}</div>
                </div>
                @endforeach
            </div>
            <div class="pt-4 border-t border-slate-100">
                <h4 class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-3">
                    Últimas 8 Semanas <span class="normal-case font-normal text-slate-400">(Dom 22:00 → Dom 07:00)</span>
                </h4>
                <div class="grid grid-cols-8 gap-1.5">
                    @foreach($weeklyStats as $week)
                    <div class="text-center">
                        <div class="text-[10px] text-slate-400 mb-1 truncate">{{ $week['week'] }}</div>
                        <div class="h-14 bg-slate-100 rounded relative overflow-hidden">
                            <div class="absolute bottom-0 left-0 right-0 rounded transition-all
                                        {{ $week['percentage'] >= 80 ? 'bg-emerald-500' : ($week['percentage'] >= 60 ? 'bg-amber-400' : 'bg-rose-400') }}"
                                 style="height: {{ $week['percentage'] }}%"></div>
                        </div>
                        <div class="text-[10px] font-bold mt-1 {{ $week['active'] ? 'text-red-600' : 'text-slate-500' }}">
                            {{ $week['percentage'] }}%
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="space-y-4">
            <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-5">
                <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-4">Resumen</h3>
                <div class="text-center mb-4">
                    <div class="text-5xl font-black text-slate-800">{{ $generalPercentage }}%</div>
                    <div class="text-xs text-slate-500 mt-1 uppercase tracking-wide">Cumplimiento General</div>
                </div>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between py-1.5 border-b border-slate-100">
                        <span class="text-slate-500">Total Personal</span>
                        <span class="font-bold text-slate-800">{{ $totalPersonnel }}</span>
                    </div>
                    <div class="flex justify-between py-1.5 border-b border-slate-100">
                        <span class="text-slate-500">Índice Estabilidad</span>
                        <span class="font-bold {{ $stabilityIndex >= 80 ? 'text-emerald-600' : ($stabilityIndex >= 60 ? 'text-amber-600' : 'text-rose-600') }}">{{ $stabilityIndex }}%</span>
                    </div>
                    <div class="flex justify-between py-1.5 border-b border-slate-100">
                        <span class="text-slate-500">Periodo</span>
                        <span class="font-bold text-slate-800 text-xs">{{ $from->format('d/m') }} — {{ $to->format('d/m') }}</span>
                    </div>
                    <div class="flex justify-between py-1.5 border-b border-slate-100">
                        <span class="text-slate-500">Total Turnos</span>
                        <span class="font-bold text-slate-800">{{ ($stats['fulfilled'] ?? 0) + ($stats['absences'] ?? 0) + ($stats['permissions'] ?? 0) + ($stats['licenses'] ?? 0) }}</span>
                    </div>
                    <div class="flex justify-between py-1.5">
                        <span class="text-slate-500">Refuerzos</span>
                        <span class="font-bold text-teal-600">{{ $stats['reinforcements'] ?? 0 }}</span>
                    </div>
                </div>
            </div>

            @if($currentView === 'general' && !empty($guardiaComparison))
            <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-5">
                <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-3">Comparativa Guardias</h3>
                <div class="space-y-3">
                    @foreach($guardiaComparison as $gc)
                    <div>
                        <div class="flex justify-between text-xs mb-1">
                            <span class="font-semibold text-slate-700">{{ $gc['name'] }}</span>
                            <span class="font-bold {{ $gc['percentage'] >= 80 ? 'text-emerald-600' : ($gc['percentage'] >= 60 ? 'text-amber-600' : 'text-rose-600') }}">{{ $gc['percentage'] }}%</span>
                        </div>
                        <div class="h-2 bg-slate-100 rounded-full overflow-hidden">
                            <div class="h-full rounded-full {{ $gc['percentage'] >= 80 ? 'bg-emerald-500' : ($gc['percentage'] >= 60 ? 'bg-amber-400' : 'bg-rose-400') }}"
                                 style="width: {{ $gc['percentage'] }}%"></div>
                        </div>
                        <div class="flex justify-between text-[10px] text-slate-400 mt-0.5">
                            <span>{{ $gc['fulfilled'] }} cumplidos / {{ $gc['total'] }} turnos</span>
                            <span>{{ $gc['personnel'] }} pers.</span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- RANKINGS MEJORADOS --}}
    @if(!empty($rankings))
    <div class="mb-6">
        <h3 class="text-sm font-black text-slate-700 uppercase tracking-widest mb-4 flex items-center gap-2">
            <i class="fas fa-trophy text-amber-500"></i> Rankings del Período
        </h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            @foreach($rankings as $rank)
            <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm hover:shadow-md transition-all">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 rounded-lg bg-{{ $rank['color'] }}-100 flex items-center justify-center text-{{ $rank['color'] }}-600">
                        <span class="text-lg">{{ $rank['emoji'] }}</span>
                    </div>
                    <span class="text-xs font-black text-slate-500 uppercase tracking-wide leading-tight">{{ $rank['label'] }}</span>
                </div>
                <p class="font-bold text-slate-800 text-sm truncate">{{ $rank['name'] }}</p>
                <div class="flex items-center gap-2 mt-2">
                    <span class="text-lg font-black text-{{ $rank['color'] }}-600">{{ $rank['value'] }}</span>
                    <span class="text-xs text-slate-400">{{ $rank['unit'] }}</span>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- INFORME POR BOMBERO - DIVIDIDO EN 3 SECCIONES --}}
    @php
        $titulares = collect($firefighterStats)->where('tipo', 'titular')->values();
        $reemplazos = collect($firefighterStats)->where('tipo', 'reemplazo')->values();
        $refuerzos = collect($firefighterStats)->where('tipo', 'refuerzo')->values();
    @endphp

    {{-- SECCIÓN 1: TITULARES --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden mb-6">
        <div class="p-4 border-b border-slate-200 bg-emerald-50 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-emerald-600 text-white flex items-center justify-center">
                    <i class="fas fa-user-check"></i>
                </div>
                <div>
                    <h3 class="text-base font-black text-slate-800 uppercase tracking-wide">Titulares</h3>
                    <p class="text-xs text-slate-500">Voluntarios de plantilla</p>
                </div>
            </div>
            <span class="text-sm font-black text-emerald-700 bg-white px-3 py-1 rounded-full border border-emerald-200">{{ $titulares->count() }} voluntarios</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="text-left py-3 px-4 font-semibold text-slate-600 text-xs uppercase">Voluntario</th>
                        <th class="text-center py-3 px-2 font-semibold text-slate-500 text-xs uppercase">Turnos</th>
                        <th class="text-center py-3 px-2 font-semibold text-emerald-600 text-xs uppercase">Cumpl.</th>
                        <th class="text-center py-3 px-2 font-semibold text-rose-600 text-xs uppercase">Aus.</th>
                        <th class="text-center py-3 px-2 font-semibold text-amber-600 text-xs uppercase">Perm.</th>
                        <th class="text-center py-3 px-2 font-semibold text-blue-600 text-xs uppercase">Lic.</th>
                        <th class="text-center py-3 px-2 font-semibold text-slate-500 text-xs uppercase">Inhab.</th>
                        <th class="text-center py-3 px-2 font-semibold text-purple-600 text-xs uppercase">Reempl. Hechos</th>
                        <th class="text-center py-3 px-2 font-semibold text-indigo-600 text-xs uppercase">Reempl. Recib.</th>
                        <th class="text-center py-3 px-3 font-semibold text-slate-600 text-xs uppercase">% Cumpl.</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($titulares as $ff)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="py-3 px-4">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 bg-emerald-100 rounded-full flex items-center justify-center text-emerald-700 font-bold text-xs flex-shrink-0">
                                    {{ $ff['code'] }}
                                </div>
                                <div>
                                    <span class="font-bold text-slate-800 text-xs block">{{ $ff['name'] }}</span>
                                    @if(!empty($ff['guardia_name']))
                                    <span class="text-[10px] text-slate-400">{{ $ff['guardia_name'] }}</span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="py-3 px-2 text-center font-bold text-slate-700">{{ $ff['shift'] }}</td>
                        <td class="py-3 px-2 text-center">
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded bg-emerald-100 text-emerald-700 font-bold text-xs">{{ $ff['fulfilled'] }}</span>
                        </td>
                        <td class="py-3 px-2 text-center">
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded {{ $ff['absences'] > 0 ? 'bg-rose-100 text-rose-700' : 'bg-slate-100 text-slate-400' }} font-bold text-xs">{{ $ff['absences'] }}</span>
                        </td>
                        <td class="py-3 px-2 text-center">
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded {{ $ff['permissions'] > 0 ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-400' }} font-bold text-xs">{{ $ff['permissions'] }}</span>
                        </td>
                        <td class="py-3 px-2 text-center">
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded {{ $ff['licenses'] > 0 ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-400' }} font-bold text-xs">{{ $ff['licenses'] }}</span>
                        </td>
                        <td class="py-3 px-2 text-center">
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded {{ $ff['disabled'] > 0 ? 'bg-slate-200 text-slate-600' : 'bg-slate-100 text-slate-400' }} font-bold text-xs">{{ $ff['disabled'] }}</span>
                        </td>
                        <td class="py-3 px-2 text-center">
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded {{ ($ff['replacements_made'] ?? 0) > 0 ? 'bg-purple-100 text-purple-700' : 'bg-slate-100 text-slate-400' }} font-bold text-xs">{{ $ff['replacements_made'] ?? 0 }}</span>
                        </td>
                        <td class="py-3 px-2 text-center">
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded {{ ($ff['replacements_received'] ?? 0) > 0 ? 'bg-indigo-100 text-indigo-700' : 'bg-slate-100 text-slate-400' }} font-bold text-xs">{{ $ff['replacements_received'] ?? 0 }}</span>
                        </td>
                        <td class="py-3 px-3">
                            <div class="flex items-center gap-2">
                                <div class="flex-1 h-1.5 bg-slate-100 rounded-full overflow-hidden min-w-[40px]">
                                    <div class="h-full rounded-full {{ $ff['percentage'] >= 90 ? 'bg-emerald-500' : ($ff['percentage'] >= 75 ? 'bg-amber-500' : 'bg-rose-500') }}"
                                         style="width: {{ $ff['percentage'] }}%"></div>
                                </div>
                                <span class="text-xs font-bold {{ $ff['percentage'] >= 90 ? 'text-emerald-600' : ($ff['percentage'] >= 75 ? 'text-amber-600' : 'text-rose-600') }} w-8 text-right">{{ $ff['percentage'] }}%</span>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="py-10 text-center text-slate-400">
                            <i class="fas fa-inbox text-2xl mb-2 block"></i>
                            No hay titulares registrados
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- SECCIÓN 2: REEMPLAZOS --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden mb-6">
        <div class="p-4 border-b border-slate-200 bg-purple-50 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-purple-600 text-white flex items-center justify-center">
                    <i class="fas fa-exchange-alt"></i>
                </div>
                <div>
                    <h3 class="text-base font-black text-slate-800 uppercase tracking-wide">Reemplazos</h3>
                    <p class="text-xs text-slate-500">Voluntarios que han reemplazado o sido reemplazados</p>
                </div>
            </div>
            <span class="text-sm font-black text-purple-700 bg-white px-3 py-1 rounded-full border border-purple-200">{{ $reemplazos->count() }} voluntarios</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="text-left py-3 px-4 font-semibold text-slate-600 text-xs uppercase">Voluntario</th>
                        <th class="text-center py-3 px-2 font-semibold text-slate-500 text-xs uppercase">Turnos</th>
                        <th class="text-center py-3 px-2 font-semibold text-emerald-600 text-xs uppercase">Cumpl.</th>
                        <th class="text-center py-3 px-2 font-semibold text-rose-600 text-xs uppercase">Aus.</th>
                        <th class="text-center py-3 px-2 font-semibold text-amber-600 text-xs uppercase">Perm.</th>
                        <th class="text-center py-3 px-2 font-semibold text-blue-600 text-xs uppercase">Lic.</th>
                        <th class="text-center py-3 px-2 font-semibold text-slate-500 text-xs uppercase">Inhab.</th>
                        <th class="text-center py-3 px-2 font-semibold text-purple-600 text-xs uppercase">Reempl. Hechos</th>
                        <th class="text-center py-3 px-2 font-semibold text-indigo-600 text-xs uppercase">Reempl. Recib.</th>
                        <th class="text-center py-3 px-3 font-semibold text-slate-600 text-xs uppercase">% Cumpl.</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($reemplazos as $ff)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="py-3 px-4">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center text-purple-700 font-bold text-xs flex-shrink-0">
                                    {{ $ff['code'] }}
                                </div>
                                <div>
                                    <span class="font-bold text-slate-800 text-xs block">{{ $ff['name'] }}</span>
                                    @if(!empty($ff['guardia_name']))
                                    <span class="text-[10px] text-slate-400">{{ $ff['guardia_name'] }}</span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="py-3 px-2 text-center font-bold text-slate-700">{{ $ff['shift'] }}</td>
                        <td class="py-3 px-2 text-center">
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded bg-emerald-100 text-emerald-700 font-bold text-xs">{{ $ff['fulfilled'] }}</span>
                        </td>
                        <td class="py-3 px-2 text-center">
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded {{ $ff['absences'] > 0 ? 'bg-rose-100 text-rose-700' : 'bg-slate-100 text-slate-400' }} font-bold text-xs">{{ $ff['absences'] }}</span>
                        </td>
                        <td class="py-3 px-2 text-center">
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded {{ $ff['permissions'] > 0 ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-400' }} font-bold text-xs">{{ $ff['permissions'] }}</span>
                        </td>
                        <td class="py-3 px-2 text-center">
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded {{ $ff['licenses'] > 0 ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-400' }} font-bold text-xs">{{ $ff['licenses'] }}</span>
                        </td>
                        <td class="py-3 px-2 text-center">
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded {{ $ff['disabled'] > 0 ? 'bg-slate-200 text-slate-600' : 'bg-slate-100 text-slate-400' }} font-bold text-xs">{{ $ff['disabled'] }}</span>
                        </td>
                        <td class="py-3 px-2 text-center">
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded {{ ($ff['replacements_made'] ?? 0) > 0 ? 'bg-purple-100 text-purple-700' : 'bg-slate-100 text-slate-400' }} font-bold text-xs">{{ $ff['replacements_made'] ?? 0 }}</span>
                        </td>
                        <td class="py-3 px-2 text-center">
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded {{ ($ff['replacements_received'] ?? 0) > 0 ? 'bg-indigo-100 text-indigo-700' : 'bg-slate-100 text-slate-400' }} font-bold text-xs">{{ $ff['replacements_received'] ?? 0 }}</span>
                        </td>
                        <td class="py-3 px-3">
                            <div class="flex items-center gap-2">
                                <div class="flex-1 h-1.5 bg-slate-100 rounded-full overflow-hidden min-w-[40px]">
                                    <div class="h-full rounded-full {{ $ff['percentage'] >= 90 ? 'bg-emerald-500' : ($ff['percentage'] >= 75 ? 'bg-amber-500' : 'bg-rose-500') }}"
                                         style="width: {{ $ff['percentage'] }}%"></div>
                                </div>
                                <span class="text-xs font-bold {{ $ff['percentage'] >= 90 ? 'text-emerald-600' : ($ff['percentage'] >= 75 ? 'text-amber-600' : 'text-rose-600') }} w-8 text-right">{{ $ff['percentage'] }}%</span>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="py-10 text-center text-slate-400">
                            <i class="fas fa-inbox text-2xl mb-2 block"></i>
                            No hay reemplazos registrados
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- SECCIÓN 3: REFUERZOS --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden mb-6">
        <div class="p-4 border-b border-slate-200 bg-sky-50 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-sky-600 text-white flex items-center justify-center">
                    <i class="fas fa-user-plus"></i>
                </div>
                <div>
                    <h3 class="text-base font-black text-slate-800 uppercase tracking-wide">Refuerzos</h3>
                    <p class="text-xs text-slate-500">Voluntarios que han venido como refuerzo</p>
                </div>
            </div>
            <span class="text-sm font-black text-sky-700 bg-white px-3 py-1 rounded-full border border-sky-200">{{ $refuerzos->count() }} voluntarios</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="text-left py-3 px-4 font-semibold text-slate-600 text-xs uppercase">Voluntario</th>
                        <th class="text-center py-3 px-2 font-semibold text-slate-500 text-xs uppercase">Turnos</th>
                        <th class="text-center py-3 px-2 font-semibold text-emerald-600 text-xs uppercase">Cumpl.</th>
                        <th class="text-center py-3 px-2 font-semibold text-rose-600 text-xs uppercase">Aus.</th>
                        <th class="text-center py-3 px-2 font-semibold text-amber-600 text-xs uppercase">Perm.</th>
                        <th class="text-center py-3 px-2 font-semibold text-blue-600 text-xs uppercase">Lic.</th>
                        <th class="text-center py-3 px-2 font-semibold text-slate-500 text-xs uppercase">Inhab.</th>
                        <th class="text-center py-3 px-2 font-semibold text-teal-600 text-xs uppercase">Refuerzos</th>
                        <th class="text-center py-3 px-3 font-semibold text-slate-600 text-xs uppercase">% Cumpl.</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($refuerzos as $ff)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="py-3 px-4">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 bg-sky-100 rounded-full flex items-center justify-center text-sky-700 font-bold text-xs flex-shrink-0">
                                    {{ $ff['code'] }}
                                </div>
                                <div>
                                    <span class="font-bold text-slate-800 text-xs block">{{ $ff['name'] }}</span>
                                    @if(!empty($ff['guardia_name']))
                                    <span class="text-[10px] text-slate-400">{{ $ff['guardia_name'] }}</span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="py-3 px-2 text-center font-bold text-slate-700">{{ $ff['shift'] }}</td>
                        <td class="py-3 px-2 text-center">
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded bg-emerald-100 text-emerald-700 font-bold text-xs">{{ $ff['fulfilled'] }}</span>
                        </td>
                        <td class="py-3 px-2 text-center">
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded {{ $ff['absences'] > 0 ? 'bg-rose-100 text-rose-700' : 'bg-slate-100 text-slate-400' }} font-bold text-xs">{{ $ff['absences'] }}</span>
                        </td>
                        <td class="py-3 px-2 text-center">
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded {{ $ff['permissions'] > 0 ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-400' }} font-bold text-xs">{{ $ff['permissions'] }}</span>
                        </td>
                        <td class="py-3 px-2 text-center">
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded {{ $ff['licenses'] > 0 ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-400' }} font-bold text-xs">{{ $ff['licenses'] }}</span>
                        </td>
                        <td class="py-3 px-2 text-center">
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded {{ $ff['disabled'] > 0 ? 'bg-slate-200 text-slate-600' : 'bg-slate-100 text-slate-400' }} font-bold text-xs">{{ $ff['disabled'] }}</span>
                        </td>
                        <td class="py-3 px-2 text-center">
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded {{ ($ff['reinforcements'] ?? 0) > 0 ? 'bg-teal-100 text-teal-700' : 'bg-slate-100 text-slate-400' }} font-bold text-xs">{{ $ff['reinforcements'] ?? 0 }}</span>
                        </td>
                        <td class="py-3 px-3">
                            <div class="flex items-center gap-2">
                                <div class="flex-1 h-1.5 bg-slate-100 rounded-full overflow-hidden min-w-[40px]">
                                    <div class="h-full rounded-full {{ $ff['percentage'] >= 90 ? 'bg-emerald-500' : ($ff['percentage'] >= 75 ? 'bg-amber-500' : 'bg-rose-500') }}"
                                         style="width: {{ $ff['percentage'] }}%"></div>
                                </div>
                                <span class="text-xs font-bold {{ $ff['percentage'] >= 90 ? 'text-emerald-600' : ($ff['percentage'] >= 75 ? 'text-amber-600' : 'text-rose-600') }} w-8 text-right">{{ $ff['percentage'] }}%</span>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="py-10 text-center text-slate-400">
                            <i class="fas fa-inbox text-2xl mb-2 block"></i>
                            No hay refuerzos registrados
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- RENDIMIENTO POR DÍA --}}
    @if(!empty($dailyHistory))
    <div class="bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden mb-6">
        <div class="p-4 border-b border-slate-200">
            <h3 class="text-base font-bold text-slate-800 uppercase tracking-wide flex items-center gap-2">
                <i class="fas fa-history text-slate-400"></i> Rendimiento por Día
                <span class="text-xs font-normal text-slate-400 normal-case ml-1">(Semana real: Dom 22:00 → Dom 07:00)</span>
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="text-left py-3 px-4 font-semibold text-slate-600 text-xs uppercase">Fecha</th>
                        <th class="text-left py-3 px-3 font-semibold text-slate-600 text-xs uppercase">Guardia</th>
                        <th class="text-center py-3 px-3 font-semibold text-emerald-600 text-xs uppercase">Constituyen</th>
                        <th class="text-center py-3 px-3 font-semibold text-slate-500 text-xs uppercase">% Cobertura</th>
                        <th class="text-left py-3 px-3 font-semibold text-rose-600 text-xs uppercase">Ausentes</th>
                        <th class="text-left py-3 px-3 font-semibold text-purple-600 text-xs uppercase">Reemplazos</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($dailyHistory as $day)
                    <tr class="hover:bg-slate-50">
                        <td class="py-2.5 px-4 font-semibold text-slate-700 text-xs">{{ $day['date'] }}</td>
                        <td class="py-2.5 px-3 text-xs text-slate-500">{{ $day['guardia'] }}</td>
                        <td class="py-2.5 px-3 text-center font-bold text-emerald-600">{{ $day['constituyen'] }}</td>
                        <td class="py-2.5 px-3">
                            <div class="flex items-center gap-2 justify-center">
                                <div class="w-16 h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                    <div class="h-full rounded-full {{ $day['coverage'] >= 80 ? 'bg-emerald-500' : ($day['coverage'] >= 60 ? 'bg-amber-400' : 'bg-rose-400') }}"
                                         style="width: {{ $day['coverage'] }}%"></div>
                                </div>
                                <span class="text-xs font-bold {{ $day['coverage'] >= 80 ? 'text-emerald-600' : ($day['coverage'] >= 60 ? 'text-amber-600' : 'text-rose-600') }}">{{ $day['coverage'] }}%</span>
                            </div>
                        </td>
                        <td class="py-2.5 px-3 text-xs text-rose-600 max-w-xs truncate">{{ $day['absent_names'] ?: '—' }}</td>
                        <td class="py-2.5 px-3 text-xs text-purple-600 max-w-xs truncate">{{ $day['replacement_names'] ?: '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- ============================================================ --}}
    {{-- TAB: PERMISOS --}}
    {{-- ============================================================ --}}
    @elseif($activeTab === 'permisos')

    <div class="bg-white border border-t-0 border-slate-200 p-4 mb-6">
        <form action="{{ route('admin.reports.attendance') }}" method="GET" class="flex flex-wrap items-end gap-3">
            <input type="hidden" name="tab" value="permisos">
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Guardia</label>
                <select name="guardia_id" class="px-3 py-2 bg-slate-50 border border-slate-300 rounded-md text-sm w-44">
                    <option value="">Todas</option>
                    @foreach($guardias as $g)
                        <option value="{{ $g->id }}" {{ $guardiaId == $g->id ? 'selected' : '' }}>{{ $g->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Desde</label>
                <input type="date" name="from" value="{{ $from->format('Y-m-d') }}" class="px-3 py-2 bg-slate-50 border border-slate-300 rounded-md text-sm w-36">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Hasta</label>
                <input type="date" name="to" value="{{ $to->format('Y-m-d') }}" class="px-3 py-2 bg-slate-50 border border-slate-300 rounded-md text-sm w-36">
            </div>
            <button type="submit" class="bg-amber-500 hover:bg-amber-600 text-white font-semibold py-2 px-4 rounded-md text-sm flex items-center gap-2">
                <i class="fas fa-filter"></i> Filtrar
            </button>
        </form>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden mb-6">
        <div class="p-4 border-b border-slate-200 flex items-center justify-between">
            <h3 class="text-base font-bold text-slate-800 uppercase tracking-wide flex items-center gap-2">
                <i class="fas fa-calendar-alt text-amber-500"></i> Permisos y Licencias
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="text-left py-3 px-4 font-semibold text-slate-600 text-xs uppercase">Voluntario</th>
                        <th class="text-left py-3 px-3 font-semibold text-slate-600 text-xs uppercase">Guardia</th>
                        <th class="text-center py-3 px-3 font-semibold text-amber-600 text-xs uppercase">Permisos</th>
                        <th class="text-center py-3 px-3 font-semibold text-blue-600 text-xs uppercase">Licencias</th>
                        <th class="text-center py-3 px-3 font-semibold text-slate-500 text-xs uppercase">Inhabilitados</th>
                        <th class="text-center py-3 px-3 font-semibold text-slate-600 text-xs uppercase">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($firefighterStats->filter(fn($f) => ($f['permissions'] + $f['licenses'] + $f['disabled']) > 0)->sortByDesc(fn($f) => $f['permissions'] + $f['licenses'] + $f['disabled']) as $ff)
                    <tr class="hover:bg-slate-50">
                        <td class="py-3 px-4">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 bg-amber-100 rounded-full flex items-center justify-center text-amber-700 font-bold text-xs flex-shrink-0">
                                    {{ $ff['code'] }}
                                </div>
                                <span class="font-medium text-slate-800 text-xs">{{ $ff['name'] }}</span>
                            </div>
                        </td>
                        <td class="py-3 px-3 text-xs text-slate-500">{{ $ff['guardia_name'] ?? '—' }}</td>
                        <td class="py-3 px-3 text-center">
                            <span class="inline-flex items-center justify-center w-7 h-7 rounded-full {{ $ff['permissions'] > 0 ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-400' }} font-bold text-xs">{{ $ff['permissions'] }}</span>
                        </td>
                        <td class="py-3 px-3 text-center">
                            <span class="inline-flex items-center justify-center w-7 h-7 rounded-full {{ $ff['licenses'] > 0 ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-400' }} font-bold text-xs">{{ $ff['licenses'] }}</span>
                        </td>
                        <td class="py-3 px-3 text-center">
                            <span class="inline-flex items-center justify-center w-7 h-7 rounded-full {{ $ff['disabled'] > 0 ? 'bg-slate-200 text-slate-600' : 'bg-slate-100 text-slate-400' }} font-bold text-xs">{{ $ff['disabled'] }}</span>
                        </td>
                        <td class="py-3 px-3 text-center font-bold text-slate-700">{{ $ff['permissions'] + $ff['licenses'] + $ff['disabled'] }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-10 text-center text-slate-400">
                            <i class="fas fa-inbox text-2xl mb-2 block"></i>
                            No hay permisos ni licencias en el periodo seleccionado
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @endif

    {{-- Cierre del contenedor principal --}}
</div>
@endsection
