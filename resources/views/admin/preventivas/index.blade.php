@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex items-center justify-between">
        <div>
            <div class="text-xs font-black uppercase tracking-widest text-slate-500">Guardias Preventivas</div>
            <div class="text-2xl font-extrabold text-slate-900">Eventos</div>
        </div>

        <a href="{{ route('admin.preventivas.create') }}" class="inline-flex items-center gap-2 bg-slate-950 hover:bg-black text-white font-black py-3 px-5 rounded-xl text-[11px] transition-all shadow-md hover:shadow-lg uppercase tracking-widest border border-slate-800">
            <i class="fas fa-plus"></i>
            Crear Preventiva
        </a>
    </div>

    <div class="mt-6 bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="text-left px-6 py-3 text-xs font-black uppercase tracking-widest text-slate-600">Evento</th>
                        <th class="text-left px-6 py-3 text-xs font-black uppercase tracking-widest text-slate-600">Rango</th>
                        <th class="text-left px-6 py-3 text-xs font-black uppercase tracking-widest text-slate-600">Estado</th>
                        <th class="text-right px-6 py-3 text-xs font-black uppercase tracking-widest text-slate-600">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($events as $event)
                        <tr class="hover:bg-slate-50">
                            <td class="px-6 py-4 font-bold text-slate-900">
                                {{ $event->title }}
                            </td>
                            <td class="px-6 py-4 text-slate-700">
                                {{ $event->start_date?->format('d-m-Y') }} → {{ $event->end_date?->format('d-m-Y') }}
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $status = strtolower((string) ($event->status ?? 'draft'));
                                    if (!in_array($status, ['draft', 'active', 'closed'], true)) {
                                        $status = 'draft';
                                    }

                                    $label = $status === 'active' ? 'Activa' : ($status === 'closed' ? 'Cerrada' : 'Borrador');
                                    $cls = $status === 'active'
                                        ? 'bg-emerald-50 text-emerald-800 border border-emerald-200'
                                        : ($status === 'closed' ? 'bg-red-50 text-red-800 border border-red-200' : 'bg-slate-100 text-slate-700 border border-slate-200');
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[11px] font-black uppercase tracking-widest {{ $cls }}">
                                    {{ $label }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('admin.preventivas.show', $event) }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-800 font-bold text-xs">
                                    <i class="fas fa-arrow-right"></i>
                                    Abrir
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-10 text-center text-slate-500">
                                No hay preventivas creadas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 border-t border-slate-200 bg-slate-50">
            {{ $events->links() }}
        </div>
    </div>
</div>
@endsection
