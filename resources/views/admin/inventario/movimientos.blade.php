@extends('layouts.app')

@section('content')
<div class="w-full py-4">
    <div class="max-w-6xl mx-auto">
        <div class="flex items-start justify-between gap-4">
            <div>
                <div class="text-xs font-black uppercase tracking-widest text-slate-500">Inventario</div>
                <div class="text-2xl font-extrabold text-slate-900">Historial de movimientos</div>
                <div class="text-sm text-slate-600 mt-1">{{ $bodega->nombre }}</div>
            </div>

            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                <a href="{{ route('inventario.config.form') }}" class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-800 font-extrabold text-[11px] uppercase tracking-widest">
                    <i class="fas fa-arrow-left"></i>
                    Volver
                </a>
                <a href="{{ route('inventario.retiro.access') }}" class="inline-flex items-center justify-center gap-2 bg-slate-950 hover:bg-black text-white font-black py-3 px-5 rounded-xl text-[11px] transition-all shadow-md hover:shadow-lg uppercase tracking-widest border border-slate-800">
                    <i class="fas fa-arrow-right"></i>
                    Ir a retiro
                </a>
            </div>
        </div>

        <div class="mt-6 bg-white rounded-2xl border border-teal-900/20 shadow-sm overflow-hidden">
            <div class="p-6 border-b border-teal-900/20 bg-sky-100">
                <div class="text-xs font-black uppercase tracking-widest text-slate-600">Movimientos</div>
                <div class="text-sm text-slate-600 mt-1">Ingresos y egresos registrados en la bodega.</div>
            </div>

            <div class="p-6">
                @if($movimientos->count() > 0)
                    <div class="overflow-x-auto rounded-xl border border-slate-200">
                        <table class="min-w-full text-sm">
                            <thead class="bg-slate-50 border-b border-slate-200">
                                <tr class="text-xs font-black uppercase tracking-widest text-slate-600">
                                    <th class="text-left px-4 py-3">Fecha</th>
                                    <th class="text-left px-4 py-3">Tipo</th>
                                    <th class="text-left px-4 py-3">Ítem</th>
                                    <th class="text-right px-4 py-3">Cantidad</th>
                                    <th class="text-left px-4 py-3">Registrado por</th>
                                    <th class="text-left px-4 py-3">Nota</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200">
                                @foreach($movimientos as $m)
                                    <tr class="hover:bg-sky-50">
                                        <td class="px-4 py-3 text-slate-700 whitespace-nowrap">{{ optional($m->created_at)->format('d-m-Y H:i') }}</td>
                                        <td class="px-4 py-3">
                                            @if(($m->tipo ?? '') === 'ingreso')
                                                <span class="inline-flex items-center rounded-full bg-sky-600/10 text-sky-700 border border-sky-600/20 px-2 py-1 text-[10px] font-black uppercase tracking-widest">Ingreso</span>
                                            @else
                                                <span class="inline-flex items-center rounded-full bg-rose-600/10 text-rose-700 border border-rose-600/20 px-2 py-1 text-[10px] font-black uppercase tracking-widest">Egreso</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-slate-900 font-semibold">{{ $m->item?->display_name ?? '—' }}</td>
                                        <td class="px-4 py-3 text-right text-slate-900 font-extrabold">{{ $m->cantidad }}</td>
                                        <td class="px-4 py-3 text-slate-700">
                                            @if($m->firefighter)
                                                <div class="font-semibold">{{ $m->firefighter->rut }}</div>
                                                <div class="text-xs text-slate-500">{{ trim((string)($m->firefighter->nombres ?? '') . ' ' . (string)($m->firefighter->apellido_paterno ?? '')) }}</div>
                                            @elseif($m->creator)
                                                <div class="font-semibold">{{ $m->creator->name ?? $m->creator->email ?? 'Usuario' }}</div>
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-slate-700">{{ $m->nota ?? '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $movimientos->links() }}
                    </div>
                @else
                    <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-10 text-center text-slate-500">
                        Aún no hay movimientos registrados.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
