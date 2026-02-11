@extends('layouts.app')

@section('content')
    <style>
        @media print {
            nav, header, .no-print { display: none !important; }
            main { padding: 0 !important; }
        }
    </style>

    <div class="no-print flex items-center justify-end mb-4">
        <button onclick="window.print()" class="bg-slate-800 hover:bg-slate-700 text-white font-bold py-2 px-4 rounded-md text-sm transition shadow-sm flex items-center">
            <i class="fas fa-print mr-2"></i> Imprimir
        </button>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-black text-slate-800 uppercase tracking-tight">Reporte de Reemplazos</h1>
                <div class="text-sm text-slate-500 font-medium mt-1">{{ $kpis['range_label'] ?? '' }}</div>
                @if(isset($activeGuardia) && $activeGuardia)
                    <div class="mt-2">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Guardia en turno esta semana</span>
                        <div>
                            <span class="inline-flex items-center gap-2 text-xs font-black bg-red-50 text-red-800 border border-red-100 px-2 py-1 rounded-full uppercase tracking-wide">
                                <i class="fas fa-calendar-week"></i> {{ $activeGuardia->name }}
                            </span>
                        </div>
                    </div>
                @endif
            </div>
            <div class="text-right">
                <div class="text-xs font-bold text-slate-400 uppercase tracking-widest">Generado</div>
                <div class="text-sm font-bold text-slate-700">{{ now()->format('d-m-Y H:i') }}</div>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mt-6">
            <div class="border border-slate-200 rounded-lg p-4">
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Total reemplazos</div>
                <div class="text-2xl font-black text-slate-800 mt-1">{{ $kpis['total_replacements'] ?? 0 }}</div>
            </div>
            <div class="border border-slate-200 rounded-lg p-4">
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Reemplazantes únicos</div>
                <div class="text-2xl font-black text-slate-800 mt-1">{{ $kpis['unique_replacers'] ?? 0 }}</div>
            </div>
            <div class="border border-slate-200 rounded-lg p-4">
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Reemplazados únicos</div>
                <div class="text-2xl font-black text-slate-800 mt-1">{{ $kpis['unique_replaced'] ?? 0 }}</div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="bg-slate-50 px-6 py-4 border-b border-slate-200">
                <div class="text-sm font-black text-slate-800 uppercase tracking-wide">Top reemplazantes</div>
            </div>
            <div class="p-4 space-y-2">
                @forelse($topReplacers as $i => $r)
                    <div class="flex items-center justify-between bg-slate-50 border border-slate-200 rounded-lg px-3 py-2">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-slate-900 text-white flex items-center justify-center font-black text-xs">{{ $i + 1 }}</div>
                            <div class="text-sm font-bold text-slate-700">{{ $r['name'] ?? '' }}</div>
                        </div>
                        <div class="text-xs font-black text-emerald-700 bg-emerald-50 border border-emerald-100 rounded-full px-2 py-1">{{ $r['total'] ?? 0 }}</div>
                    </div>
                @empty
                    <div class="text-sm text-slate-400">Sin datos</div>
                @endforelse
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="bg-slate-50 px-6 py-4 border-b border-slate-200">
                <div class="text-sm font-black text-slate-800 uppercase tracking-wide">Reemplazos por guardia</div>
            </div>
            <div class="p-4 space-y-2">
                @forelse($replacementsByGuardia as $row)
                    <div class="flex items-center justify-between bg-slate-50 border border-slate-200 rounded-lg px-3 py-2">
                        <div class="text-sm font-bold text-slate-700">{{ $row->guardia_name }}</div>
                        <div class="text-xs font-black text-blue-700 bg-blue-50 border border-blue-100 rounded-full px-2 py-1">{{ (int) $row->total }}</div>
                    </div>
                @empty
                    <div class="text-sm text-slate-400">Sin datos</div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden mb-6">
        <div class="bg-slate-50 px-6 py-4 border-b border-slate-200">
            <div class="text-sm font-black text-slate-800 uppercase tracking-wide">Top reemplazantes por guardia</div>
        </div>
        <div class="p-6 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
            @forelse($topReplacersByGuardia as $guardiaName => $rows)
                <div class="border border-slate-200 rounded-xl overflow-hidden">
                    <div class="bg-slate-900 text-white px-4 py-3 flex items-center justify-between">
                        <div class="font-black uppercase tracking-wide text-sm">{{ $guardiaName }}</div>
                        <div class="text-[10px] text-slate-300 font-bold uppercase tracking-widest">Top {{ $rows->count() }}</div>
                    </div>
                    <div class="p-3 space-y-2">
                        @foreach($rows as $i => $r)
                            <div class="flex items-center justify-between bg-slate-50 border border-slate-200 rounded-lg px-3 py-2">
                                <div class="flex items-center gap-3">
                                    <div class="w-7 h-7 rounded-lg bg-slate-900 text-white flex items-center justify-center font-black text-[11px]">{{ $i + 1 }}</div>
                                    <div class="text-sm font-bold text-slate-700">{{ $r['name'] ?? '' }}</div>
                                </div>
                                <div class="text-xs font-black text-emerald-700 bg-emerald-50 border border-emerald-100 rounded-full px-2 py-1">{{ $r['total'] ?? 0 }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="text-sm text-slate-400">Sin datos</div>
            @endforelse
        </div>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="bg-slate-50 px-6 py-4 border-b border-slate-200 flex items-center justify-between">
            <div class="text-sm font-black text-slate-800 uppercase tracking-wide">Detalle (fechas exactas)</div>
            <div class="text-xs font-bold text-slate-400 uppercase tracking-widest">{{ $events->count() }} filas</div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Inicio</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Fin</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Guardia</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Reemplazado</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Reemplazante</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-slate-100">
                    @forelse($events as $e)
                        <tr>
                            <td class="px-6 py-2 whitespace-nowrap text-sm font-bold text-slate-700">{{ optional($e->start_date)->format('d-m-Y H:i') }}</td>
                            <td class="px-6 py-2 whitespace-nowrap text-sm font-bold text-slate-700">{{ optional($e->end_date)->format('d-m-Y H:i') ?? '—' }}</td>
                            <td class="px-6 py-2 whitespace-nowrap text-sm text-slate-600">{{ $e->firefighter?->guardia?->name ?? $e->user?->guardia?->name ?? 'Sin Asignar' }}</td>
                            <td class="px-6 py-2 whitespace-nowrap text-sm text-slate-700">
                                {{ $e->firefighter
                                    ? trim(($e->firefighter?->nombres ?? '') . ' ' . ($e->firefighter?->apellido_paterno ?? ''))
                                    : ($e->user?->name ?? '')
                                }}
                            </td>
                            <td class="px-6 py-2 whitespace-nowrap text-sm text-slate-700">
                                {{ $e->replacementFirefighter
                                    ? trim(($e->replacementFirefighter?->nombres ?? '') . ' ' . ($e->replacementFirefighter?->apellido_paterno ?? ''))
                                    : ($e->replacementUser?->name ?? '')
                                }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-slate-400">Sin reemplazos</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
