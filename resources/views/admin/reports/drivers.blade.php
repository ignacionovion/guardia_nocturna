@extends('layouts.app')

@section('content')
    <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4 border-b border-slate-200 pb-6">
        <div>
            <h1 class="text-3xl font-extrabold text-slate-800 tracking-tight flex items-center uppercase">
                <i class="fas fa-id-card mr-3 text-red-700"></i> Reporte de Conductores
            </h1>
            <p class="text-slate-500 mt-1 font-medium">Ranking de conductores por asistencias (día de turno)</p>
        </div>

        <div class="flex items-center gap-2">
            {{-- Botones de Exportación --}}
            <a href="{{ route('admin.reports.drivers.export', ['format' => 'excel'] + request()->all()) }}" 
               class="bg-emerald-600 hover:bg-emerald-700 text-white font-black py-2.5 px-4 rounded-lg text-sm transition-all shadow-md hover:shadow-lg flex items-center gap-2">
                <i class="fas fa-file-excel"></i> Excel
            </a>
            <a href="{{ route('admin.reports.drivers.export', ['format' => 'pdf'] + request()->all()) }}" target="_blank"
               class="bg-rose-600 hover:bg-rose-700 text-white font-black py-2.5 px-4 rounded-lg text-sm transition-all shadow-md hover:shadow-lg flex items-center gap-2">
                <i class="fas fa-file-pdf"></i> PDF
            </a>
        </div>
    </div>

    <div class="flex flex-col md:flex-row gap-2 mb-6">
        <a href="{{ route('admin.reports.index') }}" class="px-4 py-2 rounded-lg text-sm font-bold border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 transition">
            <i class="fas fa-chart-line mr-2 text-slate-400"></i> Asistencia
        </a>
        <a href="{{ route('admin.reports.preventivas') }}" class="px-4 py-2 rounded-lg text-sm font-bold border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 transition">
            <i class="fas fa-clipboard-list mr-2 text-slate-400"></i> Preventivas
        </a>
        <a href="{{ route('admin.reports.replacements') }}" class="px-4 py-2 rounded-lg text-sm font-bold border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 transition">
            <i class="fas fa-right-left mr-2 text-slate-400"></i> Reemplazos
        </a>
        <a href="{{ route('admin.reports.drivers') }}" class="px-4 py-2 rounded-lg text-sm font-bold bg-slate-800 text-white border border-slate-800 shadow-sm">
            <i class="fas fa-truck mr-2"></i> Conductores
        </a>
        <a href="{{ route('admin.reports.emergencies') }}" class="px-4 py-2 rounded-lg text-sm font-bold border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 transition">
            <i class="fas fa-ambulance mr-2 text-red-600"></i> Emergencias
        </a>
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
