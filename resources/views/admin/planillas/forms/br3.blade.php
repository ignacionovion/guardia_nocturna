@php
    $data = $data ?? [];

    // CABINA items - primera fila con múltiples campos
    $cabinaPrincipal = [
        'linterna_nightstick' => ['label' => 'Linterna NIGHTSTICK', 'tipo' => 'funcionamiento'],
        'era_scott_4_5' => ['label' => 'ERA SCOTT 4.5', 'tipo' => 'cantidad_funcionamiento'],
        'radios_baofeng' => ['label' => 'Radios Baofeng', 'tipo' => 'numero_bat'],
        'chaquetillas_stex' => ['label' => 'Chaquetillas STEX', 'tipo' => 'cantidad'],
        'tablet_cargador' => ['label' => 'Tablet unidad BR-3 y Cargador', 'tipo' => 'bateria'],
        'lona_organizadora' => ['label' => 'Lona organizadora material', 'tipo' => 'simple'],
        'maleta_sci' => ['label' => 'Maleta SCI', 'tipo' => 'simple'],
    ];

    // Check list Herramientas - columna de Sí/NO
    $checklistHerramientas = [
        'ripper' => 'RIPPER (corta parabrisas)',
        'baston_tastik' => 'Bastón Tastik',
        'generador_honda' => 'Generador eléctrico Honda',
        'motosierra_stihl' => 'Motosierra Stihl MS170',
        'motor_holmatro' => 'Motor HOLMATRO y herramientas',
        'combi_lukas' => 'Combi Lukas E-Draulik GMBH',
        'motoamoladora_makita' => 'Motoamoladora MAKITA',
        'sierra_sable_hilty' => 'Sierra sable HILTY',
        'dremel' => 'Dremel y accesorios',
        'martillo_neumatico' => 'Martillo Neumático',
        'taladro_makita' => 'Taladro inalámbrico MAKITA',
        'control_vetter' => 'Control cojines VETTER',
        'esmeril_angular' => 'Esmeril angular',
    ];

    // TRAUMA - dos columnas
    $traumaIzquierda = [
        ['key' => 'collares_cervicales', 'label' => 'Collares cervicales', 'tipo' => 'adulto_ped'],
        ['key' => 'dea', 'label' => 'DEA', 'tipo' => 'bateria_pct'],
        ['key' => 'bolso_oxigeno', 'label' => 'Bolso Oxigenoterapia', 'tipo' => 'nivelo'],
        ['key' => 'chalecos_extricacion', 'label' => 'Chalecos de extricación', 'tipo' => 'adulto_ped'],
        ['key' => 'tablas_largas', 'label' => 'Tablas Largas', 'tipo' => 'g2_techo'],
        ['key' => 'tabla_pediatrica', 'label' => 'Tabla pediátrica', 'tipo' => 'tabla_corta'],
    ];

    $traumaDerecha = [
        ['key' => 'mochila_trauma', 'label' => 'Mochila Trauma', 'tipo' => 'simple'],
        ['key' => 'cajas_guantes', 'label' => 'Cajas de guantes', 'tipo' => 'simple'],
        ['key' => 'ferulas', 'label' => 'Férulas', 'tipo' => 'simple'],
        ['key' => 'tabla_scoop', 'label' => 'Tabla Scoop', 'tipo' => 'simple'],
        ['key' => 'laterales', 'label' => 'Laterales', 'tipo' => 'simple'],
        ['key' => 'pulpos', 'label' => 'Pulpos', 'tipo' => 'simple'],
        ['key' => 'bolso_triage', 'label' => 'Bolso TRIAGE', 'tipo' => 'simple'],
    ];

    // Indique la cantidad - estructura compleja
    $cantidadesSecciones = [
        'mangueras' => [
            'titulo' => 'MANGUERAS',
            'columnas' => ['38mm', '52mm', '75mm', 'Armada Base'],
        ],
        'herraduras' => [
            'titulo' => 'HERRADURAS',
            'columnas' => ['Cantidad', 'Llaves de copla', 'Pitón Rosenbauer 52'],
        ],
        'ataques' => [
            'titulo' => 'ATAQUES',
            'columnas' => ['52mm', '75mm', 'Manguera L D H'],
        ],
        'traspasos' => [
            'titulo' => 'TRASPASOS',
            'columnas' => ['Cantidad', 'Llave de grifo', 'Trifurca', 'Traspaso grifo'],
        ],
        'protecciones' => [
            'titulo' => 'Protecciones duras para paciente',
            'columnas' => ['Cojines VETTER', 'Eslings'],
        ],
        'tecle' => [
            'titulo' => 'Tecle para cadena 2000kg',
            'columnas' => ['Caja de Herramientas', 'Cubre Airbag'],
        ],
    ];

    // CUÑAS con 5 columnas
    $cunas = [
        'titulo' => 'CUÑAS',
        'columnas' => ['Biseladas', 'Bloques', 'Escalonadas', 'Planas', 'Combos de 2 libras'],
    ];

    // CAJONERAS LATERALES
    $cajoneras = [
        'titulo' => 'CAJONERAS LATERALES',
        'columnas' => ['Barretilla', 'Napoleón', 'Set Stab Fast XL', 'First Responder Jack'],
    ];

    // ESCALAS
    $escalas = [
        'titulo' => 'ESCALAS',
        'columnas' => ['2 cuerpos 12m', '2 cuerpos 8m', 'Escala plegable'],
    ];

    // Resto de items de cantidades
    $otrosCantidades = [
        'lona_pilares' => ['label' => 'Set lona cubre pilares', 'cols' => ['Rozón', 'Chuzo']],
        'bicheros' => ['label' => 'Bicheros', 'cols' => ['Conos', 'Halligan']],
        'focos_led' => ['label' => 'Focos LED con cable y trípode', 'cols' => ['Hacha suela', 'TNT']],
        'carrete' => ['label' => 'Carrete cable eléctrico', 'cols' => ['Hacha bombero', 'Halligan']],
        'cadenas_holmatro' => ['label' => 'Cadenas y puntas Holmatro', 'cols' => ['Extintor de agua', 'Pitón monitor']],
        'corta_parabrisas' => ['label' => 'Corta parabrisas manual', 'cols' => ['Force', 'Cámara Termal', 'Bomba Espalda']],
    ];

    // Material del Techo
    $materialTecho = 'Material del Techo (4 palas, 2 pasatiras, 2 McLeod, 3 rastrillos cegadores, 2 Chorizos, 1 Filtro de aspiración)';

    // General Unidad
    $generalUnidad = [
        ['label' => 'Alarmas Sonoras y Visuales', 'sub' => 'Aseo Interior Cabina Aseo Gavetas y Herramientas'],
        ['label' => 'Nivel Combustible del Bidón', 'sub' => 'Nivel Aceite para mezcla'],
        ['label' => 'Nivel Combustible de la Unidad', 'sub' => 'Nivel de agua de la unidad'],
    ];
@endphp

<div class="space-y-4">

    <div class="bg-white rounded-2xl border border-teal-900/20 overflow-hidden">
        <button type="button" class="w-full px-4 py-3 flex items-center justify-between bg-teal-800 border-b border-teal-900" onclick="toggleSection('secCabina')">
            <div class="text-xs font-black uppercase tracking-widest text-white">CABINA</div>
            <i class="fas fa-chevron-down text-white/80"></i>
        </button>
        <div id="secCabina" class="p-4 bg-sky-50">
            {{-- CABINA Principal - items complejos --}}
            <div class="space-y-2 mb-4">
                @foreach($cabinaPrincipal as $key => $item)
                    @php($row = $data['cabina'][$key] ?? [])
                    <div class="grid grid-cols-12 gap-2 items-center rounded-xl border border-slate-200 bg-white px-3 py-2">
                        <div class="col-span-4 rounded-lg bg-yellow-50 px-3 py-2 border border-yellow-100">
                            <div class="text-sm font-extrabold text-slate-900">{{ $item['label'] }}</div>
                        </div>
                        
                        @if($item['tipo'] == 'funcionamiento')
                            <div class="col-span-4">
                                <select name="data[cabina][{{ $key }}][funcionamiento]" class="w-full px-3 py-2 border border-slate-200 rounded-lg bg-white font-semibold text-sm">
                                    <option value="">Funcionamiento</option>
                                    <option value="si" {{ ($row['funcionamiento'] ?? '') === 'si' ? 'selected' : '' }}>Sí</option>
                                    <option value="no" {{ ($row['funcionamiento'] ?? '') === 'no' ? 'selected' : '' }}>No</option>
                                </select>
                            </div>
                        @elseif($item['tipo'] == 'cantidad_funcionamiento')
                            <div class="col-span-2">
                                <input type="text" name="data[cabina][{{ $key }}][cantidad]" value="{{ $row['cantidad'] ?? '' }}" placeholder="Cantidad" class="w-full px-3 py-2 border border-slate-200 rounded-lg bg-white font-semibold text-sm">
                            </div>
                            <div class="col-span-3">
                                <select name="data[cabina][{{ $key }}][funcionamiento]" class="w-full px-3 py-2 border border-slate-200 rounded-lg bg-white font-semibold text-sm">
                                    <option value="">Funcionamiento</option>
                                    <option value="si" {{ ($row['funcionamiento'] ?? '') === 'si' ? 'selected' : '' }}>Sí</option>
                                    <option value="no" {{ ($row['funcionamiento'] ?? '') === 'no' ? 'selected' : '' }}>No</option>
                                </select>
                            </div>
                            <div class="col-span-2 text-xs font-bold text-slate-500">Nivel de aire</div>
                        @elseif($item['tipo'] == 'numero_bat')
                            <div class="col-span-2">
                                <input type="text" name="data[cabina][{{ $key }}][numero]" value="{{ $row['numero'] ?? '' }}" placeholder="N°" class="w-full px-3 py-2 border border-slate-200 rounded-lg bg-white font-semibold text-sm">
                            </div>
                            <div class="col-span-2">
                                <input type="text" name="data[cabina][{{ $key }}][bat]" value="{{ $row['bat'] ?? '' }}" placeholder="Bat" class="w-full px-3 py-2 border border-slate-200 rounded-lg bg-white font-semibold text-sm">
                            </div>
                        @elseif($item['tipo'] == 'cantidad')
                            <div class="col-span-3">
                                <input type="text" name="data[cabina][{{ $key }}][cantidad]" value="{{ $row['cantidad'] ?? '' }}" placeholder="Cantidad" class="w-full px-3 py-2 border border-slate-200 rounded-lg bg-white font-semibold text-sm">
                            </div>
                        @elseif($item['tipo'] == 'bateria')
                            <div class="col-span-3">
                                <input type="text" name="data[cabina][{{ $key }}][bateria]" value="{{ $row['bateria'] ?? '' }}" placeholder="Batería" class="w-full px-3 py-2 border border-slate-200 rounded-lg bg-white font-semibold text-sm">
                            </div>
                        @elseif($item['tipo'] == 'simple')
                            <div class="col-span-6">
                                <input type="text" name="data[cabina][{{ $key }}][valor]" value="{{ $row['valor'] ?? '' }}" class="w-full px-3 py-2 border border-slate-200 rounded-lg bg-white font-semibold text-sm">
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            {{-- Check list Herramientas --}}
            <div class="rounded-xl border border-teal-900/20 bg-sky-100 px-4 py-2 mb-4">
                <div class="text-xs font-black uppercase tracking-widest text-slate-900">Check list Herramientas</div>
            </div>
            
            {{-- Encabezados Sí/NO/Novedades --}}
            <div class="grid grid-cols-12 gap-2 mb-2 text-center text-xs font-bold text-slate-700">
                <div class="col-span-6"></div>
                <div class="col-span-2 bg-teal-700 text-white py-1 rounded">Sí</div>
                <div class="col-span-2 bg-teal-700 text-white py-1 rounded">NO</div>
                <div class="col-span-2 bg-teal-700 text-white py-1 rounded">Novedades</div>
            </div>
            
            {{-- Items del checklist --}}
            <div class="space-y-1">
                @foreach($checklistHerramientas as $key => $label)
                    @php($row = $data['herramientas'][$key] ?? [])
                    <div class="grid grid-cols-12 gap-2 items-center bg-white rounded border border-slate-200">
                        <div class="col-span-6 px-3 py-2 text-sm font-semibold {{ in_array($key, ['generador_honda', 'motosierra_stihl', 'motor_holmatro', 'combi_lukas', 'motoamoladora_makita', 'sierra_sable_hilty']) ? 'bg-yellow-50' : '' }}">{{ $label }}</div>
                        <div class="col-span-2 text-center py-2">
                            <input type="radio" name="data[herramientas][{{ $key }}][funciona]" value="si" {{ ($row['funciona'] ?? '') === 'si' ? 'checked' : '' }} class="w-4 h-4 text-teal-600">
                        </div>
                        <div class="col-span-2 text-center py-2">
                            <input type="radio" name="data[herramientas][{{ $key }}][funciona]" value="no" {{ ($row['funciona'] ?? '') === 'no' ? 'checked' : '' }} class="w-4 h-4 text-red-600">
                        </div>
                        <div class="col-span-2 px-2 py-1">
                            <input type="text" name="data[herramientas][{{ $key }}][novedades]" value="{{ $row['novedades'] ?? '' }}" class="w-full px-2 py-1 text-sm border border-slate-200 rounded">
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- TRAUMA --}}
    <div class="bg-white rounded-2xl border border-teal-900/20 overflow-hidden">
        <button type="button" class="w-full px-4 py-3 flex items-center justify-between bg-teal-800 border-b border-teal-900" onclick="toggleSection('secTrauma')">
            <div class="text-xs font-black uppercase tracking-widest text-white">TRAUMA</div>
            <i class="fas fa-chevron-down text-white/80"></i>
        </button>
        <div id="secTrauma" class="p-4 hidden bg-sky-50">
            <div class="grid grid-cols-2 gap-4">
                {{-- Columna Izquierda --}}
                <div class="space-y-2">
                    @foreach($traumaIzquierda as $item)
                        @php($key = $item['key'])
                        @php($row = $data['trauma'][$key] ?? [])
                        <div class="rounded-lg border border-slate-200 bg-white p-2">
                            <div class="font-bold text-sm mb-2 {{ in_array($key, ['chalecos_extricacion', 'tablas_largas']) ? 'bg-yellow-50 p-1 rounded' : '' }}">{{ $item['label'] }}</div>
                            @if($item['tipo'] == 'adulto_ped')
                                <div class="grid grid-cols-2 gap-2">
                                    <input type="text" name="data[trauma][{{ $key }}][adulto]" value="{{ $row['adulto'] ?? '' }}" placeholder="Adulto" class="w-full px-2 py-1 text-sm border border-slate-200 rounded">
                                    <input type="text" name="data[trauma][{{ $key }}][ped]" value="{{ $row['ped'] ?? '' }}" placeholder="Ped." class="w-full px-2 py-1 text-sm border border-slate-200 rounded">
                                </div>
                            @elseif($item['tipo'] == 'bateria_pct')
                                <div class="grid grid-cols-2 gap-2">
                                    <input type="text" name="data[trauma][{{ $key }}][bateria]" value="{{ $row['bateria'] ?? '' }}" placeholder="Batería" class="w-full px-2 py-1 text-sm border border-slate-200 rounded">
                                    <input type="text" name="data[trauma][{{ $key }}][pct]" value="{{ $row['pct'] ?? '' }}" placeholder="%" class="w-full px-2 py-1 text-sm border border-slate-200 rounded">
                                </div>
                            @elseif($item['tipo'] == 'nivelo')
                                <input type="text" name="data[trauma][{{ $key }}][nivelo]" value="{{ $row['nivelo'] ?? '' }}" placeholder="NIVEL O." class="w-full px-2 py-1 text-sm border border-slate-200 rounded">
                            @elseif($item['tipo'] == 'g2_techo')
                                <div class="grid grid-cols-2 gap-2">
                                    <input type="text" name="data[trauma][{{ $key }}][g2]" value="{{ $row['g2'] ?? '' }}" placeholder="G2" class="w-full px-2 py-1 text-sm border border-slate-200 rounded">
                                    <input type="text" name="data[trauma][{{ $key }}][techo]" value="{{ $row['techo'] ?? '' }}" placeholder="Techo" class="w-full px-2 py-1 text-sm border border-slate-200 rounded">
                                </div>
                            @elseif($item['tipo'] == 'tabla_corta')
                                <input type="text" name="data[trauma][{{ $key }}][tabla_corta]" value="{{ $row['tabla_corta'] ?? '' }}" placeholder="Tabla corta" class="w-full px-2 py-1 text-sm border border-slate-200 rounded">
                            @endif
                        </div>
                    @endforeach
                </div>
                {{-- Columna Derecha --}}
                <div class="space-y-2">
                    @foreach($traumaDerecha as $item)
                        @php($key = $item['key'])
                        @php($row = $data['trauma'][$key] ?? [])
                        <div class="rounded-lg border border-slate-200 bg-white p-2">
                            <div class="font-bold text-sm mb-2 {{ in_array($key, ['mochila_trauma', 'cajas_guantes', 'ferulas', 'tabla_scoop', 'laterales', 'pulpos', 'bolso_triage']) ? 'bg-yellow-50 p-1 rounded' : '' }}">{{ $item['label'] }}</div>
                            <input type="text" name="data[trauma][{{ $key }}][valor]" value="{{ $row['valor'] ?? '' }}" class="w-full px-2 py-1 text-sm border border-slate-200 rounded">
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- INDIQUE LA CANTIDAD --}}
    <div class="bg-white rounded-2xl border border-teal-900/20 overflow-hidden">
        <button type="button" class="w-full px-4 py-3 flex items-center justify-between bg-teal-800 border-b border-teal-900" onclick="toggleSection('secCantidades')">
            <div class="text-xs font-black uppercase tracking-widest text-white">INDIQUE LA CANTIDAD</div>
            <i class="fas fa-chevron-down text-white/80"></i>
        </button>
        <div id="secCantidades" class="p-4 hidden bg-sky-50">
            {{-- Secciones principales --}}
            @foreach($cantidadesSecciones as $key => $seccion)
                <div class="mb-4 rounded-lg border border-slate-200 bg-white p-3">
                    <div class="font-bold text-sm mb-2 {{ $seccion['titulo'] == 'MANGUERAS' ? 'bg-teal-100 text-teal-900 px-2 py-1 rounded' : '' }}">{{ $seccion['titulo'] }}</div>
                    <div class="grid grid-cols-{{ count($seccion['columnas']) }} gap-2">
                        @foreach($seccion['columnas'] as $col)
                            <div>
                                <div class="text-xs text-slate-500 mb-1">{{ $col }}</div>
                                <input type="text" name="data[cantidades][{{ $key }}][{{ Str::slug($col, '_') }}]" value="{{ $data['cantidades'][$key][Str::slug($col, '_')] ?? '' }}" class="w-full px-2 py-1 text-sm border border-slate-200 rounded">
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach

            {{-- CUÑAS --}}
            <div class="mb-4 rounded-lg border border-slate-200 bg-white p-3">
                <div class="font-bold text-sm mb-2 bg-teal-100 text-teal-900 px-2 py-1 rounded text-center">{{ $cunas['titulo'] }}</div>
                <div class="grid grid-cols-5 gap-2">
                    @foreach($cunas['columnas'] as $col)
                        <div class="text-center">
                            <div class="text-xs text-slate-500 mb-1">{{ $col }}</div>
                            <input type="text" name="data[cantidades][cunas][{{ Str::slug($col, '_') }}]" value="{{ $data['cantidades']['cunas'][Str::slug($col, '_')] ?? '' }}" class="w-full px-2 py-1 text-sm border border-slate-200 rounded">
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- CAJONERAS LATERALES --}}
            <div class="mb-4 rounded-lg border border-slate-200 bg-white p-3">
                <div class="font-bold text-sm mb-2 bg-teal-100 text-teal-900 px-2 py-1 rounded text-center">{{ $cajoneras['titulo'] }}</div>
                <div class="grid grid-cols-4 gap-2">
                    @foreach($cajoneras['columnas'] as $col)
                        <div class="text-center">
                            <div class="text-xs text-slate-500 mb-1">{{ $col }}</div>
                            <input type="text" name="data[cantidades][cajoneras][{{ Str::slug($col, '_') }}]" value="{{ $data['cantidades']['cajoneras'][Str::slug($col, '_')] ?? '' }}" class="w-full px-2 py-1 text-sm border border-slate-200 rounded">
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- ESCALAS --}}
            <div class="mb-4 rounded-lg border border-slate-200 bg-white p-3">
                <div class="font-bold text-sm mb-2 bg-teal-100 text-teal-900 px-2 py-1 rounded text-center">{{ $escalas['titulo'] }}</div>
                <div class="grid grid-cols-3 gap-2">
                    @foreach($escalas['columnas'] as $col)
                        <div class="text-center">
                            <div class="text-xs text-slate-500 mb-1">{{ $col }}</div>
                            <input type="text" name="data[cantidades][escalas][{{ Str::slug($col, '_') }}]" value="{{ $data['cantidades']['escalas'][Str::slug($col, '_')] ?? '' }}" class="w-full px-2 py-1 text-sm border border-slate-200 rounded">
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Otros items --}}
            @foreach($otrosCantidades as $key => $item)
                <div class="mb-4 rounded-lg border border-slate-200 bg-white p-3">
                    <div class="font-bold text-sm mb-2 {{ in_array($key, ['focos_led', 'carrete', 'cadenas_holmatro', 'corta_parabrisas']) ? 'bg-yellow-50 p-1 rounded' : '' }}">{{ $item['label'] }}</div>
                    <div class="grid grid-cols-{{ count($item['cols']) }} gap-2">
                        @foreach($item['cols'] as $col)
                            <div>
                                <div class="text-xs text-slate-500 mb-1">{{ $col }}</div>
                                <input type="text" name="data[cantidades][{{ $key }}][{{ Str::slug($col, '_') }}]" value="{{ $data['cantidades'][$key][Str::slug($col, '_')] ?? '' }}" class="w-full px-2 py-1 text-sm border border-slate-200 rounded">
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach

            {{-- Material del Techo --}}
            <div class="mb-4 rounded-lg border border-slate-200 bg-yellow-50 p-3 border-l-4 border-l-yellow-400">
                <div class="font-bold text-sm mb-2">{{ $materialTecho }}</div>
                <input type="text" name="data[material_techo][valor]" value="{{ $data['material_techo']['valor'] ?? '' }}" class="w-full px-2 py-1 text-sm border border-slate-200 rounded bg-white">
            </div>
        </div>
    </div>

    {{-- General Unidad --}}
    <div class="bg-white rounded-2xl border border-teal-900/20 overflow-hidden">
        <button type="button" class="w-full px-4 py-3 flex items-center justify-between bg-teal-800 border-b border-teal-900" onclick="toggleSection('secGeneral')">
            <div class="text-xs font-black uppercase tracking-widest text-white">General Unidad</div>
            <i class="fas fa-chevron-down text-white/80"></i>
        </button>
        <div id="secGeneral" class="p-4 hidden bg-sky-50">
            @foreach($generalUnidad as $idx => $item)
                <div class="mb-3 rounded-lg border border-slate-200 bg-white p-3">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <div class="font-bold text-sm mb-2">{{ $item['label'] }}</div>
                            <input type="text" name="data[general][{{ $idx }}][label]" value="{{ $data['general'][$idx]['label'] ?? '' }}" class="w-full px-2 py-1 text-sm border border-slate-200 rounded">
                        </div>
                        <div>
                            <div class="font-bold text-sm mb-2">{{ $item['sub'] }}</div>
                            <input type="text" name="data[general][{{ $idx }}][sub]" value="{{ $data['general'][$idx]['sub'] ?? '' }}" class="w-full px-2 py-1 text-sm border border-slate-200 rounded">
                        </div>
                    </div>
                </div>
            @endforeach
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
