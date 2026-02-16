@extends('layouts.app')

@section('content')
    <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4 border-b border-slate-200 pb-6">
        <div>
            <h1 class="text-3xl font-extrabold text-slate-800 tracking-tight flex items-center uppercase">
                <i class="fas fa-id-card mr-3 text-red-700"></i> Reporte de Conductores
            </h1>
            <p class="text-slate-500 mt-1 font-medium">Ranking de conductores por asistencias (día de turno)</p>
        </div>

        <div class="bg-white p-1.5 rounded-lg shadow-sm border border-slate-200">
            <form action="{{ route('admin.reports.drivers') }}" method="GET" class="flex items-center space-x-2">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-calendar-days text-slate-400"></i>
                    </div>
                    <select name="month" class="pl-10 pr-8 py-2 bg-slate-50 border-transparent focus:border-blue-500 focus:bg-white focus:ring-0 rounded-md text-sm font-medium text-slate-700 cursor-pointer hover:bg-slate-100 transition-colors">
                        @foreach(range(1, 12) as $m)
                            <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                                {{ ucfirst(\Carbon\Carbon::create()->month($m)->locale('es')->monthName) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="relative">
                    <select name="year" class="pl-4 pr-8 py-2 bg-slate-50 border-transparent focus:border-blue-500 focus:bg-white focus:ring-0 rounded-md text-sm font-medium text-slate-700 cursor-pointer hover:bg-slate-100 transition-colors">
                        @foreach(range(now()->year - 2, now()->year) as $y)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>
                                {{ $y }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="bg-slate-800 hover:bg-slate-700 text-white font-bold py-2 px-4 rounded-md text-sm transition shadow-sm flex items-center">
                    <i class="fas fa-filter mr-2"></i> Filtrar
                </button>
            </form>
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
        <a href="{{ route('admin.reports.drivers') }}" class="px-4 py-2 rounded-lg text-sm font-bold border border-red-200 bg-red-50 text-red-800 hover:bg-red-100 transition">
            <i class="fas fa-id-card mr-2 text-red-500"></i> Conductores
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
