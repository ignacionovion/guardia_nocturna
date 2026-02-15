@php
    $cabinaChecklist = [
        'baston_tastik' => 'Bastón Tastik',
        'era_msa_g1' => 'ERA MSA G1',
        'chaquetillas_stex' => 'Chaquetillas STEX',
        'tablet_unidad_rx3_y_cargador' => 'Tablet unidad RX-3 y Cargador',
        'linternas_apasolo_3000' => 'Linternas APASO L3000',
        'demoledor_makita_y_accesorios' => 'Demoledor MAKITA y accesorios',
        'combi_lukas_e_draulik' => 'Combi Lukas E-Draulik GMBH',
        'cortadora_de_plasma' => 'Cortadora de Plasma',
        'dremel_y_accesorios' => 'Dremel y accesorios',
        'sierra_sable_inalambrica_makita' => 'Sierra sable inalámbrica MAKITA',
        'pistola_neumatica_airgun' => 'Pistola neumática AIRGUN',
        'taladro_inalambrico_makita' => 'Taladro Inalámbrico MAKITA',
        'esmeril_angular_125mm' => 'Esmeril angular 125 mm',
        'sierra_circular_7_1_4' => 'Sierra circular 7 1/4"',
        'rotomartillo_dewalt' => 'Rotomartillo DEWALT',
        'control_vetter_baja_presion' => 'Control VETTER baja presión',
        'grupo_electrogeno_30kva' => 'Grupo electrógeno 30 KVA',
        'motor_combustion_4t_weber' => 'Motor a Combustión 4T WEBER',
        'cizalla_expansor_ram_weber' => 'Cizalla, expansor y RAM WEBER',
        'cizalla_expansor_ram_lukas' => 'Cizalla, expansor y RAM LUKAS',
        'winche_unidad_rx3' => 'Winche de la unidad RX-3',
    ];

    $traumaChecklist = [
        'collares_cervicales' => 'Collares cervicales',
        'dea' => 'DEA',
        'bolsos_oxigenoterapia_2' => '2 Bolsos oxigenoterapia',
        'chalecos_de_extraccion' => 'Chalecos de extracción',
        'maleta_primeros_auxilios_quemados' => 'Maleta Primeros Auxilios Quemados',
        'mochila_trauma' => 'Mochila Trauma',
        'cajas_de_guantes' => 'Cajas de guantes',
        'ferulas' => 'Férulas',
        'tablas_largas' => 'Tablas Largas',
        'tabla_corta' => 'Tabla corta',
        'laterales' => 'Laterales',
        'pulpos' => 'Pulpos',
    ];

    $sections = [
        'cabina' => 'Cabina · Check list herramientas',
        'trauma' => 'Trauma',
        'cantidades' => 'Cantidades y material',
    ];

    $labels = [
        'cabina' => $cabinaChecklist,
        'trauma' => $traumaChecklist,
    ];

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
                <div class="text-xs font-black uppercase tracking-widest text-white">{{ $sec === 'cantidades' ? 'INDIQUE LA CANTIDAD' : ($sec === 'cabina' ? 'CABINA' : 'TRAUMA') }}</div>
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
