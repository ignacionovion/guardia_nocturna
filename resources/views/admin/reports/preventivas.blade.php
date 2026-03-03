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
               class="flex items-center gap-2 px-6 py-4 text-sm font-semibold whitespace-nowrap border-b-2 transition-colors text-red-600 border-red-600 bg-red-50">
                <i class="fas fa-clipboard-list"></i> Preventivas
            </a>
            <a href="{{ route('admin.reports.replacements') }}"
               class="flex items-center gap-2 px-6 py-4 text-sm font-semibold whitespace-nowrap border-b-2 border-transparent text-slate-600 hover:text-slate-800 transition-colors">
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
        <form action="{{ route('admin.reports.preventivas') }}" method="GET" class="flex flex-wrap items-end gap-4">
            {{-- Select Guardia - ESTANDARIZADO --}}
            <div class="min-w-[220px]">
                <label class="block text-xs font-black text-slate-500 uppercase tracking-wider mb-2">Guardia</label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none z-10">
                        <i class="fas fa-shield-alt text-slate-400 group-focus-within:text-red-500 transition-colors"></i>
                    </div>
                    <select name="guardia_id" class="w-full pl-10 pr-10 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold text-slate-700 focus:ring-2 focus:ring-red-500/20 focus:border-red-500 appearance-none cursor-pointer hover:bg-white hover:border-slate-300 transition-all shadow-sm">
                        <option value="">Todas las Guardias</option>
                        @foreach($guardias ?? [] as $g)
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

            {{-- Select Preventiva - ESTANDARIZADO --}}
            <div class="min-w-[220px]">
                <label class="block text-xs font-black text-slate-500 uppercase tracking-wider mb-2">Preventiva</label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none z-10">
                        <i class="fas fa-clipboard-list text-slate-400 group-focus-within:text-red-500 transition-colors"></i>
                    </div>
                    <select name="event_id" class="w-full pl-10 pr-10 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold text-slate-700 focus:ring-2 focus:ring-red-500/20 focus:border-red-500 appearance-none cursor-pointer hover:bg-white hover:border-slate-300 transition-all shadow-sm">
                        <option value="">Todas las preventivas</option>
                        @foreach($events ?? [] as $ev)
                            <option value="{{ $ev->id }}" {{ (string)($eventId ?? '') === (string)$ev->id ? 'selected' : '' }}>{{ $ev->title }}</option>
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
                <a href="{{ route('admin.reports.preventivas') }}" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-extrabold text-xs uppercase tracking-widest transition-all" title="Limpiar filtros">
                    <i class="fas fa-undo"></i>
                </a>
            </div>

            {{-- Botones de Exportación Profesionales --}}
            <div class="ml-auto flex gap-2">
                <a href="{{ route('admin.reports.preventivas', ['format' => 'excel'] + request()->all()) }}" 
                   class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 font-extrabold text-xs uppercase tracking-widest transition-all shadow-sm hover:shadow-md">
                    <i class="fas fa-file-excel text-emerald-600"></i> Excel
                </a>
                <a href="{{ route('admin.reports.preventivas', ['format' => 'pdf'] + request()->all()) }}" target="_blank"
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
                ['label' => 'Asignaciones', 'value' => $kpis['total_assignments'] ?? 0, 'color' => 'blue', 'icon' => 'clipboard-list'],
                ['label' => 'Asistieron', 'value' => $kpis['present'] ?? 0, 'color' => 'emerald', 'icon' => 'check-circle'],
                ['label' => 'Pendientes', 'value' => $kpis['pending'] ?? 0, 'color' => 'amber', 'icon' => 'clock'],
                ['label' => 'Ausencias', 'value' => $kpis['absences'] ?? 0, 'color' => 'rose', 'icon' => 'times-circle'],
                ['label' => 'Permisos', 'value' => $kpis['permissions'] ?? 0, 'color' => 'purple', 'icon' => 'calendar-check'],
                ['label' => 'Licencias', 'value' => $kpis['licenses'] ?? 0, 'color' => 'cyan', 'icon' => 'file-medical'],
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

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-10">
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden lg:col-span-1">
            <div class="bg-slate-50 px-6 py-4 border-b border-slate-200">
                <div class="text-sm font-black text-slate-800 uppercase tracking-wide">Resumen por preventiva</div>
            </div>
            <div class="p-4 space-y-2">
                @forelse($byEvent as $r)
                    <div class="flex items-center justify-between bg-slate-50 border border-slate-200 rounded-lg px-3 py-2">
                        <div class="min-w-0">
                            <div class="text-sm font-bold text-slate-700 truncate" title="{{ $r['event'] }}">{{ $r['event'] }}</div>
                            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Total: {{ $r['total'] ?? 0 }} · Asistieron: {{ $r['present'] ?? 0 }}</div>
                        </div>
                        <div class="text-xs font-black text-slate-700 bg-white border border-slate-200 rounded-full px-2 py-1">
                            {{ $r['pending'] ?? 0 }}
                        </div>
                    </div>
                @empty
                    <div class="text-sm text-slate-400">Sin datos para el rango seleccionado.</div>
                @endforelse
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden lg:col-span-2">
            <div class="bg-slate-50 px-6 py-4 border-b border-slate-200 flex justify-between items-center">
                <div class="text-sm font-black text-slate-800 uppercase tracking-wide">Detalle</div>
                <div class="text-xs font-bold text-slate-400 uppercase tracking-widest">{{ $kpis['range_label'] ?? '' }}</div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Día</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Turno</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Preventiva</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Bombero</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Estado</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-100">
                        @forelse($rows as $a)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-3 whitespace-nowrap text-sm font-bold text-slate-700">
                                    {{ $a->shift?->shift_date?->format('d-m-Y') ?? '—' }}
                                </td>
                                <td class="px-6 py-3 whitespace-nowrap text-sm text-slate-600">
                                    {{ substr((string)($a->shift?->start_time ?? ''), 0, 5) }} - {{ substr((string)($a->shift?->end_time ?? ''), 0, 5) }}
                                </td>
                                <td class="px-6 py-3 whitespace-nowrap text-sm text-slate-600">
                                    {{ $a->shift?->event?->title ?? '—' }}
                                </td>
                                <td class="px-6 py-3 whitespace-nowrap text-sm text-slate-700">
                                    {{ trim((string)($a->firefighter?->apellido_paterno ?? '') . ' ' . (string)($a->firefighter?->nombres ?? '')) }}
                                </td>
                                <td class="px-6 py-3 whitespace-nowrap">
                                    @if($a->attendance)
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[11px] font-black uppercase tracking-widest bg-emerald-50 text-emerald-800 border border-emerald-200">Asistió</span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[11px] font-black uppercase tracking-widest bg-slate-100 text-slate-700 border border-slate-200">Pendiente</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-10 text-center text-slate-400">Sin datos para el rango seleccionado.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
