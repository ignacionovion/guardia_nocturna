@extends('layouts.modern')

@section('title', 'Reportes - ' . branding()->nombre_empresa)
@section('page-title', 'Reportes')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <x-ui.page-header title="Reportes de Asistencia" subtitle="Análisis detallado por guardia, semana y período" icon="fas fa-chart-line" iconVariant="blue">
        <form action="{{ route('admin.reports.index') }}" method="GET" class="flex flex-wrap items-center gap-3">
            <div class="flex items-center gap-2 px-4 py-2.5 card-base">
                <i class="fas fa-calendar text-slate-400 text-sm"></i>
                <select name="month" class="bg-transparent border-0 text-sm font-medium text-slate-700 dark:text-slate-300 focus:ring-0 cursor-pointer pr-8">
                    @foreach(range(1, 12) as $m)
                        <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                            {{ ucfirst(\Carbon\Carbon::create()->month($m)->locale('es')->monthName) }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="flex items-center gap-2 px-4 py-2.5 card-base">
                <select name="year" class="bg-transparent border-0 text-sm font-medium text-slate-700 dark:text-slate-300 focus:ring-0 cursor-pointer pr-8">
                    @foreach(range(now()->year - 2, now()->year) as $y)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
            </div>

            <x-ui.button type="submit" variant="primary" size="md" icon="fas fa-search">
                Aplicar
            </x-ui.button>
        </form>
    </x-ui.page-header>

    {{-- Tabs --}}
    @include('admin.reports._tabs')

    {{-- KPIs --}}
    @isset($selectedMonthKpis)
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
        <x-ui.stat-card
            title="Reemplazos"
            :value="$selectedMonthKpis['reemplazo'] ?? 0"
            icon="fas fa-right-left"
            color="purple"
        />

        <x-ui.stat-card
            title="Ausentes"
            :value="$selectedMonthKpis['ausente'] ?? 0"
            icon="fas fa-user-slash"
            color="rose"
        />

        <x-ui.stat-card
            title="Permisos"
            :value="$selectedMonthKpis['permiso'] ?? 0"
            icon="fas fa-id-badge"
            color="amber"
        />

        <x-ui.stat-card
            title="Licencias"
            :value="$selectedMonthKpis['licencia'] ?? 0"
            icon="fas fa-notes-medical"
            color="blue"
        />

        <x-ui.stat-card
            title="Inhabilitados"
            :value="$selectedMonthKpis['disabled'] ?? 0"
            icon="fas fa-user-lock"
            color="slate"
        />
    </div>
    @endisset

    {{-- Tablas por Guardia --}}
    <div class="space-y-6">
        @foreach($guardias as $guardia)
            @if($guardia->bomberos->count() > 0)
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                {{-- Header de Guardia --}}
                <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-red-500 to-red-700 flex items-center justify-center">
                            <i class="fas fa-shield-halved text-white text-sm"></i>
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-[#1e293b]">{{ $guardia->name }}</h2>
                            <p class="text-xs text-[#475569]">{{ $guardia->bomberos->count() }} voluntarios activos</p>
                        </div>
                    </div>
                </div>

                {{-- Tabla --}}
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-white">
                                <th class="px-6 py-3 text-left text-xs font-semibold text-[#475569] uppercase tracking-wider sticky left-0 bg-white z-10 border-r border-slate-200">
                                    Voluntario
                                </th>
                                @foreach($weeksInMonth as $weekNum)
                                <th class="px-4 py-3 text-center text-xs font-semibold text-[#475569] uppercase tracking-wider">
                                    S{{ $weekNum }}
                                </th>
                                @endforeach
                                <th class="px-5 py-3 text-center text-xs font-semibold text-indigo-600 uppercase tracking-wider bg-indigo-50">
                                    Mes
                                </th>
                                <th class="px-5 py-3 text-center text-xs font-semibold text-[#475569] uppercase tracking-wider bg-white">
                                    Año
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#9fb0c3]">
                            @foreach($guardia->bomberos as $user)
                            <tr class="hover:bg-white transition-colors group">
                                <td class="px-6 py-3 whitespace-nowrap sticky left-0 bg-white group-hover:bg-white transition-colors z-10 border-r border-slate-200">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-white flex items-center justify-center text-xs font-bold text-[#1e293b]">
                                            {{ substr($user->nombres, 0, 1) }}{{ substr($user->apellido_paterno, 0, 1) }}
                                        </div>
                                        <span class="text-sm font-medium text-[#1e293b]">{{ $user->nombres }} {{ $user->apellido_paterno }}</span>
                                    </div>
                                </td>
                                @foreach($weeksInMonth as $weekNum)
                                    @php $stats = $user->weekly_stats->get($weekNum); @endphp
                                <td class="px-4 py-3 text-center">
                                    @if($stats && ($stats['shifts'] ?? 0) > 0)
                                    <span class="inline-flex items-center px-2 py-1 rounded-lg text-xs font-semibold bg-emerald-100 text-emerald-700">
                                        {{ $stats['shifts'] }}
                                    </span>
                                    @else
                                    <span class="text-[#9fb0c3]">—</span>
                                    @endif
                                </td>
                                @endforeach
                                <td class="px-5 py-3 text-center bg-indigo-50/50">
                                    <span class="text-sm font-bold text-indigo-700">{{ $user->month_shifts ?? 0 }}</span>
                                </td>
                                <td class="px-5 py-3 text-center bg-white">
                                    <span class="text-sm font-semibold text-[#475569]">{{ $user->year_shifts ?? 0 }}</span>
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
        <div class="bg-white rounded-2xl border border-slate-200 p-12 text-center">
            <div class="w-16 h-16 mx-auto rounded-2xl bg-white flex items-center justify-center mb-4">
                <i class="fas fa-folder-open text-2xl text-[#475569]"></i>
            </div>
            <h3 class="text-lg font-bold text-[#1e293b] mb-2">Sin datos</h3>
            <p class="text-sm text-[#475569]">No se encontraron registros para el período seleccionado.</p>
        </div>
        @endif
    </div>
</div>
@endsection
