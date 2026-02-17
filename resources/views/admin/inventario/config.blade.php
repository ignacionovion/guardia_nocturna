@extends('layouts.app')

@section('content')
<div class="w-full py-4">
    @if(session('success'))
        <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-900 text-sm font-semibold">
            {{ session('success') }}
        </div>
    @endif

    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="text-xs font-black uppercase tracking-widest text-slate-500">Inventario</div>
            <div class="text-2xl font-extrabold text-slate-900">Configuración inicial</div>
            <div class="text-sm text-slate-600 mt-1">La <span class="font-bold">bodega</span> es el lugar físico (ej: “Bodega Sala de Máquinas”). Las <span class="font-bold">categorías</span> (Trauma, Ferulas, etc.) vienen desde el Excel.</div>
        </div>

        @if($bodega)
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                <a href="{{ route('inventario.qr.admin') }}" class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-800 font-extrabold text-[11px] uppercase tracking-widest">
                    <i class="fas fa-qrcode"></i>
                    Ver QR fijo
                </a>
                <a href="{{ route('inventario.import.form') }}" class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-800 font-extrabold text-[11px] uppercase tracking-widest">
                    <i class="fas fa-file-import"></i>
                    Importar
                </a>
                <a href="{{ route('inventario.retiro.access') }}" class="inline-flex items-center justify-center gap-2 bg-slate-950 hover:bg-black text-white font-black py-3 px-5 rounded-xl text-[11px] transition-all shadow-md hover:shadow-lg uppercase tracking-widest border border-slate-800">
                    <i class="fas fa-arrow-right"></i>
                    Ir a retiro
                </a>
            </div>
        @endif
    </div>

    <div class="mt-6 bg-white rounded-2xl border border-teal-900/20 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-teal-900/20 bg-sky-100">
            <div class="text-xs font-black uppercase tracking-widest text-slate-600">Stock actual</div>
            <div class="text-sm text-slate-600 mt-1">Listado de ítems activos.</div>
        </div>

        <div class="p-4">
            <div class="mb-4">
                <input id="invStockSearch" type="text" class="w-full px-4 py-3 border border-slate-200 rounded-xl bg-white text-slate-800 font-semibold text-sm" placeholder="Buscar por nombre, categoría o unidad..." autocomplete="off" />
            </div>
            <div class="overflow-y-auto" style="max-height: 420px;">
                <div id="invStockGrid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3">
                    @forelse($items as $item)
                        <div class="rounded-xl border border-slate-200 bg-white p-4 hover:bg-sky-50" data-search="{{ mb_strtolower(($item->display_name ?? '') . ' ' . ($item->categoria ?? '') . ' ' . ($item->unidad ?? '')) }}">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="text-sm font-extrabold text-slate-900 truncate">{{ $item->display_name }}</div>
                                    <div class="mt-1 text-[11px] text-slate-600 truncate">{{ $item->categoria ?? '—' }}</div>
                                    <div class="mt-1 text-[11px] text-slate-500 truncate">{{ $item->unidad ?? '—' }}</div>
                                </div>
                                <div class="shrink-0">
                                    <div class="text-xs font-black uppercase tracking-widest text-slate-500 text-right">Stock</div>
                                    <div class="text-2xl font-extrabold text-slate-900 text-right leading-none mt-1">{{ $item->stock }}</div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-10 text-center text-slate-500">
                            No hay ítems activos.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-6">
        <div class="bg-white rounded-2xl border border-teal-900/20 shadow-sm overflow-hidden">
            <div class="p-6 border-b border-teal-900/20 bg-sky-100">
                <div class="text-xs font-black uppercase tracking-widest text-slate-600">Bodega</div>
                <div class="text-sm text-slate-600 mt-1">Nombre del lugar físico donde está guardado el material.</div>
            </div>
            <div class="p-6">
                <form method="POST" action="{{ route('inventario.config.bodega.store') }}" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-xs font-black uppercase tracking-widest text-slate-600 mb-2">Nombre</label>
                        <input type="text" name="nombre" value="{{ old('nombre', $bodega->nombre ?? '') }}" class="w-full px-3 py-3 border border-slate-200 rounded-xl bg-white text-slate-800 font-semibold text-sm" placeholder="Ej: Bodega Sala de Máquinas" required />
                    </div>

                    <div>
                        <label class="block text-xs font-black uppercase tracking-widest text-slate-600 mb-2">Ubicación (opcional)</label>
                        <input type="text" name="ubicacion" value="{{ old('ubicacion', $bodega->ubicacion ?? '') }}" class="w-full px-3 py-3 border border-slate-200 rounded-xl bg-white text-slate-800 font-semibold text-sm" placeholder="Ej: Estante 3 / Caja plástica" />
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="w-full sm:w-auto inline-flex items-center gap-2 bg-slate-950 hover:bg-black text-white font-black py-3 px-5 rounded-xl text-[11px] transition-all shadow-md hover:shadow-lg uppercase tracking-widest border border-slate-800">
                            <i class="fas fa-save"></i>
                            Guardar bodega
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-teal-900/20 shadow-sm overflow-hidden">
            <div class="p-6 border-b border-teal-900/20 bg-sky-100">
                <div class="text-xs font-black uppercase tracking-widest text-slate-600">Ítems</div>
                <div class="text-sm text-slate-600 mt-1">Agrega ítems con stock inicial.</div>
            </div>
            <div class="p-6">
                @if(!$bodega)
                    <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-amber-900 text-sm font-semibold">
                        Primero guarda la bodega para poder agregar ítems.
                    </div>
                @else
                    <div class="bg-slate-50 rounded-2xl border border-slate-200 p-5">
                        <div class="text-xs font-black uppercase tracking-widest text-slate-600">Ingreso de stock</div>
                        <div class="text-sm text-slate-600 mt-1">Suma unidades a un ítem existente (queda registro como movimiento de ingreso).</div>

                        <form method="POST" action="{{ route('inventario.config.stock.ingreso.store') }}" class="mt-4 space-y-4">
                            @csrf

                            <div>
                                <label class="block text-xs font-black uppercase tracking-widest text-slate-600 mb-2">Ítem</label>
                                <select name="item_id" class="w-full px-3 py-3 border border-slate-200 rounded-xl bg-white text-slate-800 font-semibold text-sm" required>
                                    <option value="">Seleccionar...</option>
                                    @foreach($items as $it)
                                        <option value="{{ $it->id }}">{{ $it->display_name }} (Stock: {{ $it->stock }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-black uppercase tracking-widest text-slate-600 mb-2">Cantidad a ingresar</label>
                                    <input type="number" name="cantidad" min="1" value="1" class="w-full px-3 py-3 border border-slate-200 rounded-xl bg-white text-slate-800 font-semibold text-sm" required />
                                </div>
                                <div>
                                    <label class="block text-xs font-black uppercase tracking-widest text-slate-600 mb-2">Nota (opcional)</label>
                                    <input type="text" name="nota" class="w-full px-3 py-3 border border-slate-200 rounded-xl bg-white text-slate-800 font-semibold text-sm" placeholder="Ej: reposición" />
                                </div>
                            </div>

                            <div class="pt-1">
                                <button type="submit" class="w-full sm:w-auto inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white font-black py-3 px-5 rounded-xl text-[11px] transition-all shadow-md hover:shadow-lg uppercase tracking-widest border border-emerald-700">
                                    <i class="fas fa-plus"></i>
                                    Ingresar stock
                                </button>
                            </div>
                        </form>
                    </div>

                    <form method="POST" action="{{ route('inventario.config.items.store') }}" class="space-y-4 mt-6">
                        @csrf

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-black uppercase tracking-widest text-slate-600 mb-2">Categoría (opcional)</label>
                                <input type="text" name="categoria" value="{{ old('categoria') }}" class="w-full px-3 py-3 border border-slate-200 rounded-xl bg-white text-slate-800 font-semibold text-sm" />
                            </div>
                            <div>
                                <label class="block text-xs font-black uppercase tracking-widest text-slate-600 mb-2">Unidad (opcional)</label>
                                <input type="text" name="unidad" value="{{ old('unidad') }}" class="w-full px-3 py-3 border border-slate-200 rounded-xl bg-white text-slate-800 font-semibold text-sm" placeholder="Ej: unidades, cajas" />
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-black uppercase tracking-widest text-slate-600 mb-2">Título</label>
                            <input type="text" name="titulo" value="{{ old('titulo') }}" class="w-full px-3 py-3 border border-slate-200 rounded-xl bg-white text-slate-800 font-semibold text-sm" required />
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-black uppercase tracking-widest text-slate-600 mb-2">Variante (opcional)</label>
                                <input type="text" name="variante" value="{{ old('variante') }}" class="w-full px-3 py-3 border border-slate-200 rounded-xl bg-white text-slate-800 font-semibold text-sm" placeholder="Ej: Adulto, Pediátrico" />
                            </div>
                            <div>
                                <label class="block text-xs font-black uppercase tracking-widest text-slate-600 mb-2">Stock inicial</label>
                                <input type="number" name="stock" min="0" value="{{ old('stock', 0) }}" class="w-full px-3 py-3 border border-slate-200 rounded-xl bg-white text-slate-800 font-semibold text-sm" required />
                            </div>
                        </div>

                        <div class="pt-2">
                            <button type="submit" class="w-full sm:w-auto inline-flex items-center gap-2 bg-slate-950 hover:bg-black text-white font-black py-3 px-5 rounded-xl text-[11px] transition-all shadow-md hover:shadow-lg uppercase tracking-widest border border-slate-800">
                                <i class="fas fa-plus"></i>
                                Agregar ítem
                            </button>
                        </div>
                    </form>

                    <div class="mt-6">
                        <div class="text-xs font-black uppercase tracking-widest text-slate-500">Ítems cargados</div>
                        <div class="mt-3 overflow-x-auto overflow-y-auto rounded-xl border border-slate-200" style="max-height: 420px;">
                            <table class="min-w-full text-sm">
                                <thead class="bg-slate-50 border-b border-slate-200">
                                    <tr class="text-xs font-black uppercase tracking-widest text-slate-700">
                                        <th class="text-left px-4 py-3">Ítem</th>
                                        <th class="text-left px-4 py-3">Categoría</th>
                                        <th class="text-right px-4 py-3">Stock</th>
                                        <th class="text-right px-4 py-3">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @forelse($items as $item)
                                        <tr>
                                            <td class="px-4 py-3 font-bold text-slate-900">{{ $item->display_name }}</td>
                                            <td class="px-4 py-3 text-slate-600">{{ $item->categoria ?? '—' }}</td>
                                            <td class="px-4 py-3 text-right font-extrabold text-slate-900">{{ $item->stock }}</td>
                                            <td class="px-4 py-3 text-right">
                                                <form method="POST" action="{{ route('inventario.config.items.destroy', ['itemId' => $item->id]) }}" onsubmit="return confirm('¿Eliminar este ítem?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-rose-200 bg-rose-50 hover:bg-rose-100 text-rose-800 font-extrabold text-[11px] uppercase tracking-widest">
                                                        <i class="fas fa-trash"></i>
                                                        Eliminar
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="px-4 py-6 text-center text-slate-500">Aún no hay ítems.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        const input = document.getElementById('invStockSearch');
        const grid = document.getElementById('invStockGrid');
        if (!input || !grid) return;

        function apply() {
            const q = (input.value || '').trim().toLowerCase();
            const cards = Array.from(grid.querySelectorAll('[data-search]'));
            cards.forEach((c) => {
                const hay = (c.getAttribute('data-search') || '');
                c.style.display = q === '' || hay.includes(q) ? '' : 'none';
            });
        }

        input.addEventListener('input', apply);
        apply();
    })();
</script>
@endpush
