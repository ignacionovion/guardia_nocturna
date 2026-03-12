@extends('layouts.modern')

@section('title', 'Reportes - ' . branding()->nombre_empresa)
@section('page-title', 'Reportes')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-500 to-indigo-700 flex items-center justify-center shadow-lg shadow-indigo-500/25">
                <i class="fas fa-chart-line text-white text-lg"></i>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Reportes de Asistencia</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400">Análisis detallado por guardia, semana y período</p>
            </div>
        </div>
        
        {{-- Filtros --}}
        <form action="{{ route('admin.reports.index') }}" method="GET" class="flex flex-wrap items-center gap-3">
            <div class="flex items-center gap-2 px-4 py-2.5 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm">
                <i class="fas fa-calendar text-slate-400 text-sm"></i>
                <select name="month" class="bg-transparent border-0 text-sm font-medium text-slate-700 dark:text-slate-300 focus:ring-0 cursor-pointer pr-8">
                    @foreach(range(1, 12) as $m)
                        <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                            {{ ucfirst(\Carbon\Carbon::create()->month($m)->locale('es')->monthName) }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="flex items-center gap-2 px-4 py-2.5 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm">
                <select name="year" class="bg-transparent border-0 text-sm font-medium text-slate-700 dark:text-slate-300 focus:ring-0 cursor-pointer pr-8">
                    @foreach(range(now()->year - 2, now()->year) as $y)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 bg-slate-900 dark:bg-white text-white dark:text-slate-900 text-sm font-semibold rounded-xl hover:bg-slate-800 dark:hover:bg-slate-100 transition-all shadow-sm">
                <i class="fas fa-search text-xs"></i>
                <span>Aplicar</span>
            </button>
        </form>
    </div>

    {{-- Tabs --}}
    @include('admin.reports._tabs')

    {{-- KPIs --}}
    @isset($selectedMonthKpis)
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-xl bg-violet-100 dark:bg-violet-900/30 flex items-center justify-center">
                    <i class="fas fa-right-left text-violet-600 dark:text-violet-400"></i>
                </div>
                <span class="text-2xl font-black text-slate-900 dark:text-white">{{ $selectedMonthKpis['reemplazo'] ?? 0 }}</span>
            </div>
            <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Reemplazos</p>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-xl bg-rose-100 dark:bg-rose-900/30 flex items-center justify-center">
                    <i class="fas fa-user-slash text-rose-600 dark:text-rose-400"></i>
                </div>
                <span class="text-2xl font-black text-slate-900 dark:text-white">{{ $selectedMonthKpis['ausente'] ?? 0 }}</span>
            </div>
            <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Ausentes</p>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-xl bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
                    <i class="fas fa-id-badge text-amber-600 dark:text-amber-400"></i>
                </div>
                <span class="text-2xl font-black text-slate-900 dark:text-white">{{ $selectedMonthKpis['permiso'] ?? 0 }}</span>
            </div>
            <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Permisos</p>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-xl bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                    <i class="fas fa-notes-medical text-blue-600 dark:text-blue-400"></i>
                </div>
                <span class="text-2xl font-black text-slate-900 dark:text-white">{{ $selectedMonthKpis['licencia'] ?? 0 }}</span>
            </div>
            <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Licencias</p>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center">
                    <i class="fas fa-user-lock text-slate-600 dark:text-slate-400"></i>
                </div>
                <span class="text-2xl font-black text-slate-900 dark:text-white">{{ $selectedMonthKpis['disabled'] ?? 0 }}</span>
            </div>
            <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Inhabilitados</p>
        </div>
    </div>
    @endisset

    {{-- Tablas por Guardia --}}
    <div class="space-y-6">
        @foreach($guardias as $guardia)
            @if($guardia->bomberos->count() > 0)
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
                {{-- Header de Guardia --}}
                <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-red-500 to-red-700 flex items-center justify-center">
                            <i class="fas fa-shield-halved text-white text-sm"></i>
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-slate-900 dark:text-white">{{ $guardia->name }}</h2>
                            <p class="text-xs text-slate-500 dark:text-slate-400">{{ $guardia->bomberos->count() }} voluntarios activos</p>
                        </div>
                    </div>
                </div>

                {{-- Tabla --}}
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-slate-50 dark:bg-slate-800/50">
                                <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider sticky left-0 bg-slate-50 dark:bg-slate-800/50 z-10 border-r border-slate-200 dark:border-slate-700">
                                    Voluntario
                                </th>
                                @foreach($weeksInMonth as $weekNum)
                                <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                    S{{ $weekNum }}
                                </th>
                                @endforeach
                                <th class="px-5 py-3 text-center text-xs font-semibold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider bg-indigo-50 dark:bg-indigo-900/20">
                                    Mes
                                </th>
                                <th class="px-5 py-3 text-center text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider bg-slate-100 dark:bg-slate-800">
                                    Año
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @foreach($guardia->bomberos as $user)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors group">
                                <td class="px-6 py-3 whitespace-nowrap sticky left-0 bg-white dark:bg-slate-900 group-hover:bg-slate-50 dark:group-hover:bg-slate-800/50 transition-colors z-10 border-r border-slate-100 dark:border-slate-800">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-xs font-bold text-slate-600 dark:text-slate-400">
                                            {{ substr($user->nombres, 0, 1) }}{{ substr($user->apellido_paterno, 0, 1) }}
                                        </div>
                                        <span class="text-sm font-medium text-slate-700 dark:text-slate-300">{{ $user->nombres }} {{ $user->apellido_paterno }}</span>
                                    </div>
                                </td>
                                @foreach($weeksInMonth as $weekNum)
                                    @php $stats = $user->weekly_stats->get($weekNum); @endphp
                                <td class="px-4 py-3 text-center">
                                    @if($stats && ($stats['shifts'] ?? 0) > 0)
                                    <span class="inline-flex items-center px-2 py-1 rounded-lg text-xs font-semibold bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400">
                                        {{ $stats['shifts'] }}
                                    </span>
                                    @else
                                    <span class="text-slate-300 dark:text-slate-600">—</span>
                                    @endif
                                </td>
                                @endforeach
                                <td class="px-5 py-3 text-center bg-indigo-50/50 dark:bg-indigo-900/10">
                                    <span class="text-sm font-bold text-indigo-700 dark:text-indigo-400">{{ $user->month_shifts ?? 0 }}</span>
                                </td>
                                <td class="px-5 py-3 text-center bg-slate-50 dark:bg-slate-800/50">
                                    <span class="text-sm font-semibold text-slate-600 dark:text-slate-400">{{ $user->year_shifts ?? 0 }}</span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        @endforeach

        @if($guardias->isEmpty())
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-12 text-center">
            <div class="w-16 h-16 mx-auto rounded-2xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center mb-4">
                <i class="fas fa-folder-open text-2xl text-slate-400"></i>
            </div>
            <h3 class="text-lg font-bold text-slate-700 dark:text-slate-300 mb-2">Sin datos</h3>
            <p class="text-sm text-slate-500 dark:text-slate-400">No se encontraron registros para el período seleccionado.</p>
        </div>
        @endif
    </div>
</div>
@endsection
