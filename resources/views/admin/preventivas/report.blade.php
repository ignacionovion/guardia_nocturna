@extends('layouts.app')

@section('content')
<div class="w-full">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <div>
            <div class="text-xs font-black uppercase tracking-widest text-slate-500">Preventivas</div>
            <div class="text-3xl font-extrabold text-slate-900 tracking-tight flex items-center">
                <i class="fas fa-chart-bar mr-3 text-blue-600"></i>
                Reporte: {{ $event->title }}
            </div>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('admin.preventivas.report.excel', $event) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs uppercase tracking-wider rounded-lg transition-colors">
                <i class="fas fa-file-excel"></i>
                Excel
            </a>
            <a href="{{ route('admin.preventivas.report.pdf', $event) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white font-black text-xs uppercase tracking-wider rounded-lg transition-colors">
                <i class="fas fa-file-pdf"></i>
                PDF
            </a>
            <a href="{{ route('admin.preventivas.show', $event) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 font-black text-xs uppercase tracking-wider rounded-lg transition-colors">
                <i class="fas fa-arrow-left"></i>
                Volver
            </a>
        </div>
    </div>

    <!-- Resumen del Evento -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
            <div class="text-[10px] font-black uppercase tracking-widest text-slate-400">Período</div>
            <div class="text-lg font-bold text-slate-900">{{ $event->start_date->format('d/m/Y') }} - {{ $event->end_date->format('d/m/Y') }}</div>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
            <div class="text-[10px] font-black uppercase tracking-widest text-slate-400">Total Asignaciones</div>
            <div class="text-2xl font-black text-slate-900">{{ $totalAssignments }}</div>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
            <div class="text-[10px] font-black uppercase tracking-widest text-slate-400">Asistencias Confirmadas</div>
            <div class="text-2xl font-black text-emerald-600">{{ $totalAttendance }}</div>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
            <div class="text-[10px] font-black uppercase tracking-widest text-slate-400">Tasa de Asistencia</div>
            <div class="text-2xl font-black {{ $totalAssignments > 0 ? ($effectiveAttendance/$totalAssignments >= 0.8 ? 'text-emerald-600' : 'text-amber-600') : 'text-slate-400' }}">
                {{ $totalAssignments > 0 ? round(($effectiveAttendance / $totalAssignments) * 100, 1) : 0 }}%
            </div>
        </div>
    </div>

    <!-- Detalles por Turno -->
    <div class="space-y-4">
        @foreach($shifts as $shift)
            @php
                $shiftAssignments = $shift->assignments->count();
                $shiftAttendance = $shift->assignments->whereNotNull('attendance')->count();
                $shiftRefuerzos = $shift->assignments->where('es_refuerzo', true)->count();
            @endphp
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                    <div>
                        <div class="text-sm font-black text-slate-900 uppercase">
                            {{ $shift->shift_date->format('d/m/Y') }} - {{ $shift->label ?: 'Turno ' . ($shift->sort_order + 1) }}
                        </div>
                        <div class="text-xs text-slate-500 mt-0.5">
                            {{ substr($shift->start_time, 0, 5) }} a {{ substr($shift->end_time, 0, 5) }}
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-black bg-blue-100 text-blue-700 border border-blue-200">
                            {{ $shiftAttendance }}/{{ $shiftAssignments }} presentes
                        </span>
                        @if($shiftRefuerzos > 0)
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-black bg-sky-100 text-sky-700 border border-sky-200">
                                {{ $shiftRefuerzos }} refuerzo{{ $shiftRefuerzos > 1 ? 's' : '' }}
                            </span>
                        @endif
                    </div>
                </div>

                <div class="p-4">
                    @if($shift->assignments->count() === 0)
                        <div class="text-center text-slate-400 py-4">
                            <i class="fas fa-inbox text-2xl mb-2"></i>
                            <div class="text-sm">Sin asignaciones</div>
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b border-slate-200">
                                        <th class="text-left py-2 px-3 font-black text-xs uppercase tracking-wider text-slate-500">Bombero</th>
                                        <th class="text-left py-2 px-3 font-black text-xs uppercase tracking-wider text-slate-500">RUT</th>
                                        <th class="text-left py-2 px-3 font-black text-xs uppercase tracking-wider text-slate-500">Cargo</th>
                                        <th class="text-left py-2 px-3 font-black text-xs uppercase tracking-wider text-slate-500">Tipo</th>
                                        <th class="text-left py-2 px-3 font-black text-xs uppercase tracking-wider text-slate-500">Reemplaza a</th>
                                        <th class="text-left py-2 px-3 font-black text-xs uppercase tracking-wider text-slate-500">Hora Entrada</th>
                                        <th class="text-left py-2 px-3 font-black text-xs uppercase tracking-wider text-slate-500">Asistencia</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($shift->assignments->sortByDesc('attendance') as $assignment)
                                        @php
                                            $f = $assignment->firefighter;
                                            $hasAttendance = (bool) $assignment->attendance;
                                            $esReemplazo = (bool) $assignment->reemplaza_a_bombero_id;
                                            $reemplazaA = $assignment->replacedFirefighter;
                                            $fueReemplazado = !$assignment->es_refuerzo && !$esReemplazo && !$hasAttendance && \App\Models\PreventiveShiftAssignment::where('preventive_shift_id', $shift->id)->where('reemplaza_a_bombero_id', $assignment->bombero_id)->exists();
                                        @endphp
                                        <tr class="border-b border-slate-100 last:border-0 {{ $hasAttendance ? 'bg-emerald-50/30' : ($fueReemplazado ? 'bg-rose-50/30' : '') }}">
                                            <td class="py-3 px-3">
                                                <div class="font-bold {{ $fueReemplazado ? 'text-slate-400 line-through' : 'text-slate-900' }}">{{ $f?->apellido_paterno ?? 'N/A' }}</div>
                                                <div class="text-xs {{ $fueReemplazado ? 'text-slate-300' : 'text-slate-500' }}">{{ $f?->nombres ?? '' }}</div>
                                            </td>
                                            <td class="py-3 px-3 text-slate-600 font-mono text-xs {{ $fueReemplazado ? 'text-slate-400' : '' }}">{{ $f?->rut ?? 'N/A' }}</td>
                                            <td class="py-3 px-3 {{ $fueReemplazado ? 'text-slate-400' : 'text-slate-600' }}">{{ $f?->cargo_texto ?? '-' }}</td>
                                            <td class="py-3 px-3">
                                                @if($assignment->es_refuerzo)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-black bg-sky-100 text-sky-700 border border-sky-200 {{ $fueReemplazado ? 'opacity-50' : '' }}">
                                                        <i class="fas fa-user-plus mr-1 text-[10px]"></i>REFUERZO
                                                    </span>
                                                @elseif($esReemplazo)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-black bg-purple-100 text-purple-700 border border-purple-200 {{ $fueReemplazado ? 'opacity-50' : '' }}">
                                                        <i class="fas fa-exchange-alt mr-1 text-[10px]"></i>REEMPLAZO
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-black bg-slate-100 text-slate-600 border border-slate-200 {{ $fueReemplazado ? 'opacity-50' : '' }}">TITULAR</span>
                                                @endif
                                            </td>
                                            <td class="py-3 px-3 text-slate-600">
                                                @if($esReemplazo && $reemplazaA)
                                                    <span class="text-xs font-semibold text-purple-700 {{ $fueReemplazado ? 'opacity-50' : '' }}">{{ $reemplazaA->apellido_paterno }}</span>
                                                @else
                                                    <span class="text-slate-400">-</span>
                                                @endif
                                            </td>
                                            <td class="py-3 px-3 {{ $fueReemplazado ? 'text-slate-400' : 'text-slate-600' }}">
                                                @if($assignment->entrada_hora)
                                                    {{ $assignment->entrada_hora->timezone($event->timezone)->format('H:i:s') }}
                                                @elseif($assignment->attendance?->confirmed_at)
                                                    {{ $assignment->attendance->confirmed_at->timezone($event->timezone)->format('H:i:s') }}
                                                @else
                                                    <span class="text-slate-400">-</span>
                                                @endif
                                            </td>
                                            <td class="py-3 px-3">
                                                @if($hasAttendance)
                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-black bg-emerald-100 text-emerald-700 border border-emerald-200">
                                                        <i class="fas fa-check-circle"></i>
                                                        {{ $assignment->attendance->confirmed_at->timezone($event->timezone)->format('H:i') }}
                                                    </span>
                                                @elseif($fueReemplazado)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-black bg-rose-100 text-rose-700 border border-rose-200">
                                                        <i class="fas fa-exchange-alt mr-1 text-[10px]"></i>Reemplazado
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-black bg-slate-100 text-slate-500 border border-slate-200">Pendiente</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
