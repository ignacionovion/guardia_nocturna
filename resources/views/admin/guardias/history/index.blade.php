@extends('layouts.modern')

@section('content')
    <div class="max-w-6xl mx-auto py-10">
        <x-ui.page-header title="Historial - {{ $guardia->name }}" subtitle="Registros privados archivados al cierre semanal" icon="fas fa-clock-rotate-left" iconVariant="red">
            <x-ui.button variant="secondary" size="md" icon="fas fa-arrow-left" href="{{ route('admin.guardias') }}">
                Volver
            </x-ui.button>
        </x-ui.page-header>

        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800">
                <div class="text-sm font-bold text-slate-900 dark:text-white">Archivos</div>
                <div class="text-xs text-slate-500 dark:text-slate-400 mt-1">Selecciona una fecha para ver el detalle.</div>
            </div>

            <div class="divide-y divide-slate-100">
                @forelse($archives as $a)
                    <a href="{{ route('admin.guardias.history.show', [$guardia->id, $a->id]) }}" class="block px-6 py-4 hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                        <div class="flex items-center justify-between gap-4">
                            <div class="min-w-0">
                                <div class="text-sm font-bold text-slate-900 dark:text-white truncate">{{ $a->label ?: 'Cierre semanal' }}</div>
                                <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 font-semibold">{{ $a->archived_at?->format('Y-m-d H:i') }}</div>
                            </div>
                            <div class="text-slate-400">
                                <i class="fas fa-chevron-right"></i>
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="px-6 py-10 text-center text-slate-500 dark:text-slate-400 font-semibold">
                        No hay historial archivado todavía.
                    </div>
                @endforelse
            </div>

            @if(method_exists($archives, 'links'))
                <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-white">
                    {{ $archives->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
