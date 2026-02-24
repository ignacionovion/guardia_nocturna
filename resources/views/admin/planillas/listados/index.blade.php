@extends('layouts.app')

@section('content')
<div class="w-full py-4">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="text-xs font-black uppercase tracking-widest text-slate-500">Planillas</div>
            <div class="text-2xl font-extrabold text-slate-900">Editar listados</div>
            <div class="text-sm text-slate-600 mt-1">Agrega/ajusta ítems de checklist sin modificar código.</div>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('admin.planillas.index') }}" class="px-4 py-2 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs">Volver</a>
        </div>
    </div>

    @if(session('success'))
        <div class="mt-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-6 py-4 text-emerald-900">
            <div class="text-sm font-extrabold">{{ session('success') }}</div>
        </div>
    @endif

    <div class="mt-6 bg-white rounded-2xl border border-teal-900/20 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-teal-900/20 bg-sky-100">
            <form method="GET" class="flex flex-col md:flex-row md:items-end gap-4">
                <div>
                    <div class="text-xs font-black uppercase tracking-widest text-slate-600 mb-2">Unidad</div>
                    <select name="unidad" class="w-full md:w-56 px-3 py-2 border border-slate-200 rounded-lg bg-white text-slate-800 font-semibold text-sm">
                        <option value="">Todas</option>
                        @foreach($unidades as $u)
                            <option value="{{ $u }}" {{ ($unidadSeleccionada ?? '') === $u ? 'selected' : '' }}>{{ $u }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <div class="text-xs font-black uppercase tracking-widest text-slate-600 mb-2">Sección</div>
                    <select name="section" class="w-full md:w-56 px-3 py-2 border border-slate-200 rounded-lg bg-white text-slate-800 font-semibold text-sm">
                        <option value="">Todas</option>
                        @foreach($sections as $key => $label)
                            <option value="{{ $key }}" {{ ($sectionSeleccionada ?? '') === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-center gap-3">
                    <button type="submit" class="px-5 py-2 rounded-lg bg-slate-900 hover:bg-black text-white font-black text-[11px] uppercase tracking-widest">Filtrar</button>
                    <a href="{{ route('admin.planillas.listados.index') }}" class="px-5 py-2 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-extrabold text-[11px] uppercase tracking-widest text-center">Limpiar</a>
                </div>
            </form>
        </div>

        <div class="p-6 border-b border-slate-200">
            <form method="POST" action="{{ route('admin.planillas.listados.store') }}" class="grid grid-cols-1 md:grid-cols-5 gap-3">
                @csrf
                <div>
                    <div class="text-xs font-black uppercase tracking-widest text-slate-500 mb-2">Unidad</div>
                    <select name="unidad" class="w-full px-3 py-2 border border-slate-200 rounded-lg bg-white text-slate-800 font-semibold text-sm" required>
                        @foreach($unidades as $u)
                            <option value="{{ $u }}" {{ ($unidadSeleccionada ?? '') === $u ? 'selected' : '' }}>{{ $u }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <div class="text-xs font-black uppercase tracking-widest text-slate-500 mb-2">Sección</div>
                    <select name="section" class="w-full px-3 py-2 border border-slate-200 rounded-lg bg-white text-slate-800 font-semibold text-sm" required>
                        @foreach($sections as $key => $label)
                            <option value="{{ $key }}" {{ ($sectionSeleccionada ?? '') === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <div class="text-xs font-black uppercase tracking-widest text-slate-500 mb-2">Key</div>
                    <input name="item_key" class="w-full px-3 py-2 border border-slate-200 rounded-lg bg-white text-slate-800 font-semibold text-sm" placeholder="ej: tiras_70" required>
                </div>
                <div>
                    <div class="text-xs font-black uppercase tracking-widest text-slate-500 mb-2">Nombre</div>
                    <input name="label" class="w-full px-3 py-2 border border-slate-200 rounded-lg bg-white text-slate-800 font-semibold text-sm" placeholder="ej: Tiras de 70" required>
                </div>
                <div>
                    <div class="text-xs font-black uppercase tracking-widest text-slate-500 mb-2">Orden</div>
                    <input type="number" name="sort_order" class="w-full px-3 py-2 border border-slate-200 rounded-lg bg-white text-slate-800 font-semibold text-sm" placeholder="0">
                </div>
                <div class="md:col-span-5 flex justify-end">
                    <button type="submit" class="inline-flex items-center justify-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white font-black py-3 px-6 rounded-xl text-[11px] transition-all shadow-md hover:shadow-lg uppercase tracking-widest">
                        <i class="fas fa-plus"></i>
                        Agregar
                    </button>
                </div>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-sky-50 border-b border-teal-900/20">
                    <tr class="text-xs font-black uppercase tracking-widest text-slate-700">
                        <th class="text-left px-6 py-3">Unidad</th>
                        <th class="text-left px-6 py-3">Sección</th>
                        <th class="text-left px-6 py-3">Key</th>
                        <th class="text-left px-6 py-3">Nombre</th>
                        <th class="text-center px-6 py-3">Orden</th>
                        <th class="text-center px-6 py-3">Activo</th>
                        <th class="text-right px-6 py-3">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($items as $item)
                        <tr class="hover:bg-sky-50">
                            <td class="px-6 py-4 font-bold text-slate-900">{{ $item->unidad }}</td>
                            <td class="px-6 py-4 text-slate-700 font-bold">{{ $sections[$item->section] ?? $item->section }}</td>
                            <td class="px-6 py-4 text-slate-700 font-mono text-xs">{{ $item->item_key }}</td>
                            <td class="px-6 py-4">
                                <form method="POST" action="{{ route('admin.planillas.listados.update', $item) }}" class="flex items-center gap-2 justify-end">
                                    @csrf
                                    @method('PUT')
                                    <input name="label" value="{{ $item->label }}" class="w-full min-w-[220px] px-3 py-2 border border-slate-200 rounded-lg bg-white text-slate-800 font-semibold text-sm">
                            </td>
                            <td class="px-6 py-4 text-center">
                                    <input type="number" name="sort_order" value="{{ $item->sort_order }}" class="w-24 px-3 py-2 border border-slate-200 rounded-lg bg-white text-slate-800 font-semibold text-sm">
                            </td>
                            <td class="px-6 py-4 text-center">
                                    <input type="hidden" name="is_active" value="0">
                                    <input type="checkbox" name="is_active" value="1" {{ $item->is_active ? 'checked' : '' }}>
                            </td>
                            <td class="px-6 py-4 text-right">
                                    <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-800 font-bold text-xs">
                                        <i class="fas fa-save"></i>
                                        Guardar
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.planillas.listados.destroy', $item) }}" class="inline" onsubmit="return confirm('¿Eliminar este ítem?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-rose-200 bg-rose-50 hover:bg-rose-100 text-rose-800 font-extrabold text-xs mt-2">
                                        <i class="fas fa-trash"></i>
                                        Eliminar
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-10 text-center text-slate-500">No hay ítems.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
