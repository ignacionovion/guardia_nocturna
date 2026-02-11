@extends('layouts.app')

@section('content')
    <div class="max-w-6xl mx-auto py-10">
        <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4 border-b border-slate-200 pb-6">
            <div>
                <h1 class="text-3xl font-extrabold text-slate-800 tracking-tight flex items-center uppercase">
                    <i class="fas fa-clock-rotate-left mr-3 text-red-700"></i> Historial - {{ $guardia->name }}
                </h1>
                <p class="text-slate-500 mt-1 font-medium">Registros privados archivados al cierre semanal.</p>
            </div>

            <a href="{{ route('admin.guardias') }}" class="bg-white hover:bg-slate-50 text-slate-700 font-bold py-2.5 px-4 rounded-lg shadow-sm border border-slate-200 flex items-center gap-2 uppercase text-xs tracking-widest">
                <i class="fas fa-arrow-left"></i>
                Volver
            </a>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-200 bg-slate-50">
                <div class="text-sm font-black text-slate-700 uppercase tracking-widest">Archivos</div>
                <div class="text-xs text-slate-500 mt-1">Selecciona una fecha para ver el detalle.</div>
            </div>

            <div class="divide-y divide-slate-100">
                @forelse($archives as $a)
                    <a href="{{ route('admin.guardias.history.show', [$guardia->id, $a->id]) }}" class="block px-6 py-4 hover:bg-slate-50 transition">
                        <div class="flex items-center justify-between gap-4">
                            <div class="min-w-0">
                                <div class="text-sm font-black text-slate-800 uppercase tracking-tight truncate">{{ $a->label ?: 'Cierre semanal' }}</div>
                                <div class="text-xs text-slate-500 mt-0.5 font-semibold">{{ $a->archived_at?->format('Y-m-d H:i') }}</div>
                            </div>
                            <div class="text-slate-400">
                                <i class="fas fa-chevron-right"></i>
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="px-6 py-10 text-center text-slate-500 font-semibold">
                        No hay historial archivado todavía.
                    </div>
                @endforelse
            </div>

            @if(method_exists($archives, 'links'))
                <div class="px-6 py-4 border-t border-slate-200 bg-white">
                    {{ $archives->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
