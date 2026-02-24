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

    $sections = [
        'cabina' => 'Cabina · Check list herramientas',
        'trauma' => 'Trauma',
        'cantidades' => 'Cantidades y material',
    ];

    $labels = [
        'cabina' => $cabinaChecklist,
        'trauma' => $traumaChecklist,
    ];

    // Cargar etiquetas dinámicas para que aparezcan en el historial
    $customItems = \App\Models\PlanillaListItem::where('unidad', 'BR-3')->where('is_active', true)->get();
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
