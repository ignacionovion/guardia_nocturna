@extends('layouts.modern')

@section('content')
<div class="w-full py-4">
    @if(session('success'))
        <x-ui.alert type="success" icon="fas fa-check-circle" class="mb-6">
            {{ session('success') }}
        </x-ui.alert>
    @endif

    <x-ui.page-header title="Bodega Material Menor" subtitle="La bodega es el lugar físico (ej: \"Bodega Sala de Máquinas\"). Las categorías (Trauma, Ferulas, etc.)" icon="fas fa-boxes-stacked" iconVariant="cyan">

        @if($bodega)
            <div class="flex flex-wrap items-center justify-end gap-2">
                <x-ui.button variant="danger" size="sm" icon="fas fa-file-pdf" href="{{ route('inventario.snapshot.pdf') }}" target="_blank">PDF</x-ui.button>
                <x-ui.button variant="success" size="sm" icon="fas fa-envelope" onclick="sendBodegaSnapshotEmail()">Email</x-ui.button>
                <x-ui.button variant="secondary" size="sm" icon="fas fa-qrcode" href="{{ route('inventario.qr.admin') }}">QR</x-ui.button>
                <x-ui.button variant="secondary" size="sm" icon="fas fa-list" href="{{ route('inventario.movimientos.index') }}">Historial</x-ui.button>
                <x-ui.button variant="warning" size="sm" icon="fas fa-file-import" href="{{ route('inventario.import.form') }}">Importar</x-ui.button>
                <x-ui.button variant="primary" size="sm" icon="fas fa-arrow-right" href="{{ route('inventario.retiro.access') }}">Retiro</x-ui.button>
            </div>
        @endif
    </x-ui.page-header>

    <div class="mt-6 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
            <div class="text-sm font-bold text-slate-900 dark:text-white">Stock actual</div>
            <div class="text-xs text-slate-500 dark:text-slate-400 mt-1">Bodega actual.</div>
        </div>

        <div class="p-4">
            <div class="mb-4">
                <input id="invStockSearch" type="text" class="w-full px-4 py-3 border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-900 text-slate-800 dark:text-white font-semibold text-sm" placeholder="Buscar por nombre, categoría o unidad..." autocomplete="off" />
            </div>
            <div class="overflow-y-auto" style="max-height: 420px;">
                <div id="invStockGrid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3">
                    @forelse($items as $item)
                        <div class="rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-4 hover:bg-sky-50" data-search="{{ mb_strtolower(($item->display_name ?? '') . ' ' . ($item->categoria ?? '') . ' ' . ($item->unidad ?? '')) }}">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="text-sm font-bold text-slate-900 dark:text-white truncate">{{ $item->display_name }}</div>
                                    <div class="mt-1 text-[11px] text-slate-600 dark:text-slate-400 truncate">{{ $item->categoria ?? '—' }}</div>
                                    <div class="mt-1 text-[11px] text-slate-500 dark:text-slate-400 truncate">{{ $item->unidad ?? '—' }}</div>
                                </div>
                                <div class="shrink-0">
                                    <div class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 text-right">Stock</div>
                                    <div class="text-2xl font-bold text-slate-900 dark:text-white text-right leading-none mt-1">{{ $item->stock }}</div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 px-4 py-10 text-center text-slate-500 dark:text-slate-400">
                            No hay ítems activos.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-6">
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
            <div class="p-6 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
                <div class="text-sm font-bold text-slate-900 dark:text-white">Bodega</div>
                <div class="text-xs text-slate-500 dark:text-slate-400 mt-1">Nombre del lugar físico donde está guardado el material.</div>
            </div>
            <div class="p-6">
                <form method="POST" action="{{ route('inventario.config.bodega.store') }}" class="space-y-4">
                    @csrf

                    <div>
                        <label class="form-label">Nombre</label>
                        <input type="text" name="nombre" value="{{ old('nombre', $bodega->nombre ?? '') }}" class="form-input" placeholder="Ej: Bodega Sala de Máquinas" required />
                    </div>

                    <div>
                        <label class="form-label">Ubicación (opcional)</label>
                        <input type="text" name="ubicacion" value="{{ old('ubicacion', $bodega->ubicacion ?? '') }}" class="form-input" placeholder="Ej: Estante 3 / Caja plástica" />
                    </div>

                    <div class="pt-2">
                        <x-ui.button type="submit" variant="primary" size="md" icon="fas fa-save">
                            Guardar bodega
                        </x-ui.button>
                    </div>
                </form>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
            <div class="p-6 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
                <div class="text-sm font-bold text-slate-900 dark:text-white">Ítems</div>
                <div class="text-xs text-slate-500 dark:text-slate-400 mt-1">Agrega ítems con stock inicial.</div>
            </div>
            <div class="p-6">
                @if(!$bodega)
                    <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-amber-900 text-sm font-semibold">
                        Primero guarda la bodega para poder agregar ítems.
                    </div>
                @else
                    <div class="bg-slate-50 dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-5">
                        <div class="text-sm font-bold text-slate-900 dark:text-white">Ingreso de stock</div>
                        <div class="text-xs text-slate-500 dark:text-slate-400 mt-1">Suma unidades a un ítem existente (queda registro como movimiento de ingreso).</div>

                        <form method="POST" action="{{ route('inventario.config.stock.ingreso.store') }}" class="mt-4 space-y-4">
                            @csrf

                            <div>
                                <label class="form-label">Ítem</label>
                                <select name="item_id" class="form-input" required>
                                    <option value="">Seleccionar...</option>
                                    @foreach($items as $it)
                                        <option value="{{ $it->id }}">{{ $it->display_name }} (Stock: {{ $it->stock }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="form-label">Cantidad a ingresar</label>
                                    <input type="number" name="cantidad" min="1" value="1" class="form-input" required />
                                </div>
                                <div>
                                    <label class="form-label">Nota (opcional)</label>
                                    <input type="text" name="nota" class="form-input" placeholder="Ej: reposición" />
                                </div>
                            </div>

                            <div class="pt-1">
                                <x-ui.button type="submit" variant="success" size="md" icon="fas fa-plus">
                                    Ingresar stock
                                </x-ui.button>
                            </div>
                        </form>
                    </div>

                    <div class="bg-slate-50 dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-5 mt-6">
                        <div class="text-sm font-bold text-slate-900 dark:text-white">Nuevo ítem</div>
                        <div class="text-xs text-slate-500 dark:text-slate-400 mt-1">Crea un ítem (y su variante/medida) con stock inicial.</div>

                        <form method="POST" action="{{ route('inventario.config.items.store') }}" class="mt-4 space-y-4">
                            @csrf

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="form-label">Categoría (opcional)</label>
                                    <input type="text" name="categoria" value="{{ old('categoria') }}" class="form-input" />
                                </div>
                                <div>
                                    <label class="form-label">Unidad (opcional)</label>
                                    <input type="text" name="unidad" value="{{ old('unidad') }}" class="form-input" placeholder="Ej: unidades, cajas" />
                                </div>
                            </div>

                            <div>
                                <label class="form-label">Título</label>
                                <input type="text" name="titulo" value="{{ old('titulo') }}" class="form-input" required />
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="form-label">Variante (opcional)</label>
                                    <input type="text" name="variante" value="{{ old('variante') }}" class="form-input" placeholder="Ej: Adulto, Pediátrico" />
                                </div>
                                <div>
                                    <label class="form-label">Stock inicial</label>
                                    <input type="number" name="stock" min="0" value="{{ old('stock', 0) }}" class="form-input" required />
                                </div>
                            </div>

                            <div class="pt-1">
                                <x-ui.button type="submit" variant="primary" size="md" icon="fas fa-plus">
                                    Agregar ítem
                                </x-ui.button>
                            </div>
                        </form>
                    </div>

                    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden mt-6">
                        <div class="p-6 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50">
                            <div class="text-sm font-bold text-slate-900 dark:text-white">Ítems cargados</div>
                            <div class="text-xs text-slate-500 dark:text-slate-400 mt-1">Listado de ítems activos en la bodega.</div>
                        </div>

                        <div class="p-6">
                            <div class="overflow-x-auto overflow-y-auto rounded-xl border border-slate-200 dark:border-slate-700" style="max-height: 420px;">
                                <table class="min-w-full text-sm">
                                    <thead class="bg-slate-50 dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700">
                                        <tr class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                            <th class="text-left px-4 py-3">Ítem</th>
                                            <th class="text-left px-4 py-3">Categoría</th>
                                            <th class="text-right px-4 py-3">Stock</th>
                                            <th class="text-right px-4 py-3">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        @forelse($items as $item)
                                            <tr>
                                                <td class="px-4 py-3 font-semibold text-slate-900 dark:text-white">{{ $item->display_name }}</td>
                                                <td class="px-4 py-3 text-slate-600 dark:text-slate-400">{{ $item->categoria ?? '—' }}</td>
                                                <td class="px-4 py-3 text-right font-bold text-slate-900 dark:text-white">{{ $item->stock }}</td>
                                                <td class="px-4 py-3 text-right">
                                                    <form method="POST" action="{{ route('inventario.config.items.destroy', ['itemId' => $item->id]) }}" onsubmit="return confirm('¿Eliminar este ítem?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <x-ui.button type="submit" variant="danger" size="sm" icon="fas fa-trash">
                                                            Eliminar
                                                        </x-ui.button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="px-4 py-6 text-center text-slate-500 dark:text-slate-400">Aún no hay ítems.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
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
    // Send snapshot email function
    async function sendBodegaSnapshotEmail() {
        const btn = document.querySelector('button[onclick="sendBodegaSnapshotEmail()"]');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enviando...';
        btn.disabled = true;

        try {
            const res = await fetch('{{ route('inventario.snapshot.email') }}', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                credentials: 'same-origin'
            });

            const json = await res.json();

            if (json.success) {
                alert('✓ ' + json.message);
            } else {
                alert('✗ ' + (json.error || 'Error al enviar'));
            }
        } catch (e) {
            alert('✗ Error de conexión al enviar email');
        } finally {
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    }

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
