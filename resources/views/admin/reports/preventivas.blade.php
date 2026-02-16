@extends('layouts.app')

@section('content')
    <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4 border-b border-slate-200 pb-6">
        <div>
            <h1 class="text-3xl font-extrabold text-slate-800 tracking-tight flex items-center uppercase">
                <i class="fas fa-clipboard-list mr-3 text-red-700"></i> Reporte de Preventivas
            </h1>
            <p class="text-slate-500 mt-1 font-medium">Asistencia por turnos y bomberos en guardias preventivas</p>
        </div>

        <div class="bg-white p-1.5 rounded-lg shadow-sm border border-slate-200 w-full md:w-auto">
            <form action="{{ route('admin.reports.preventivas') }}" method="GET" class="flex flex-col md:flex-row md:items-center gap-2">
                <div class="flex items-center gap-2">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-calendar-days text-slate-400"></i>
                        </div>
                        <input type="date" name="from" value="{{ $from->toDateString() }}" class="pl-10 pr-4 py-2 bg-slate-50 border-transparent focus:border-blue-500 focus:bg-white focus:ring-0 rounded-md text-sm font-medium text-slate-700 cursor-pointer hover:bg-slate-100 transition-colors" />
                    </div>

                    <div class="relative">
                        <input type="date" name="to" value="{{ $to->toDateString() }}" class="pl-4 pr-4 py-2 bg-slate-50 border-transparent focus:border-blue-500 focus:bg-white focus:ring-0 rounded-md text-sm font-medium text-slate-700 cursor-pointer hover:bg-slate-100 transition-colors" />
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <select name="event_id" class="pl-4 pr-8 py-2 bg-slate-50 border-transparent focus:border-blue-500 focus:bg-white focus:ring-0 rounded-md text-sm font-medium text-slate-700 cursor-pointer hover:bg-slate-100 transition-colors">
                        <option value="">Todas las preventivas</option>
                        @foreach($events as $ev)
                            <option value="{{ $ev->id }}" {{ (string)($eventId ?? '') === (string)$ev->id ? 'selected' : '' }}>
                                {{ $ev->title }}
                            </option>
                        @endforeach
                    </select>

                    <button type="submit" class="bg-slate-800 hover:bg-slate-700 text-white font-bold py-2 px-4 rounded-md text-sm transition shadow-sm flex items-center justify-center">
                        <i class="fas fa-filter mr-2"></i> Filtrar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="flex flex-col md:flex-row gap-2 mb-6">
        <a href="{{ route('admin.reports.index') }}" class="px-4 py-2 rounded-lg text-sm font-bold border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 transition">
            <i class="fas fa-chart-line mr-2 text-slate-400"></i> Asistencia
        </a>
        <a href="{{ route('admin.reports.preventivas') }}" class="px-4 py-2 rounded-lg text-sm font-bold border border-red-200 bg-red-50 text-red-800 hover:bg-red-100 transition">
            <i class="fas fa-clipboard-list mr-2 text-red-500"></i> Preventivas
        </a>
        <a href="{{ route('admin.reports.replacements') }}" class="px-4 py-2 rounded-lg text-sm font-bold border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 transition">
            <i class="fas fa-right-left mr-2 text-slate-400"></i> Reemplazos
        </a>
        <a href="{{ route('admin.reports.drivers') }}" class="px-4 py-2 rounded-lg text-sm font-bold border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 transition">
            <i class="fas fa-id-card mr-2 text-slate-400"></i> Conductores
        </a>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4">
            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Asignaciones</div>
            <div class="text-2xl font-black text-slate-800 mt-1">{{ $kpis['total_assignments'] ?? 0 }}</div>
            <div class="text-[10px] text-slate-400 mt-2 font-medium">{{ $kpis['range_label'] ?? '' }}</div>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4">
            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Asistieron</div>
            <div class="text-2xl font-black text-emerald-700 mt-1">{{ $kpis['present'] ?? 0 }}</div>
            <div class="text-[10px] text-slate-400 mt-2 font-medium">Confirmadas (QR/Admin)</div>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4">
            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Pendientes</div>
            <div class="text-2xl font-black text-slate-700 mt-1">{{ $kpis['pending'] ?? 0 }}</div>
            <div class="text-[10px] text-slate-400 mt-2 font-medium">Sin confirmación</div>
        </div>
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
