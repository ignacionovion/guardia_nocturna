@extends('layouts.app')

@section('content')
<div class="w-full py-4">
    <div class="max-w-3xl mx-auto">
        <div class="bg-slate-900 rounded-2xl overflow-hidden border border-slate-800 shadow-xl">
            <div class="p-8 text-center">
                @if(file_exists(public_path('brand/guardiappcheck.png')))
                    <img src="{{ asset('brand/guardiappcheck.png') }}" alt="GuardiaAPP" class="mx-auto h-[70px] w-auto drop-shadow-sm">
                @endif
                <div class="mt-2 text-xs font-black uppercase tracking-widest text-slate-400">Inventario</div>
                <div class="text-2xl font-extrabold text-white">Retiro de bodega</div>
                <div class="text-sm text-slate-400 mt-1">{{ $bodega->nombre }}</div>
                @if(isset($bombero) && $bombero)
                    <div class="mt-3 inline-flex items-center gap-2 rounded-full border border-emerald-500/30 bg-emerald-500/10 px-4 py-2 text-emerald-100">
                        <i class="fas fa-id-card"></i>
                        <div class="text-xs font-black uppercase tracking-widest">{{ $bombero->rut }}</div>
                        <div class="text-xs text-emerald-100/90 font-semibold">{{ trim((string)($bombero->nombres ?? '') . ' ' . (string)($bombero->apellido_paterno ?? '')) }}</div>
                    </div>
                @endif
            </div>

            <div class="p-6 border-t border-slate-800 bg-white/5">
                <div class="text-sm font-black uppercase tracking-widest text-slate-300">Registrar retiro</div>
                <div class="text-sm text-slate-300 mt-1">Selecciona un ítem e ingresa la cantidad retirada.</div>

                <form method="POST" action="{{ route('inventario.retiro.store') }}" class="mt-4 space-y-4">
                    @csrf

                    @if(session('success'))
                        <div class="rounded-xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-emerald-100">
                            <div class="text-sm font-extrabold">{{ session('success') }}</div>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="rounded-xl border border-rose-500/30 bg-rose-500/10 px-4 py-3 text-rose-100">
                            <div class="text-sm font-extrabold">Revisa los datos e intenta nuevamente.</div>
                        </div>
                    @endif

                    <div>
                        <label class="block text-xs font-black uppercase tracking-widest text-slate-300 mb-2">Item</label>
                        <select id="inv_titulo" required class="w-full px-4 py-3 rounded-xl bg-slate-900 border border-slate-700 text-slate-100 font-semibold">
                            <option value="">Seleccionar...</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-black uppercase tracking-widest text-slate-300 mb-2">Variante / Unidad</label>
                        <select id="inv_variante" name="item_id" required disabled class="w-full px-4 py-3 rounded-xl bg-slate-900 border border-slate-700 text-slate-100 font-semibold disabled:opacity-60">
                            <option value="">Seleccionar...</option>
                        </select>
                        <div id="inv_stock" class="mt-2 text-xs text-slate-400"></div>
                    </div>

                    <div>
                        <label class="block text-xs font-black uppercase tracking-widest text-slate-300 mb-2">Cantidad</label>
                        <input type="number" name="cantidad" min="1" value="{{ old('cantidad', 1) }}" required class="w-full px-4 py-3 rounded-xl bg-slate-900 border border-slate-700 text-slate-100 font-semibold" />
                    </div>

                    <div>
                        <label class="block text-xs font-black uppercase tracking-widest text-slate-300 mb-2">Nota (opcional)</label>
                        <textarea name="nota" rows="3" class="w-full px-4 py-3 rounded-xl bg-slate-900 border border-slate-700 text-slate-100 font-semibold" placeholder="Ej: Se retiró para atención de trauma">{{ old('nota') }}</textarea>
                    </div>

                    <div class="flex gap-2">
                        <a href="{{ route('inventario.retiro.identificar.form') }}" class="w-1/2 inline-flex items-center justify-center gap-2 bg-slate-800 hover:bg-slate-700 text-white font-black py-3 px-6 rounded-xl text-[11px] uppercase tracking-widest border border-slate-700">
                            <i class="fas fa-id-card"></i>
                            Cambiar RUT
                        </a>
                        <button type="submit" class="w-1/2 inline-flex items-center justify-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white font-black py-3 px-6 rounded-xl text-[11px] transition-all shadow-md hover:shadow-lg uppercase tracking-widest">
                            <i class="fas fa-check"></i>
                            Registrar retiro
                        </button>
                    </div>
                </form>

                <div class="mt-8 bg-white/5 border border-white/10 rounded-2xl overflow-hidden">
                    <div class="p-6 border-b border-white/10">
                        <div class="text-sm font-black uppercase tracking-widest text-slate-300">Baja de inventario</div>
                        <div class="text-sm text-slate-300 mt-1">Historial de salidas de la bodega.</div>
                    </div>

                    <div class="p-6">
                        @if(isset($movimientos) && $movimientos && count($movimientos) > 0)
                            <div class="overflow-x-auto rounded-xl border border-white/10">
                                <table class="min-w-full text-sm">
                                    <thead class="bg-white/5 border-b border-white/10">
                                        <tr class="text-xs font-black uppercase tracking-widest text-slate-300">
                                            <th class="text-left px-4 py-3">Fecha</th>
                                            <th class="text-left px-4 py-3">Ítem</th>
                                            <th class="text-right px-4 py-3">Cantidad</th>
                                            <th class="text-left px-4 py-3">Bombero</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-white/10">
                                        @foreach($movimientos as $m)
                                            <tr>
                                                <td class="px-4 py-3 text-slate-300 whitespace-nowrap">{{ optional($m->created_at)->format('d-m-Y H:i') }}</td>
                                                <td class="px-4 py-3 text-slate-100 font-semibold">{{ $m->item?->display_name ?? '—' }}</td>
                                                <td class="px-4 py-3 text-right text-slate-100 font-extrabold">{{ $m->cantidad }}</td>
                                                <td class="px-4 py-3 text-slate-300">
                                                    @if($m->firefighter)
                                                        <div class="font-semibold">{{ $m->firefighter->rut }}</div>
                                                        <div class="text-xs text-slate-400">{{ trim((string)($m->firefighter->nombres ?? '') . ' ' . (string)($m->firefighter->apellido_paterno ?? '')) }}</div>
                                                    @else
                                                        —
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-sm text-slate-400">Aún no hay bajas registradas.</div>
                        @endif
                    </div>
                </div>

                @php
                    $invItemsForJs = ($items ?? collect())->map(function ($i) {
                        return [
                            'id' => $i->id,
                            'titulo' => (string) ($i->titulo ?? ''),
                            'variante' => (string) ($i->variante ?? ''),
                            'unidad' => (string) ($i->unidad ?? ''),
                            'stock' => (int) ($i->stock ?? 0),
                        ];
                    })->values();
                @endphp

                <script>
                    (function () {
                        const items = @json($invItemsForJs);

                        const tituloSelect = document.getElementById('inv_titulo');
                        const varianteSelect = document.getElementById('inv_variante');
                        const stockEl = document.getElementById('inv_stock');
                        if (!tituloSelect || !varianteSelect) return;

                        const byTitulo = new Map();
                        items.forEach((it) => {
                            const t = (it.titulo || '').trim();
                            if (!byTitulo.has(t)) byTitulo.set(t, []);
                            byTitulo.get(t).push(it);
                        });

                        const titulos = Array.from(byTitulo.keys()).filter(Boolean).sort((a, b) => a.localeCompare(b));
                        titulos.forEach((t) => {
                            const opt = document.createElement('option');
                            opt.value = t;
                            opt.textContent = t;
                            tituloSelect.appendChild(opt);
                        });

                        const oldItemId = @json((string) old('item_id'));
                        let oldTitulo = '';
                        if (oldItemId) {
                            const found = items.find((i) => String(i.id) === String(oldItemId));
                            if (found) oldTitulo = (found.titulo || '').trim();
                        }

                        function fmtVariante(it) {
                            const v = (it.variante || '').trim();
                            const u = (it.unidad || '').trim();
                            const base = v !== '' ? v : 'Sin variante';
                            const su = u !== '' ? (' · ' + u) : '';
                            return base + su + ' (Stock: ' + String(it.stock ?? 0) + ')';
                        }

                        function fillVariantes(titulo, preselectId) {
                            while (varianteSelect.options.length > 1) {
                                varianteSelect.remove(1);
                            }
                            stockEl.textContent = '';

                            const arr = byTitulo.get(titulo) || [];
                            arr
                                .slice()
                                .sort((a, b) => fmtVariante(a).localeCompare(fmtVariante(b)))
                                .forEach((it) => {
                                    const opt = document.createElement('option');
                                    opt.value = String(it.id);
                                    opt.textContent = fmtVariante(it);
                                    if (preselectId && String(preselectId) === String(it.id)) {
                                        opt.selected = true;
                                        stockEl.textContent = 'Stock disponible: ' + String(it.stock ?? 0);
                                    }
                                    varianteSelect.appendChild(opt);
                                });

                            varianteSelect.disabled = arr.length === 0;
                        }

                        tituloSelect.addEventListener('change', function () {
                            fillVariantes(this.value || '', null);
                        });

                        varianteSelect.addEventListener('change', function () {
                            const id = this.value;
                            const it = items.find((i) => String(i.id) === String(id));
                            if (!it) {
                                stockEl.textContent = '';
                                return;
                            }
                            stockEl.textContent = 'Stock disponible: ' + String(it.stock ?? 0);
                        });

                        if (oldTitulo) {
                            tituloSelect.value = oldTitulo;
                            fillVariantes(oldTitulo, oldItemId);
                            varianteSelect.disabled = false;
                        }
                    })();
                </script>
            </div>
        </div>
    </div>
</div>
@endsection
