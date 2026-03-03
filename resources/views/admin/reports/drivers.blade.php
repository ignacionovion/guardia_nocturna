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
            <a href="{{ route('admin.reports.attendance') }}" class="flex items-center gap-2 px-6 py-4 text-sm font-semibold whitespace-nowrap border-b-2 border-transparent text-slate-600 hover:text-slate-800 transition-colors">
                <i class="fas fa-calendar-check"></i> Asistencia
            </a>
            <a href="{{ route('admin.reports.preventivas') }}" class="flex items-center gap-2 px-6 py-4 text-sm font-semibold whitespace-nowrap border-b-2 border-transparent text-slate-600 hover:text-slate-800 transition-colors">
                <i class="fas fa-clipboard-list"></i> Preventivas
            </a>
            <a href="{{ route('admin.reports.replacements') }}" class="flex items-center gap-2 px-6 py-4 text-sm font-semibold whitespace-nowrap border-b-2 border-transparent text-slate-600 hover:text-slate-800 transition-colors">
                <i class="fas fa-exchange-alt"></i> Reemplazos
            </a>
            <a href="{{ route('admin.reports.refuerzos') }}" class="flex items-center gap-2 px-6 py-4 text-sm font-semibold whitespace-nowrap border-b-2 border-transparent text-slate-600 hover:text-slate-800 transition-colors">
                <i class="fas fa-user-plus"></i> Refuerzos
            </a>
            <a href="{{ route('admin.reports.drivers') }}" class="flex items-center gap-2 px-6 py-4 text-sm font-semibold whitespace-nowrap border-b-2 transition-colors text-red-600 border-red-600 bg-red-50">
                <i class="fas fa-truck"></i> Conductores
            </a>
            <a href="{{ route('admin.reports.emergencies') }}" class="flex items-center gap-2 px-6 py-4 text-sm font-semibold whitespace-nowrap border-b-2 border-transparent text-slate-600 hover:text-slate-800 transition-colors">
                <i class="fas fa-ambulance text-red-600"></i> Emergencias
            </a>
        </div>
    </div>

    {{-- FILTROS CON SELECTS ESTANDARIZADOS --}}
    <div class="bg-white p-5 border border-t-0 border-slate-200 mb-6 rounded-b-lg shadow-sm">
        <form action="{{ route('admin.reports.drivers') }}" method="GET" class="flex flex-wrap items-end gap-4">
            
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

            {{-- Botón Filtrar --}}
            <div class="flex gap-2">
                <button type="submit" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-slate-950 hover:bg-slate-900 text-white font-extrabold text-xs uppercase tracking-widest transition-all shadow-md hover:shadow-lg">
                    <i class="fas fa-filter"></i> Filtrar
                </button>
                <a href="{{ route('admin.reports.drivers') }}" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-extrabold text-xs uppercase tracking-widest transition-all" title="Limpiar filtros">
                    <i class="fas fa-undo"></i>
                </a>
            </div>

            {{-- Botones de Exportación Profesionales --}}
            <div class="ml-auto flex gap-2">
                <a href="{{ route('admin.reports.drivers.export', ['format' => 'excel'] + request()->all()) }}" 
                   class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 font-extrabold text-xs uppercase tracking-widest transition-all shadow-sm hover:shadow-md">
                    <i class="fas fa-file-excel text-emerald-600"></i> Excel
                </a>
                <a href="{{ route('admin.reports.drivers.export', ['format' => 'pdf'] + request()->all()) }}" target="_blank"
                   class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 font-extrabold text-xs uppercase tracking-widest transition-all shadow-sm hover:shadow-md">
                    <i class="fas fa-file-pdf text-rose-600"></i> PDF
                </a>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="bg-slate-50 px-6 py-4 border-b border-slate-200 flex justify-between items-center">
            <h2 class="text-lg font-bold text-slate-800 flex items-center uppercase tracking-wide">
                <span class="w-2 h-6 bg-red-600 rounded mr-3"></span>
                Top Conductores
            </h2>
            <span class="text-xs font-bold bg-white text-slate-600 px-3 py-1 rounded-full border border-slate-200 shadow-sm">
                {{ ucfirst(\Carbon\Carbon::create()->month($month)->locale('es')->monthName) }} {{ $year }}
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Conductor</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Guardia</th>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-bold text-emerald-600 uppercase tracking-wider">Turnos presentes</th>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-bold text-slate-600 uppercase tracking-wider">Días únicos</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-slate-100">
                    @forelse($topDrivers as $i => $row)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-3 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-slate-900 text-white flex items-center justify-center font-black text-xs">{{ $i + 1 }}</div>
                                    <div class="text-sm font-bold text-slate-700">{{ $row['name'] ?? '' }}</div>
                                </div>
                            </td>
                            <td class="px-6 py-3 whitespace-nowrap text-sm font-bold text-slate-700">{{ $row['guardia'] ?? '' }}</td>
                            <td class="px-6 py-3 whitespace-nowrap text-center text-sm font-black text-emerald-700">{{ $row['present_shifts'] ?? 0 }}</td>
                            <td class="px-6 py-3 whitespace-nowrap text-center text-sm font-bold text-slate-700">{{ $row['unique_days'] ?? 0 }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-10 text-center text-sm text-slate-400">Sin datos para el periodo seleccionado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
