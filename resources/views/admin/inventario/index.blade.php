@extends('layouts.modern')

@section('content')
<div class="w-full py-4">
    <x-ui.page-header title="Panel" subtitle="{{ $bodega->nombre }}" icon="fas fa-boxes-stacked" iconVariant="cyan">
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
            <x-ui.button variant="secondary" size="md" icon="fas fa-gear" href="{{ route('inventario.config.form') }}">
                Administrar
            </x-ui.button>
            <x-ui.button variant="secondary" size="md" icon="fas fa-qrcode" href="{{ route('inventario.qr.admin') }}">
                QR fijo
            </x-ui.button>
            <x-ui.button variant="primary" size="md" icon="fas fa-arrow-right" href="{{ route('inventario.retiro.access') }}">
                Ir a formulario
            </x-ui.button>
        </div>
    </x-ui.page-header>

    <x-ui.card class="!border-sky-200 dark:!border-sky-800">
        <x-slot:header class="!bg-sky-50 dark:!bg-sky-900/20 !border-sky-200 dark:!border-sky-800">
            <div class="text-label">Stock actual</div>
            <div class="text-body-sm">Bodega actual</div>
        </x-slot:header>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-sky-50/50 dark:bg-sky-900/10 border-b border-sky-200 dark:border-sky-800">
                    <tr class="text-label">
                        <th class="text-left px-6 py-3">Ítem</th>
                        <th class="text-left px-6 py-3">Categoría</th>
                        <th class="text-left px-6 py-3">Unidad</th>
                        <th class="text-right px-6 py-3">Stock</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($items as $item)
                        <tr class="hover:bg-sky-50 dark:hover:bg-sky-900/10 transition-colors">
                            <td class="px-6 py-4 font-semibold text-slate-900 dark:text-white">{{ $item->display_name }}</td>
                            <td class="px-6 py-4 text-body-sm">{{ $item->categoria ?? '—' }}</td>
                            <td class="px-6 py-4 text-body-sm">{{ $item->unidad ?? '—' }}</td>
                            <td class="px-6 py-4 text-right font-bold text-slate-900 dark:text-white">{{ $item->stock }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-10 text-center text-slate-500 dark:text-slate-400">
                                <x-ui.empty-state icon="fas fa-box-open" message="No hay ítems activos." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.card>
</div>
@endsection
