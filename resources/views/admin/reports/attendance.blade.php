@extends('layouts.modern')

@section('content')
<div class="container mx-auto px-4 py-6 max-w-7xl">

    {{-- HEADER --}}
    <div class="mb-6 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-white flex items-center uppercase">
                <span class="mr-3 inline-flex h-10 w-10 items-center justify-center rounded-xl bg-slate-900 text-white shadow-sm">
                    <i class="fas fa-calendar-check text-sm"></i>
                </span>
                Reporte de Asistencia
            </h1>
            <p class="text-slate-500 dark:text-slate-400 mt-1 text-sm">Resumen simple de asistencia por guardia y por voluntario.</p>
        </div>
    </div>

    @include('admin.reports._tabs')

    @php $activeTab = request('tab', 'asistencia'); @endphp

    @if($activeTab === 'asistencia')

    {{-- FILTROS CON SELECTS ESTANDARIZADOS --}}
    <div class="bg-white dark:bg-slate-900 p-5 border border-t-0 border-slate-200 dark:border-slate-700 mb-6 rounded-b-lg shadow-sm">
        <form action="{{ route('admin.reports.attendance') }}" method="GET" class="flex flex-wrap items-end gap-4">
            <input type="hidden" name="tab" value="asistencia">
            
            {{-- Select Guardia - ESTANDARIZADO --}}
            <div class="min-w-[220px]">
                <label class="block text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Guardia</label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none z-10">
                        <i class="fas fa-shield-alt text-slate-400 group-focus-within:text-red-500 transition-colors"></i>
                    </div>
                    <select name="guardia_id" class="w-full pl-10 pr-10 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-semibold text-slate-700 dark:text-slate-300 focus:ring-2 focus:ring-red-500/20 focus:border-red-500 appearance-none cursor-pointer hover:bg-white dark:bg-slate-900 hover:border-slate-300 dark:border-slate-600 transition-all shadow-sm">
                        <option value="">Todas las Guardias</option>
                        @foreach($guardias as $g)
                            <option value="{{ $g->id }}" {{ $guardiaId == $g->id ? 'selected' : '' }}>{{ $g->name }}</option>
                        @endforeach
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-2 flex items-center pointer-events-none">
                        <div class="w-7 h-7 rounded-lg bg-slate-100 dark:bg-slate-800 flex items-center justify-center">
                            <i class="fas fa-chevron-down text-slate-400 text-xs"></i>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Select Semana - ESTANDARIZADO --}}
            <div class="min-w-[220px]">
                <label class="block text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Semana</label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none z-10">
                        <i class="fas fa-calendar-week text-slate-400 group-focus-within:text-red-500 transition-colors"></i>
                    </div>
                    <select name="week" class="w-full pl-10 pr-10 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-semibold text-slate-700 dark:text-slate-300 focus:ring-2 focus:ring-red-500/20 focus:border-red-500 appearance-none cursor-pointer hover:bg-white dark:bg-slate-900 hover:border-slate-300 dark:border-slate-600 transition-all shadow-sm">
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
                    <div class="absolute inset-y-0 right-0 pr-2 flex items-center pointer-events-none">
                        <div class="w-7 h-7 rounded-lg bg-slate-100 dark:bg-slate-800 flex items-center justify-center">
                            <i class="fas fa-chevron-down text-slate-400 text-xs"></i>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Fecha Desde - ESTANDARIZADO --}}
            <div class="min-w-[160px]">
                <label class="block text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Desde</label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none z-10">
                        <i class="fas fa-calendar text-slate-400 group-focus-within:text-red-500 transition-colors"></i>
                    </div>
                    <input type="date" name="from" value="{{ $from->format('Y-m-d') }}" class="w-full pl-10 pr-3 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-semibold text-slate-700 dark:text-slate-300 focus:ring-2 focus:ring-red-500/20 focus:border-red-500 hover:bg-white dark:bg-slate-900 hover:border-slate-300 dark:border-slate-600 transition-all shadow-sm">
                </div>
            </div>

            {{-- Fecha Hasta - ESTANDARIZADO --}}
            <div class="min-w-[160px]">
                <label class="block text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Hasta</label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none z-10">
                        <i class="fas fa-calendar text-slate-400 group-focus-within:text-red-500 transition-colors"></i>
                    </div>
                    <input type="date" name="to" value="{{ $to->format('Y-m-d') }}" class="w-full pl-10 pr-3 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-semibold text-slate-700 dark:text-slate-300 focus:ring-2 focus:ring-red-500/20 focus:border-red-500 hover:bg-white dark:bg-slate-900 hover:border-slate-300 dark:border-slate-600 transition-all shadow-sm">
                </div>
            </div>

            {{-- Botón Filtrar --}}
            <div class="flex gap-2">
                <button type="submit" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-slate-950 hover:bg-slate-900 text-white font-extrabold text-xs uppercase tracking-widest transition-all shadow-md hover:shadow-lg">
                    <i class="fas fa-filter"></i> Filtrar
                </button>
                <a href="{{ route('admin.reports.attendance') }}" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-slate-700 dark:text-slate-300 font-extrabold text-xs uppercase tracking-widest transition-all" title="Limpiar filtros">
                    <i class="fas fa-undo"></i>
                </a>
            </div>

            {{-- Botones de Exportación Profesionales --}}
            <div class="ml-auto flex gap-2">
                <a href="{{ route('admin.reports.attendance.export', ['format' => 'excel'] + request()->all()) }}" 
                   class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 font-extrabold text-xs uppercase tracking-widest transition-all shadow-sm hover:shadow-md">
                    <i class="fas fa-file-excel text-emerald-600"></i> Excel
                </a>
                <a href="{{ route('admin.reports.attendance.export', ['format' => 'pdf'] + request()->all()) }}" target="_blank"
                   class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 font-extrabold text-xs uppercase tracking-widest transition-all shadow-sm hover:shadow-md">
                    <i class="fas fa-file-pdf text-rose-600"></i> PDF
                </a>
            </div>
        </form>
    </div>

    {{-- SUB-TABS: Por Guardia / General --}}
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-t-lg">
        <div class="flex">
            <a href="{{ route('admin.reports.attendance', array_merge(request()->all(), ['view' => 'guardias'])) }}"
               class="flex items-center gap-2 px-5 py-3 text-sm font-semibold border-b-2 transition-colors
                      {{ $currentView !== 'general' ? 'text-red-600 border-red-600 bg-red-50' : 'text-slate-600 dark:text-slate-400 border-transparent hover:text-slate-800 dark:text-white' }}">
                <i class="fas fa-shield-alt"></i> Por Guardia
            </a>
            <a href="{{ route('admin.reports.attendance', array_merge(request()->all(), ['view' => 'general'])) }}"
               class="flex items-center gap-2 px-5 py-3 text-sm font-semibold border-b-2 transition-colors
                      {{ $currentView === 'general' ? 'text-red-600 border-red-600 bg-red-50' : 'text-slate-600 dark:text-slate-400 border-transparent hover:text-slate-800 dark:text-white' }}">
                <i class="fas fa-globe"></i> General
            </a>
        </div>
    </div>

    {{-- STATS CARDS --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 bg-white dark:bg-slate-900 border border-t-0 border-slate-200 dark:border-slate-700 p-4 mb-6">
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
        <div class="bg-slate-50 dark:bg-slate-800 rounded-lg p-3 border border-slate-100 dark:border-slate-800">
            <div class="flex items-center justify-between mb-1">
                <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">{{ $card['label'] }}</p>
                <i class="fas fa-{{ $card['icon'] }} text-{{ $card['color'] }}-400 text-xs"></i>
            </div>
            <p class="text-2xl font-bold text-{{ $card['color'] }}-600">{{ $card['value'] }}</p>
        </div>
        @endforeach
    </div>

    {{-- INFORME GENERAL --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <div class="lg:col-span-2 bg-white dark:bg-slate-900 rounded-lg shadow-sm border border-slate-200 dark:border-slate-700 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-bold text-slate-800 dark:text-white uppercase tracking-wide">
                    <i class="fas fa-chart-bar mr-2 text-red-500"></i>
                    {{ $currentView === 'general' ? 'Consolidado General' : ($activeGuardia ? $activeGuardia->name : 'Sin Guardia') }}
                </h3>
                <span class="text-xs text-slate-400">{{ $from->format('d/m/Y') }} — {{ $to->format('d/m/Y') }}</span>
            </div>
            <div class="space-y-3">
                @foreach($guardiaStats as $stat)
                <div class="flex items-center gap-3">
                    <div class="w-24 text-xs font-semibold text-slate-600 dark:text-slate-400 truncate">{{ $stat['label'] }}</div>
                    <div class="flex-1 h-2.5 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                        <div class="h-full bg-{{ $stat['color'] }}-500 rounded-full" style="width: {{ min($stat['value'], 100) }}%"></div>
                    </div>
                    <div class="w-12 text-right">
                        <span class="text-xs font-bold text-{{ $stat['color'] }}-600">{{ $stat['value'] }}%</span>
                    </div>
                    <div class="w-8 text-right text-xs text-slate-400 font-semibold">{{ $stat['count'] }}</div>
                </div>
                @endforeach
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-lg shadow-sm border border-slate-200 dark:border-slate-700 p-5">
            <h3 class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-4">Resumen</h3>
            <div class="text-center mb-4">
                <div class="text-5xl font-black text-slate-800 dark:text-white">{{ $generalPercentage }}%</div>
                <div class="text-xs text-slate-500 dark:text-slate-400 mt-1 uppercase tracking-wide">Cumplimiento General</div>
            </div>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between py-1.5 border-b border-slate-100 dark:border-slate-800">
                    <span class="text-slate-500 dark:text-slate-400">Total personal</span>
                    <span class="font-bold text-slate-800 dark:text-white">{{ $totalPersonnel }}</span>
                </div>
                <div class="flex justify-between py-1.5 border-b border-slate-100 dark:border-slate-800">
                    <span class="text-slate-500 dark:text-slate-400">Periodo</span>
                    <span class="font-bold text-slate-800 dark:text-white text-xs">{{ $from->format('d/m') }} — {{ $to->format('d/m') }}</span>
                </div>
                <div class="flex justify-between py-1.5 border-b border-slate-100 dark:border-slate-800">
                    <span class="text-slate-500 dark:text-slate-400">Registros evaluados</span>
                    <span class="font-bold text-slate-800 dark:text-white">{{ ($stats['fulfilled'] ?? 0) + ($stats['absences'] ?? 0) + ($stats['permissions'] ?? 0) + ($stats['licenses'] ?? 0) }}</span>
                </div>
                <div class="flex justify-between py-1.5">
                    <span class="text-slate-500 dark:text-slate-400">Refuerzos</span>
                    <span class="font-bold text-teal-600">{{ $stats['reinforcements'] ?? 0 }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- INFORME POR VOLUNTARIO --}}
    @php
        $titulares = collect($firefighterStats)->reject(fn($ff) => (bool) ($ff['is_refuerzo'] ?? false))->values();
    @endphp

    <div class="bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden mb-6">
        <div class="p-4 border-b border-slate-200 dark:border-slate-700 bg-emerald-50 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-emerald-600 text-white flex items-center justify-center">
                    <i class="fas fa-user-check"></i>
                </div>
                <div>
                    <h3 class="text-base font-black text-slate-800 dark:text-white uppercase tracking-wide">Personal de Guardia</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Voluntarios titulares y con reemplazos del período</p>
                </div>
            </div>
            <span class="text-sm font-black text-emerald-700 bg-white dark:bg-slate-900 px-3 py-1 rounded-full border border-emerald-200">{{ $titulares->count() }} voluntarios</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700">
                    <tr>
                        <th class="text-left py-3 px-4 font-semibold text-slate-600 dark:text-slate-400 text-xs uppercase">Voluntario</th>
                        <th class="text-center py-3 px-2 font-semibold text-slate-500 dark:text-slate-400 text-xs uppercase">Noches</th>
                        <th class="text-center py-3 px-2 font-semibold text-emerald-600 text-xs uppercase">Asistió</th>
                        <th class="text-center py-3 px-2 font-semibold text-rose-600 text-xs uppercase">Aus.</th>
                        <th class="text-center py-3 px-2 font-semibold text-amber-600 text-xs uppercase">Perm.</th>
                        <th class="text-center py-3 px-2 font-semibold text-blue-600 text-xs uppercase">Lic.</th>
                        <th class="text-center py-3 px-2 font-semibold text-slate-500 dark:text-slate-400 text-xs uppercase">Inhab.</th>
                        <th class="text-center py-3 px-3 font-semibold text-slate-600 dark:text-slate-400 text-xs uppercase">% Asistencia</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($titulares as $ff)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                        <td class="py-3 px-4">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 bg-emerald-100 rounded-full flex items-center justify-center text-emerald-700 font-bold text-xs flex-shrink-0">
                                    {{ $ff['code'] }}
                                </div>
                                <div>
                                    <span class="font-bold text-slate-800 dark:text-white text-xs block">{{ $ff['name'] }}</span>
                                    @if(!empty($ff['guardia_name']))
                                    <span class="text-[10px] text-slate-400">{{ $ff['guardia_name'] }}</span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="py-3 px-2 text-center font-bold text-slate-700 dark:text-slate-300">{{ $ff['shift'] }}</td>
                        <td class="py-3 px-2 text-center">
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded bg-emerald-100 text-emerald-700 font-bold text-xs">{{ $ff['fulfilled'] }}</span>
                        </td>
                        <td class="py-3 px-2 text-center">
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded {{ $ff['absences'] > 0 ? 'bg-rose-100 text-rose-700' : 'bg-slate-100 dark:bg-slate-800 text-slate-400' }} font-bold text-xs">{{ $ff['absences'] }}</span>
                        </td>
                        <td class="py-3 px-2 text-center">
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded {{ $ff['permissions'] > 0 ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 dark:bg-slate-800 text-slate-400' }} font-bold text-xs">{{ $ff['permissions'] }}</span>
                        </td>
                        <td class="py-3 px-2 text-center">
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded {{ $ff['licenses'] > 0 ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 dark:bg-slate-800 text-slate-400' }} font-bold text-xs">{{ $ff['licenses'] }}</span>
                        </td>
                        <td class="py-3 px-2 text-center">
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded {{ $ff['disabled'] > 0 ? 'bg-slate-200 text-slate-600 dark:text-slate-400' : 'bg-slate-100 dark:bg-slate-800 text-slate-400' }} font-bold text-xs">{{ $ff['disabled'] }}</span>
                        </td>
                        <td class="py-3 px-3">
                            <div class="flex items-center gap-2">
                                <div class="flex-1 h-1.5 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden min-w-[40px]">
                                    <div class="h-full rounded-full {{ $ff['percentage'] >= 90 ? 'bg-emerald-500' : ($ff['percentage'] >= 75 ? 'bg-amber-500' : 'bg-rose-500') }}" style="width: {{ $ff['percentage'] }}%"></div>
                                </div>
                                <span class="text-xs font-bold {{ $ff['percentage'] >= 90 ? 'text-emerald-600' : ($ff['percentage'] >= 75 ? 'text-amber-600' : 'text-rose-600') }} w-8 text-right">{{ $ff['percentage'] }}%</span>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-10 text-center text-slate-400">
                            <i class="fas fa-inbox text-2xl mb-2 block"></i>
                            No hay voluntarios con registros en el período seleccionado
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- TAB: PERMISOS --}}
    {{-- ============================================================ --}}
    @elseif($activeTab === 'permisos')

    <div class="bg-white dark:bg-slate-900 border border-t-0 border-slate-200 dark:border-slate-700 p-4 mb-6">
        <form action="{{ route('admin.reports.attendance') }}" method="GET" class="flex flex-wrap items-end gap-3">
            <input type="hidden" name="tab" value="permisos">
            <div>
                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Guardia</label>
                <select name="guardia_id" class="px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-md text-sm w-44">
                    <option value="">Todas</option>
                    @foreach($guardias as $g)
                        <option value="{{ $g->id }}" {{ $guardiaId == $g->id ? 'selected' : '' }}>{{ $g->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Desde</label>
                <input type="date" name="from" value="{{ $from->format('Y-m-d') }}" class="px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-md text-sm w-36">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Hasta</label>
                <input type="date" name="to" value="{{ $to->format('Y-m-d') }}" class="px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-md text-sm w-36">
            </div>
            <button type="submit" class="bg-amber-500 hover:bg-amber-600 text-white font-semibold py-2 px-4 rounded-md text-sm flex items-center gap-2">
                <i class="fas fa-filter"></i> Filtrar
            </button>
        </form>
    </div>

    <div class="bg-white dark:bg-slate-900 rounded-lg shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden mb-6">
        <div class="p-4 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
            <h3 class="text-base font-bold text-slate-800 dark:text-white uppercase tracking-wide flex items-center gap-2">
                <i class="fas fa-calendar-alt text-amber-500"></i> Permisos y Licencias
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700">
                    <tr>
                        <th class="text-left py-3 px-4 font-semibold text-slate-600 dark:text-slate-400 text-xs uppercase">Voluntario</th>
                        <th class="text-left py-3 px-3 font-semibold text-slate-600 dark:text-slate-400 text-xs uppercase">Guardia</th>
                        <th class="text-center py-3 px-3 font-semibold text-amber-600 text-xs uppercase">Permisos</th>
                        <th class="text-center py-3 px-3 font-semibold text-blue-600 text-xs uppercase">Licencias</th>
                        <th class="text-center py-3 px-3 font-semibold text-slate-500 dark:text-slate-400 text-xs uppercase">Inhabilitados</th>
                        <th class="text-center py-3 px-3 font-semibold text-slate-600 dark:text-slate-400 text-xs uppercase">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($firefighterStats->filter(fn($f) => ($f['permissions'] + $f['licenses'] + $f['disabled']) > 0)->sortByDesc(fn($f) => $f['permissions'] + $f['licenses'] + $f['disabled']) as $ff)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800">
                        <td class="py-3 px-4">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 bg-amber-100 rounded-full flex items-center justify-center text-amber-700 font-bold text-xs flex-shrink-0">
                                    {{ $ff['code'] }}
                                </div>
                                <span class="font-medium text-slate-800 dark:text-white text-xs">{{ $ff['name'] }}</span>
                            </div>
                        </td>
                        <td class="py-3 px-3 text-xs text-slate-500 dark:text-slate-400">{{ $ff['guardia_name'] ?? '—' }}</td>
                        <td class="py-3 px-3 text-center">
                            <span class="inline-flex items-center justify-center w-7 h-7 rounded-full {{ $ff['permissions'] > 0 ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 dark:bg-slate-800 text-slate-400' }} font-bold text-xs">{{ $ff['permissions'] }}</span>
                        </td>
                        <td class="py-3 px-3 text-center">
                            <span class="inline-flex items-center justify-center w-7 h-7 rounded-full {{ $ff['licenses'] > 0 ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 dark:bg-slate-800 text-slate-400' }} font-bold text-xs">{{ $ff['licenses'] }}</span>
                        </td>
                        <td class="py-3 px-3 text-center">
                            <span class="inline-flex items-center justify-center w-7 h-7 rounded-full {{ $ff['disabled'] > 0 ? 'bg-slate-200 text-slate-600 dark:text-slate-400' : 'bg-slate-100 dark:bg-slate-800 text-slate-400' }} font-bold text-xs">{{ $ff['disabled'] }}</span>
                        </td>
                        <td class="py-3 px-3 text-center font-bold text-slate-700 dark:text-slate-300">{{ $ff['permissions'] + $ff['licenses'] + $ff['disabled'] }}</td>
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
