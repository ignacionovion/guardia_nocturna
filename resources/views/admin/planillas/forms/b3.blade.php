@php
    $data = $data ?? [];

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
                @foreach($cabinaChecklist as $key => $label)
                    @php($row = $data['cabina'][$key] ?? [])
                    <div class="grid grid-cols-12 gap-2 items-center rounded-xl border border-slate-200 bg-white px-3 py-2">
                        <div class="col-span-12 md:col-span-5 rounded-lg bg-yellow-50 px-3 py-2 border border-yellow-100">
                            <div class="text-sm font-extrabold text-slate-900">{{ $label }}</div>
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
                            <input type="text" name="data[cantidades][{{ $key }}]" value="{{ $data['cantidades'][$key] ?? '' }}" class="w-full px-3 py-2 border border-slate-200 rounded-lg bg-white font-semibold text-sm" placeholder="Cantidad">
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-4">
                <div class="text-xs font-black uppercase tracking-widest text-slate-500 mb-2">Observaciones generales</div>
                <textarea name="data[observaciones_generales]" rows="3" class="w-full px-3 py-2 border border-slate-200 rounded-lg bg-white font-semibold text-sm" placeholder="Observaciones...">{{ $data['observaciones_generales'] ?? '' }}</textarea>
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
</script>
