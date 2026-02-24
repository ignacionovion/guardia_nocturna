@php
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

    $sections = [
        'cabina' => 'Cabina · Check list herramientas',
        'cantidades' => 'Cantidades y material',
    ];

    $labels = [
        'cabina' => $cabinaChecklist,
    ];

    // Cargar etiquetas dinámicas
    $customItems = \App\Models\PlanillaListItem::where('unidad', 'B-3')->where('is_active', true)->get();
    foreach($customItems as $ci) {
        $labels[$ci->section][$ci->item_key] = $ci->label;
    }

    $boolLabel = function ($v) {
        if ($v === 'si') return 'Sí';
        if ($v === 'no') return 'No';
        return '—';
    };
@endphp

<div class="space-y-4">
    @foreach($sections as $sec => $title)
        <div class="rounded-2xl border border-teal-900/20 overflow-hidden">
            <div class="px-4 py-3 bg-teal-800 border-b border-teal-900">
                <div class="text-xs font-black uppercase tracking-widest text-white">{{ $sec === 'cantidades' ? 'INDIQUE LA CANTIDAD' : 'CABINA' }}</div>
            </div>
            <div class="p-4 bg-sky-50">
                @if($sec === 'cantidades')
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                        @foreach(($data['cantidades'] ?? []) as $k => $v)
                            <div class="rounded-xl border border-slate-200 bg-white px-4 py-2">
                                <div class="flex items-center justify-between">
                                    <div class="text-sm font-extrabold text-slate-900">{{ $k }}</div>
                                    <div class="text-sm font-extrabold text-slate-700">{{ $v !== '' ? $v : '—' }}</div>
                                </div>
                                @php($nov = $data['cantidades_novedades'][$k] ?? '')
                                @if((string) $v === '0' && trim((string) $nov) !== '')
                                    <div class="mt-2 text-xs font-semibold text-slate-600">
                                        Novedad: {{ $nov }}
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-4">
                        <div class="text-xs font-black uppercase tracking-widest text-slate-500">Observaciones generales</div>
                        <div class="mt-2 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 font-semibold">
                            {{ ($data['observaciones_generales'] ?? '') !== '' ? $data['observaciones_generales'] : '—' }}
                        </div>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="text-xs font-black uppercase tracking-widest text-slate-700 bg-sky-100 border border-sky-200">
                                    <th class="text-left py-2 px-3">Ítem</th>
                                    <th class="text-left py-2 px-3">Funciona</th>
                                    <th class="text-left py-2 px-3">Cantidad</th>
                                    <th class="text-left py-2 px-3">Novedades</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white">
                                @foreach(($data[$sec] ?? []) as $key => $row)
                                    <tr class="border border-slate-200">
                                        <td class="py-2 px-3 font-extrabold text-slate-900 bg-yellow-50">{{ $labels[$sec][$key] ?? $key }}</td>
                                        <td class="py-2 px-3 text-slate-700 font-semibold">{{ $boolLabel($row['funciona'] ?? null) }}</td>
                                        <td class="py-2 px-3 text-slate-700 font-semibold">{{ ($row['cantidad'] ?? '') !== '' ? $row['cantidad'] : '—' }}</td>
                                        <td class="py-2 px-3 text-slate-700 font-semibold">{{ ($row['novedades'] ?? '') !== '' ? $row['novedades'] : '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    @endforeach
</div>
