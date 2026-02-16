@extends('layouts.app')

@section('content')
<div class="w-full py-4">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="text-xs font-black uppercase tracking-widest text-slate-500">Inventario</div>
            <div class="text-2xl font-extrabold text-slate-900">Panel</div>
            <div class="text-sm text-slate-600 mt-1">{{ $bodega->nombre }}</div>
        </div>

        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
            <a href="{{ route('inventario.config.form') }}" class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-800 font-extrabold text-[11px] uppercase tracking-widest">
                <i class="fas fa-gear"></i>
                Administrar
            </a>
            <a href="{{ route('inventario.qr.admin') }}" class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-800 font-extrabold text-[11px] uppercase tracking-widest">
                <i class="fas fa-qrcode"></i>
                QR fijo
            </a>
            <a href="{{ route('inventario.retiro.access') }}" class="inline-flex items-center justify-center gap-2 bg-slate-950 hover:bg-black text-white font-black py-3 px-5 rounded-xl text-[11px] transition-all shadow-md hover:shadow-lg uppercase tracking-widest border border-slate-800">
                <i class="fas fa-arrow-right"></i>
                Ir a formulario
            </a>
        </div>
    </div>

    <div class="mt-6 bg-white rounded-2xl border border-teal-900/20 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-teal-900/20 bg-sky-100">
            <div class="text-xs font-black uppercase tracking-widest text-slate-600">Stock actual</div>
            <div class="text-sm text-slate-600 mt-1">Listado de ítems activos.</div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-sky-50 border-b border-teal-900/20">
                    <tr class="text-xs font-black uppercase tracking-widest text-slate-700">
                        <th class="text-left px-6 py-3">Ítem</th>
                        <th class="text-left px-6 py-3">Categoría</th>
                        <th class="text-left px-6 py-3">Unidad</th>
                        <th class="text-right px-6 py-3">Stock</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($items as $item)
                        <tr class="hover:bg-sky-50">
                            <td class="px-6 py-4 font-bold text-slate-900">{{ $item->display_name }}</td>
                            <td class="px-6 py-4 text-slate-700">{{ $item->categoria ?? '—' }}</td>
                            <td class="px-6 py-4 text-slate-700">{{ $item->unidad ?? '—' }}</td>
                            <td class="px-6 py-4 text-right font-extrabold text-slate-900">{{ $item->stock }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-10 text-center text-slate-500">No hay ítems activos.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
