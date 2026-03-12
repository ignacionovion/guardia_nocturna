@extends('layouts.modern')

@section('title', 'Asistencia - ' . branding()->nombre_empresa)

@section('content')
<div class="space-y-6">
    @include('admin.reports._header')
    @include('admin.reports._tabs')

    @php $activeTab = request('tab', 'asistencia'); @endphp

    @if($activeTab === 'asistencia')

    {{-- Filtros --}}
    <x-ui.card>
        <form action="{{ route('admin.reports.attendance') }}" method="GET" class="flex flex-wrap items-end gap-4">
            <input type="hidden" name="tab" value="asistencia">
            
            {{-- Select Guardia --}}
            <div class="min-w-[220px]">
                <label class="form-label">Guardia</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-shield-alt text-slate-400"></i>
                    </div>
                    <select name="guardia_id" class="form-select pl-10">
                        <option value="">Todas las Guardias</option>
                        @foreach($guardias as $g)
                            <option value="{{ $g->id }}" {{ $guardiaId == $g->id ? 'selected' : '' }}>{{ $g->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Select Semana --}}
            <div class="min-w-[220px]">
                <label class="form-label">Semana</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-calendar-week text-slate-400"></i>
                    </div>
                    <select name="week" class="form-select pl-10">
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
                </div>
            </div>

            {{-- Fecha Desde --}}
            <div class="min-w-[160px]">
                <label class="form-label">Desde</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-calendar text-slate-400"></i>
                    </div>
                    <input type="date" name="from" value="{{ $from->format('Y-m-d') }}" class="form-input pl-10">
                </div>
            </div>

            {{-- Fecha Hasta --}}
            <div class="min-w-[160px]">
                <label class="form-label">Hasta</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-calendar text-slate-400"></i>
                    </div>
                    <input type="date" name="to" value="{{ $to->format('Y-m-d') }}" class="form-input pl-10">
                </div>
            </div>

            {{-- Botones --}}
            <div class="flex gap-2">
                <x-ui.button type="submit" variant="primary" size="md" icon="fas fa-filter">
                    Filtrar
                </x-ui.button>
                <x-ui.button variant="secondary" size="md" href="{{ route('admin.reports.attendance') }}" icon="fas fa-undo" title="Limpiar filtros">
                </x-ui.button>
            </div>

            {{-- Botones de Exportación --}}
            <div class="ml-auto flex gap-2">
                <x-ui.button 
                    variant="success" 
                    size="md" 
                    icon="fas fa-file-excel"
                    href="{{ route('admin.reports.attendance.export', ['format' => 'excel'] + request()->all()) }}">
                    Excel
                </x-ui.button>
                <x-ui.button 
                    variant="danger" 
                    size="md" 
                    icon="fas fa-file-pdf"
                    href="{{ route('admin.reports.attendance.export', ['format' => 'pdf'] + request()->all()) }}"
                    target="_blank">
                    PDF
                </x-ui.button>
            </div>
        </form>
    </x-ui.card>

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
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
        <x-ui.stat-card title="Cumplidos" :value="$stats['fulfilled'] ?? 0" icon="fas fa-check-circle" color="emerald" />
        <x-ui.stat-card title="Ausencias" :value="$stats['absences'] ?? 0" icon="fas fa-times-circle" color="rose" />
        <x-ui.stat-card title="Permisos" :value="$stats['permissions'] ?? 0" icon="fas fa-calendar-check" color="amber" />
        <x-ui.stat-card title="Licencias" :value="$stats['licenses'] ?? 0" icon="fas fa-file-medical" color="blue" />
        <x-ui.stat-card title="Inhabilitados" :value="$stats['disabled'] ?? 0" icon="fas fa-ban" color="slate" />
        <x-ui.stat-card title="Reemplazos" :value="$stats['replacements'] ?? 0" icon="fas fa-exchange-alt" color="purple" />
    </div>

    {{-- INFORME GENERAL --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <x-ui.card class="lg:col-span-2">
            <x-slot:header>
                <div class="flex items-center justify-between w-full">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-chart-bar text-red-500"></i>
                        <span class="text-title-sm">{{ $currentView === 'general' ? 'Consolidado General' : ($activeGuardia ? $activeGuardia->name : 'Sin Guardia') }}</span>
                    </div>
                    <span class="text-caption">{{ $from->format('d/m/Y') }} — {{ $to->format('d/m/Y') }}</span>
                </div>
            </x-slot:header>
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
        </x-ui.card>

        <x-ui.card>
            <x-slot:header>
                <div class="text-label">Resumen</div>
            </x-slot:header>
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
        </x-ui.card>
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

    {{-- TAB: PERMISOS --}}
    @elseif($activeTab === 'permisos')

    <x-ui.card class="mb-6">
        <form action="{{ route('admin.reports.attendance') }}" method="GET" class="flex flex-wrap items-end gap-3">
            <input type="hidden" name="tab" value="permisos">
            <div>
                <label class="form-label">Guardia</label>
                <select name="guardia_id" class="form-select w-44">
                    <option value="">Todas</option>
                    @foreach($guardias as $g)
                        <option value="{{ $g->id }}" {{ $guardiaId == $g->id ? 'selected' : '' }}>{{ $g->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Desde</label>
                <input type="date" name="from" value="{{ $from->format('Y-m-d') }}" class="form-input w-36">
            </div>
            <div>
                <label class="form-label">Hasta</label>
                <input type="date" name="to" value="{{ $to->format('Y-m-d') }}" class="form-input w-36">
            </div>
            <x-ui.button type="submit" variant="warning" size="md" icon="fas fa-filter">
                Filtrar
            </x-ui.button>
        </form>
    </x-ui.card>

    <x-ui.card class="mb-6">
        <x-slot:header>
            <div class="flex items-center gap-2">
                <i class="fas fa-calendar-alt text-amber-500"></i>
                <span class="text-title-sm">Permisos y Licencias</span>
            </div>
        </x-slot:header>
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
    </x-ui.card>

    @endif
</div>
@endsection
