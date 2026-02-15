@php
    $cabinaChecklist = [
        'linterna_nightstick' => 'Linterna NIGHTSTICK',
        'era_scott_4_5' => 'ERA SCOTT 4.5',
        'chaquetillas_stex' => 'Chaquetillas STEX',
        'tablet_br3_y_cargador' => 'Tablet unidad BR-3 y Cargador',
        'ripper_corta_parabrisas' => 'RIPPER (corta parabrisas)',
        'baston_tastik' => 'Bastón Tastik',
        'generador_electrico_honda' => 'Generador eléctrico Honda',
        'motosierra_stihl_ms170' => 'Motosierra Stihl MS170',
        'motor_holmatro_y_herramientas' => 'Motor HOLMATRO y herramientas',
        'combi_lukas_e_draulik' => 'Combi Lukas E-Draulik GMBH',
        'motoamoladora_makita' => 'Motoamoladora MAKITA',
        'sierra_sable_hilti' => 'Sierra sable HILTI',
        'dremel_y_accesorios' => 'Dremel y accesorios',
        'martillo_neumatico' => 'Martillo neumático',
        'taladro_inalambrico_makita' => 'Taladro inalámbrico MAKITA',
        'control_cojines_vetter' => 'Control cojines VETTER',
        'esmeril_angular' => 'Esmeril angular',
    ];

    $traumaChecklist = [
        'collares_cervicales' => 'Collares cervicales',
        'dea' => 'DEA',
        'bolso_oxigenoterapia' => 'Bolso Oxigenoterapia',
        'chalecos_extraccion' => 'Chalecos de extracción',
        'tablas_largas' => 'Tablas largas',
        'tabla_pediatrica' => 'Tabla pediátrica',
        'mochila_trauma' => 'Mochila Trauma',
        'cajas_guantes' => 'Cajas de guantes',
        'ferulas' => 'Férulas',
        'tabla_scoop' => 'Tabla Scoop',
        'laterales' => 'Laterales',
        'pulpos' => 'Pulpos',
        'bolso_triage' => 'Bolso TRIAGE',
    ];

    $cantidades = [
        'mangueras_38mm' => 'Mangueras 38mm',
        'mangueras_52mm' => 'Mangueras 52mm',
        'mangueras_75mm' => 'Mangueras 75mm',
        'herraduras' => 'HERRADURAS',
        'ataques_52mm' => 'Ataques 52mm',
        'ataques_75mm' => 'Ataques 75mm',
        'traspasos' => 'Traspasos',
        'protecciones_duras_paciente' => 'Protecciones duras para paciente',
        'tecle_cadena_2000kg' => 'Tecle para cadena 2000kg',
        'cunas' => 'CUÑAS',
        'cajoneras_laterales' => 'Cajoneras laterales',
        'escalas' => 'ESCALAS',
        'set_lona_cubre_pilares' => 'Set lona cubre pilares',
        'bicheros' => 'Bicheros',
        'focos_led_tripode' => 'Focos LED con cable y trípode',
        'carrete_cable_electrico' => 'Carrete cable eléctrico',
        'cadenas_y_puntas_holmatro' => 'Cadenas y puntas Holmatro',
        'corta_parabrisas_manual' => 'Corta parabrisas manual',
        'material_techo' => 'Material del techo',
    ];
@endphp

<div class="space-y-6">
    @php($data = $data ?? [])

    <div class="rounded-2xl border border-teal-900/30 bg-sky-100 p-4">
        <div class="text-sm font-black uppercase tracking-widest text-slate-900">PLANILLA DE REVISIÓN DE NIVELES</div>
        <div class="text-sm font-black uppercase tracking-widest text-slate-900 mt-1">BR-3</div>
        <div class="text-xs text-slate-700 mt-2 font-semibold">Marca funcionamiento (Sí/No), agrega novedades y completa cantidades cuando aplique.</div>
    </div>

    <div class="bg-white rounded-2xl border border-teal-900/20 overflow-hidden">
        <button type="button" class="w-full px-4 py-3 flex items-center justify-between bg-teal-800 border-b border-teal-900" onclick="toggleSection('secCabina')">
            <div class="text-xs font-black uppercase tracking-widest text-white">CABINA</div>
            <i class="fas fa-chevron-down text-white/80"></i>
        </button>
        <div id="secCabina" class="p-4 bg-sky-50">
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
        <button type="button" class="w-full px-4 py-3 flex items-center justify-between bg-teal-800 border-b border-teal-900" onclick="toggleSection('secTrauma')">
            <div class="text-xs font-black uppercase tracking-widest text-white">TRAUMA</div>
            <i class="fas fa-chevron-down text-white/80"></i>
        </button>
        <div id="secTrauma" class="p-4 hidden bg-sky-50">
            <div class="grid grid-cols-1 gap-3">
                @foreach($traumaChecklist as $key => $label)
                    @php($row = $data['trauma'][$key] ?? [])
                    <div class="grid grid-cols-12 gap-2 items-center rounded-xl border border-slate-200 bg-white px-3 py-2">
                        <div class="col-span-12 md:col-span-5 rounded-lg bg-yellow-50 px-3 py-2 border border-yellow-100">
                            <div class="text-sm font-extrabold text-slate-900">{{ $label }}</div>
                        </div>
                        <div class="col-span-6 md:col-span-2">
                            <select name="data[trauma][{{ $key }}][funciona]" class="w-full px-3 py-2 border border-slate-200 rounded-lg bg-white font-semibold text-sm">
                                <option value="" {{ ($row['funciona'] ?? '') === '' ? 'selected' : '' }}>¿Funciona?</option>
                                <option value="si" {{ ($row['funciona'] ?? '') === 'si' ? 'selected' : '' }}>Sí</option>
                                <option value="no" {{ ($row['funciona'] ?? '') === 'no' ? 'selected' : '' }}>No</option>
                            </select>
                        </div>
                        <div class="col-span-6 md:col-span-2">
                            <input type="text" name="data[trauma][{{ $key }}][cantidad]" value="{{ $row['cantidad'] ?? '' }}" class="w-full px-3 py-2 border border-slate-200 rounded-lg bg-white font-semibold text-sm" placeholder="Cant.">
                        </div>
                        <div class="col-span-12 md:col-span-3">
                            <input type="text" name="data[trauma][{{ $key }}][novedades]" value="{{ $row['novedades'] ?? '' }}" class="w-full px-3 py-2 border border-slate-200 rounded-lg bg-white font-semibold text-sm" placeholder="Novedades">
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-teal-900/20 overflow-hidden">
        <button type="button" class="w-full px-4 py-3 flex items-center justify-between bg-teal-800 border-b border-teal-900" onclick="toggleSection('secCantidades')">
            <div class="text-xs font-black uppercase tracking-widest text-white">INDIQUE LA CANTIDAD</div>
            <i class="fas fa-chevron-down text-white/80"></i>
        </button>
        <div id="secCantidades" class="p-4 hidden bg-sky-50">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                @foreach($cantidades as $key => $label)
                    <div class="grid grid-cols-12 gap-2 items-center rounded-xl border border-slate-200 bg-white px-3 py-2">
                        <div class="col-span-7 rounded-lg bg-sky-100 px-3 py-2 border border-sky-200">
                            <div class="text-sm font-extrabold text-slate-900">{{ $label }}</div>
                        </div>
                        <div class="col-span-5">
                            <input type="text" name="data[cantidades][{{ $key }}]" value="{{ $data['cantidades'][$key] ?? '' }}" class="w-full px-3 py-2 border border-slate-200 rounded-lg bg-white font-semibold text-sm" placeholder="Cantidad" data-cantidad-item="{{ $key }}">
                        </div>

                        <div class="col-span-12 hidden" data-cantidad-novedad-row="{{ $key }}">
                            <input type="text" name="data[cantidades_novedades][{{ $key }}]" value="{{ $data['cantidades_novedades'][$key] ?? '' }}" class="w-full px-3 py-2 border border-slate-200 rounded-lg bg-white font-semibold text-sm" placeholder="Novedad">
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
