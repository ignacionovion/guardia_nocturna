@php
    $data = $data ?? [];
    $readonly = $readonly ?? false;

    // Get custom item keys to avoid duplicates
    $customCabinaKeys = isset($customItems['cabina']) 
        ? $customItems['cabina']->pluck('item_key')->toArray() 
        : [];

    $cabinaChecklist = [
        'era_msa_g1' => 'ERA MSA G 1',
        'linterna_nightstick_xpp_5568' => 'Linterna NIGHTSTICK XPP-5568',
        'tablet_unidad_b3_y_cargador' => 'Tablet unidad B-3 y Cargador',
        'camara_termal' => 'Cámara Termal',
        'baston_tastik' => 'Bastón Tastik',
        'detector_gas_tif8900' => 'Detector de Gas TIF8900',
        'motosierra_cutter_edge' => 'Motosierra "CUTTER EDGE"',
        'taladro_inalambrico_makita' => 'Taladro Inalámbrico MAKITA',
        'motobomba_rosenbauer' => 'Motobomba Rosenbauer',
        'aspirador_nautilus_8_1' => 'Aspirador NAUTILUS 8/1',
        'motoamoladora_makita_m14' => 'Motoamoladora M14 MAKITA',
        'motosierra_stihl' => 'Motosierra STIHL',
        'motor_electrogeno_rs14' => 'Motor electrógeno RS 14',
        'focos_led_1000w' => 'Focos LED 1000 watt',
        'foco_inalambrico_makita_tripode' => 'Foco inalámbrico Makita & trípode',
        'winche_unidad_b3' => 'Winche unidad B-3',
        'ventilador_rosenbauer' => 'Ventilador Rosenbauer',
    ];

    // Filter out items that exist in customItems to avoid duplicates
    $filteredCabinaChecklist = array_diff_key($cabinaChecklist, array_flip($customCabinaKeys));

    $cantidades = [
        'mangueras' => 'Mangueras',
        'paquete_circular' => 'Paquete Circular',
        'ataques' => 'Ataques',
        'traspasos' => 'Traspasos',
        'llaves_de_copla' => 'Llaves de copla',
        'escalas_de_techo' => 'Escalas de techo',
        'combo_8_libras' => 'Combo de 8 libras',
        'bicheros' => 'Bicheros',
        'caja_de_herramientas' => 'Caja de Herramientas',
        'grembio' => 'Grembio',
        'filtro_de_aspiracion' => 'Filtro de Aspiración',
        'kit_quemados_asistente_trauma' => 'Kit quemados / Asistente de Trauma',
        'bidon_emergencia_verde_10l' => 'Bidón de emergencia Verde 10L',
        'extintor_de_agua' => 'Extintor de agua',
    ];
@endphp

<div class="space-y-6">
    <div class="rounded-2xl border border-teal-900/30 bg-sky-100 p-4">
        <div class="text-sm font-black uppercase tracking-widest text-slate-900">PLANILLA DE REVISIÓN DE NIVELES</div>
        <div class="text-sm font-black uppercase tracking-widest text-slate-900 mt-1">B-3</div>
        <div class="text-xs text-slate-700 mt-2 font-semibold">Marca funcionamiento (Sí/No), agrega novedades y completa cantidades cuando aplique.</div>
    </div>

    <div class="bg-white rounded-2xl border border-teal-900/20 overflow-hidden">
        <button type="button" class="w-full px-4 py-3 flex items-center justify-between bg-teal-800 border-b border-teal-900" onclick="toggleSection('secCabinaB3')">
            <div class="text-xs font-black uppercase tracking-widest text-white">CABINA</div>
            <i class="fas fa-chevron-down text-white/80"></i>
        </button>
        <div id="secCabinaB3" class="p-4 bg-sky-50">
            <div class="rounded-xl border border-teal-900/20 bg-sky-100 px-4 py-2 mb-4">
                <div class="text-xs font-black uppercase tracking-widest text-slate-900">Check list herramientas</div>
            </div>

            <div class="grid grid-cols-1 gap-3">
                @foreach($filteredCabinaChecklist as $key => $label)
                    @php($row = $data['cabina'][$key] ?? [])
                    <div class="grid grid-cols-12 gap-2 items-center rounded-xl border border-slate-200 bg-white px-3 py-2">
                        <div class="col-span-12 md:col-span-5 rounded-lg bg-yellow-50 px-3 py-2 border border-yellow-100">
                            <div class="text-sm font-extrabold text-slate-900">{{ $label }}</div>
                        </div>
                        <div class="col-span-6 md:col-span-2">
                            @if($readonly)
                                <div class="px-3 py-2 text-sm font-semibold {{ ($row['funciona'] ?? '') === 'si' ? 'text-emerald-600' : (($row['funciona'] ?? '') === 'no' ? 'text-red-600' : 'text-slate-400') }}">
                                    {{ ($row['funciona'] ?? '') === 'si' ? 'Sí' : (($row['funciona'] ?? '') === 'no' ? 'No' : '—') }}
                                </div>
                            @else
                                <select name="data[cabina][{{ $key }}][funciona]" class="w-full px-3 py-2 border border-slate-200 rounded-lg bg-white font-semibold text-sm">
                                    <option value="" {{ ($row['funciona'] ?? '') === '' ? 'selected' : '' }}>¿Funciona?</option>
                                    <option value="si" {{ ($row['funciona'] ?? '') === 'si' ? 'selected' : '' }}>Sí</option>
                                    <option value="no" {{ ($row['funciona'] ?? '') === 'no' ? 'selected' : '' }}>No</option>
                                </select>
                            @endif
                        </div>
                        <div class="col-span-6 md:col-span-2">
                            @if($readonly)
                                <div class="px-3 py-2 text-sm font-semibold text-slate-700">{{ ($row['cantidad'] ?? '') !== '' ? $row['cantidad'] : '—' }}</div>
                            @else
                                <input type="text" name="data[cabina][{{ $key }}][cantidad]" value="{{ $row['cantidad'] ?? '' }}" class="w-full px-3 py-2 border border-slate-200 rounded-lg bg-white font-semibold text-sm" placeholder="Cant.">
                            @endif
                        </div>
                        <div class="col-span-12 md:col-span-3">
                            @if($readonly)
                                <div class="px-3 py-2 text-sm font-semibold text-slate-700">{{ ($row['novedades'] ?? '') !== '' ? $row['novedades'] : '—' }}</div>
                            @else
                                <input type="text" name="data[cabina][{{ $key }}][novedades]" value="{{ $row['novedades'] ?? '' }}" class="w-full px-3 py-2 border border-slate-200 rounded-lg bg-white font-semibold text-sm" placeholder="Novedades">
                            @endif
                        </div>
                    </div>
                @endforeach

                {{-- Ítems dinámicos de Cabina --}}
                @if(isset($customItems['cabina']))
                    @foreach($customItems['cabina'] as $item)
                        @php($key = $item->item_key)
                        @php($row = $data['cabina'][$key] ?? [])
                        <div class="grid grid-cols-12 gap-2 items-center rounded-xl border border-slate-300 bg-white px-3 py-2">
                            <div class="col-span-12 md:col-span-5 rounded-lg bg-cyan-50 px-3 py-2 border border-cyan-100">
                                <div class="text-sm font-extrabold text-slate-900">{{ $item->label }}</div>
                                <div class="text-[9px] font-bold text-cyan-600 uppercase tracking-tight">Nuevo Item</div>
                            </div>
                            <div class="col-span-6 md:col-span-2">
                                <select name="data[cabina][{{ $key }}][funciona]" class="w-full px-3 py-2 border border-slate-200 rounded-lg bg-white font-semibold text-sm">
                                    <option value="" {{ ($row['funciona'] ?? '') === '' ? 'selected' : '' }}>¿Funciona?</option>
                                    <option value="si" {{ ($row['funciona'] ?? '') === 'si' ? 'selected' : '' }}>Sí</option>
                                    <option value="no" {{ ($row['funciona'] ?? '') === 'no' ? 'selected' : '' }}>No</option>
                                </select>
                            </div>
                            <div class="col-span-6 md:col-span-2">
                                <input type="text" name="data[cabina][{{ $key }}][cantidad]" value="{{ $row['cantidad'] ?? '' }}" class="w-full px-3 py-2 border border-slate-200 rounded-lg bg-white font-semibold text-sm" placeholder="Cant.">
                            </div>
                            <div class="col-span-12 md:col-span-3">
                                <input type="text" name="data[cabina][{{ $key }}][novedades]" value="{{ $row['novedades'] ?? '' }}" class="w-full px-3 py-2 border border-slate-200 rounded-lg bg-white font-semibold text-sm" placeholder="Novedades">
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-teal-900/20 overflow-hidden">
        <button type="button" class="w-full px-4 py-3 flex items-center justify-between bg-teal-800 border-b border-teal-900" onclick="toggleSection('secCantidadesB3')">
            <div class="text-xs font-black uppercase tracking-widest text-white">INDIQUE LA CANTIDAD</div>
            <i class="fas fa-chevron-down text-white/80"></i>
        </button>
        <div id="secCantidadesB3" class="p-4 hidden bg-sky-50">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                @foreach($cantidades as $key => $label)
                    <div class="grid grid-cols-12 gap-2 items-center rounded-xl border border-slate-200 bg-white px-3 py-2">
                        <div class="col-span-7 rounded-lg bg-sky-100 px-3 py-2 border border-sky-200">
                            <div class="text-sm font-extrabold text-slate-900">{{ $label }}</div>
                        </div>
                        <div class="col-span-5">
                            @if($readonly)
                                <div class="px-3 py-2 text-sm font-semibold text-slate-700">{{ ($data['cantidades'][$key] ?? '') !== '' ? $data['cantidades'][$key] : '—' }}</div>
                            @else
                                <input type="text" name="data[cantidades][{{ $key }}]" value="{{ $data['cantidades'][$key] ?? '' }}" class="w-full px-3 py-2 border border-slate-200 rounded-lg bg-white font-semibold text-sm" placeholder="Cantidad" data-cantidad-item="{{ $key }}">
                            @endif
                        </div>

                        <div class="col-span-12 {{ $readonly ? '' : 'hidden' }}" data-cantidad-novedad-row="{{ $key }}">
                            @if($readonly)
                                @if(($data['cantidades_novedades'][$key] ?? '') !== '')
                                    <div class="mt-2 text-xs font-semibold text-slate-600">Novedad: {{ $data['cantidades_novedades'][$key] }}</div>
                                @endif
                            @else
                                <input type="text" name="data[cantidades_novedades][{{ $key }}]" value="{{ $data['cantidades_novedades'][$key] ?? '' }}" class="w-full px-3 py-2 border border-slate-200 rounded-lg bg-white font-semibold text-sm" placeholder="Novedad">
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-4">
                <div class="text-xs font-black uppercase tracking-widest text-slate-500 mb-2">Observaciones generales</div>
                @if($readonly)
                    <div class="px-3 py-2 text-sm font-semibold text-slate-700 bg-slate-50 rounded-lg min-h-[60px]">{{ ($data['observaciones_generales'] ?? '') !== '' ? $data['observaciones_generales'] : '—' }}</div>
                @else
                    <textarea name="data[observaciones_generales]" rows="3" class="w-full px-3 py-2 border border-slate-200 rounded-lg bg-white font-semibold text-sm" placeholder="Observaciones...">{{ $data['observaciones_generales'] ?? '' }}</textarea>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
    function toggleSection(id) {
        const el = document.getElementById(id);
        if (!el) return;
        el.classList.toggle('hidden');
    }

    document.addEventListener('DOMContentLoaded', function () {
        function syncCantidadNovedad(key) {
            const cantidad = document.querySelector('[data-cantidad-item="' + key + '"]');
            const row = document.querySelector('[data-cantidad-novedad-row="' + key + '"]');
            if (!cantidad || !row) return;

            const v = String(cantidad.value ?? '').trim();
            if (v === '0') {
                row.classList.remove('hidden');
            } else {
                row.classList.add('hidden');
                const input = row.querySelector('input');
                if (input) input.value = '';
            }
        }

        document.querySelectorAll('[data-cantidad-item]').forEach(function (input) {
            const key = input.getAttribute('data-cantidad-item');
            if (!key) return;
            syncCantidadNovedad(key);
            input.addEventListener('input', function () {
                syncCantidadNovedad(key);
            });
        });
    });
</script>
