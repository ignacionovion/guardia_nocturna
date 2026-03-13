@extends('layouts.modern')

@section('content')
<div class="w-full py-4">
    <div class="max-w-6xl mx-auto">
        <x-ui.page-header title="Historial de Movimientos" subtitle="{{ $bodega->nombre }}" icon="fas fa-list" iconVariant="cyan">
            <div class="flex items-center gap-2">
                <x-ui.button variant="secondary" size="md" icon="fas fa-arrow-left" href="{{ route('inventario.config.form') }}">Volver</x-ui.button>
                <x-ui.button variant="primary" size="md" icon="fas fa-arrow-right" href="{{ route('inventario.retiro.access') }}">Ir a retiro</x-ui.button>
            </div>
        </x-ui.page-header>

        <div class="mt-6 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
            <div class="p-6 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
                <div class="text-sm font-bold text-slate-900 dark:text-white">Movimientos</div>
                <div class="text-xs text-slate-500 dark:text-slate-400 mt-1">Ingresos y egresos registrados en la bodega.</div>
            </div>

            <div class="p-6">
                @if($movimientos->count() > 0)
                    <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-700">
                        <table class="min-w-full text-sm">
                            <thead class="bg-slate-50 dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700">
                                <tr class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
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
                                        <td class="px-4 py-3 text-slate-700 dark:text-slate-300 whitespace-nowrap">{{ optional($m->created_at)->format('d-m-Y H:i') }}</td>
                                        <td class="px-4 py-3">
                                            @if(($m->tipo ?? '') === 'ingreso')
                                                <span class="inline-flex items-center rounded-full bg-sky-600/10 text-sky-700 border border-sky-600/20 px-2 py-1 text-[10px] font-bold uppercase tracking-wider">Ingreso</span>
                                            @else
                                                <span class="inline-flex items-center rounded-full bg-rose-600/10 text-rose-700 border border-rose-600/20 px-2 py-1 text-[10px] font-bold uppercase tracking-wider">Egreso</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-slate-900 font-semibold">{{ $m->item?->display_name ?? '—' }}</td>
                                        <td class="px-4 py-3 text-right text-slate-900 dark:text-white font-bold">{{ $m->cantidad }}</td>
                                        <td class="px-4 py-3 text-slate-700 dark:text-slate-300">
                                            @if($m->firefighter)
                                                <div class="font-semibold">{{ $m->firefighter->rut }}</div>
                                                <div class="text-xs text-slate-500 dark:text-slate-400">{{ trim((string)($m->firefighter->nombres ?? '') . ' ' . (string)($m->firefighter->apellido_paterno ?? '')) }}</div>
                                            @elseif($m->creator)
                                                <div class="font-semibold">{{ $m->creator->name ?? $m->creator->email ?? 'Usuario' }}</div>
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-slate-700 dark:text-slate-300">{{ $m->nota ?? '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $movimientos->links() }}
                    </div>
                @else
                    <div class="rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 px-4 py-10 text-center text-slate-500 dark:text-slate-400">
                        Aún no hay movimientos registrados.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
