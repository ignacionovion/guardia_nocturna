@extends('layouts.modern')

@section('content')
<div class="w-full">
    <x-ui.page-header title="Guardias Preventivas" subtitle="Registro de eventos preventivos" icon="fas fa-shield-halved" iconVariant="amber">
        <x-ui.button variant="primary" size="md" icon="fas fa-plus" href="{{ route('admin.preventivas.create') }}">
            Crear Preventiva
        </x-ui.button>
    </x-ui.page-header>

    <x-ui.card>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <th class="text-left px-6 py-3 text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Evento</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Rango</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Estado</th>
                        <th class="text-right px-6 py-3 text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($events as $event)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                            <td class="px-6 py-4 font-semibold text-slate-900 dark:text-white">
                                {{ $event->title }}
                            </td>
                            <td class="px-6 py-4 text-slate-700 dark:text-slate-300">
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
                                        : ($status === 'closed' ? 'bg-red-50 text-red-800 border border-red-200' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700');
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold {{ $cls }}">
                                    {{ $label }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <x-ui.button variant="secondary" size="sm" icon="fas fa-arrow-right" href="{{ route('admin.preventivas.show', $event) }}">
                                    Abrir
                                </x-ui.button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-10 text-center text-slate-500 dark:text-slate-400">
                                No hay preventivas creadas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/30">
            {{ $events->links() }}
        </div>
    </x-ui.card>
</div>
@endsection
