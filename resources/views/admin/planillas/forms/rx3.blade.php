@php
    $data = $data ?? [];

    // CABINA items - primera fila
    $cabinaPrincipal = [
        'baston_tastik' => ['label' => 'Bastón Tastik', 'tipo' => 'funcionamiento'],
        'era_msa_g1' => ['label' => 'ERA MSA G1', 'tipo' => 'cantidad_funcionamiento_aire'],
        'chaquetillas_stex' => ['label' => 'Chaquetillas STEX', 'tipo' => 'cantidad'],
        'tablet_cargador' => ['label' => 'Tablet unidad RX-3 y Cargador', 'tipo' => 'bateria_radios'],
        'linternas_apaso' => ['label' => 'Linternas APASO L3000', 'tipo' => 'funcionamiento_linea'],
    ];

    // Check list Herramientas
    $herramientasChecklist = [
        'demoledor_makita' => 'Demoledor MAKITA c/ accesorios',
        'combi_lukas' => 'Combi Lukas E-Draulik GMBH',
        'cortadora_plasma' => 'Cortadora de Plasma',
        'dremel' => 'DREMEL c/ accesorios',
        'sierra_sable' => 'Sierra sable inalámbrica MAKITA',
        'pistola_airgun' => 'Pistola neumática AIRGUN',
        'taladro_makita' => 'Taladro Inalámbrico MAKITA',
        'esmeril_angular' => 'Esmeril angular 125 mm',
        'sierra_circular' => 'Sierra circular 7 1/4"',
        'rotomartillo' => 'Rotomartillo DEWALT',
        'control_vetter' => 'Control VETTER baja presión',
        'grupo_electrogeno' => 'Grupo electrógeno 30 KVA',
        'motor_weber' => 'Motor a Combustión 4T WEBER',
        'cizalla_weber' => 'Cizalla, expansor y RAM WEBER',
        'cizalla_lukas' => 'Cizalla, expansor y RAM LUKAS',
        'winche_rx3' => 'Winche de la unidad RX-3',
    ];

    // TRAUMA - dos columnas como en la foto
    $traumaIzquierda = [
        ['key' => 'collares_cervicales', 'label' => 'Collares cervicales', 'tipo' => 'adulto_ped_pct'],
        ['key' => 'dea', 'label' => 'DEA', 'tipo' => 'bateria_pct'],
        ['key' => 'bolsos_oxigeno', 'label' => '2 Bolsos Oxigenoterapia', 'tipo' => 'nivelo_doble'],
        ['key' => 'chalecos_extricacion', 'label' => 'Chalecos de extricación', 'tipo' => 'bolso_triage'],
        ['key' => 'maleta_quemados', 'label' => 'Maleta Primeros Auxilios Quemados', 'tipo' => 'tabla_corta'],
    ];

    $traumaDerecha = [
        ['key' => 'mochila_trauma', 'label' => 'Mochila Trauma', 'tipo' => 'simple'],
        ['key' => 'cajas_guantes', 'label' => 'Cajas de guantes', 'tipo' => 'simple'],
        ['key' => 'ferulas', 'label' => 'Férulas', 'tipo' => 'simple'],
        ['key' => 'tablas_largas', 'label' => 'Tablas Largas', 'tipo' => 'simple'],
        ['key' => 'laterales', 'label' => 'Laterales', 'tipo' => 'simple'],
        ['key' => 'pulpos', 'label' => 'Pulpos', 'tipo' => 'simple'],
    ];

    // Indique la cantidad - estructura de la foto
    $cantidadesItems = [
        ['label' => 'Cilindros para cojines de levante', 'sub' => 'Cojines Paratech', 'extra' => 'Tirfor de Rescate'],
        ['label' => 'Focos de 1000W y trípode', 'sub' => 'Caja de Herramientas', 'extra' => 'Cubre Airbag'],
    ];

    // CUÑAS
    $cunas = [
        'titulo' => 'CUÑAS',
        'columnas' => ['Biseladas', 'Bloques', 'Escalonadas', 'Planas', 'Combos de 2 libras'],
    ];

    // Items con 3 columnas
    $tresColumnas = [
        ['label' => 'Set lona cubre pilares', 'cols' => ['Force', 'Combo 8 libras']],
        ['label' => 'Eslings Naranjas', 'cols' => ['Barretilla', 'Halligan']],
        ['label' => 'Eslings Azules', 'cols' => ['Napoleón 24"', 'TNT']],
        ['label' => 'Eslings Ojo a ojo', 'cols' => ['Hacha bombero', 'Estacas de fierro']],
        ['label' => 'Cadenas WEBER', 'cols' => ['Soporte RAM WEBER', 'Muela RAM Lukas']],
        ['label' => 'Estabilizadores PARATECH', 'cols' => ['Extensiones', 'Bases']],
        ['label' => 'Puntas', 'cols' => ['Llaveros', 'Eslings']],
        ['label' => 'Plataforma de Rescate', 'cols' => ['Escalas', 'Conos']],
        ['label' => 'Colchón Vetter baja presión', 'cols' => ['Set apertura puertas', 'Palas']],
        ['label' => 'Paquete Circular', 'cols' => ['Llave de grifo', 'Traspaso de grifo']],
        ['label' => 'Manguera de alimentación', 'cols' => ['Traspasos', 'Llaves de copla']],
    ];

    // Maleta Sistema Paratech
    $maletaParatech = 'Maleta Sistema Paratech (mando dual, regulador de presión, mangueras de 10m, válvulas de retención)';

    // Material de cuerdas
    $materialCuerdas = 'Material de cuerdas (Jumar, mosquetones, poleas simples y dobles, ascensores Buddy, freno microtraction, plato multiplicador de anclaje, descendedor en 8)';

    // General Unidad
    $generalUnidad = [
        ['label' => 'Alarmas Sonoras y Visuales', 'sub' => 'Aseo Interior Cabina Aseo Gavetas y Herramientas'],
        ['label' => 'Nivel Combustible del Bidón', 'sub' => 'Nivel Aceite para mezcla (Jeringa)'],
        ['label' => 'Nivel Combustible de la Unidad', 'sub' => 'Nivel de agua de la unidad'],
        ['label' => 'Nivel de aceite para cadena', 'sub' => ''],
    ];
@endphp

<div class="space-y-4">

    {{-- CABINA --}}
    <div class="bg-white rounded-2xl border border-teal-900/20 overflow-hidden">
        <div class="w-full px-4 py-3 bg-teal-800 border-b border-teal-900">
            <div class="text-xs font-black uppercase tracking-widest text-white">CABINA</div>
        </div>
        <div class="p-4 bg-sky-50">
            {{-- Items principales --}}
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
                        @elseif($item['tipo'] == 'cantidad_funcionamiento_aire')
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
                        @elseif($item['tipo'] == 'cantidad')
                            <div class="col-span-3">
                                <input type="text" name="data[cabina][{{ $key }}][cantidad]" value="{{ $row['cantidad'] ?? '' }}" placeholder="Cantidad" class="w-full px-3 py-2 border border-slate-200 rounded-lg bg-white font-semibold text-sm">
                            </div>
                        @elseif($item['tipo'] == 'bateria_radios')
                            <div class="col-span-2">
                                <input type="text" name="data[cabina][{{ $key }}][bateria]" value="{{ $row['bateria'] ?? '' }}" placeholder="Batería" class="w-full px-3 py-2 border border-slate-200 rounded-lg bg-white font-semibold text-sm">
                            </div>
                            <div class="col-span-2">
                                <input type="text" name="data[cabina][{{ $key }}][radios]" value="{{ $row['radios'] ?? '' }}" placeholder="Radios" class="w-full px-3 py-2 border border-slate-200 rounded-lg bg-white font-semibold text-sm">
                            </div>
                            <div class="col-span-3">
                                <input type="text" name="data[cabina][{{ $key }}][linea]" value="{{ $row['linea'] ?? '' }}" placeholder="Línea de vida B&R" class="w-full px-3 py-2 border border-slate-200 rounded-lg bg-white font-semibold text-sm">
                            </div>
                        @elseif($item['tipo'] == 'funcionamiento_linea')
                            <div class="col-span-3">
                                <select name="data[cabina][{{ $key }}][funcionamiento]" class="w-full px-3 py-2 border border-slate-200 rounded-lg bg-white font-semibold text-sm">
                                    <option value="">Funcionamiento</option>
                                    <option value="si" {{ ($row['funcionamiento'] ?? '') === 'si' ? 'selected' : '' }}>Sí</option>
                                    <option value="no" {{ ($row['funcionamiento'] ?? '') === 'no' ? 'selected' : '' }}>No</option>
                                </select>
                            </div>
                            <div class="col-span-4">
                                <input type="text" name="data[cabina][{{ $key }}][linea]" value="{{ $row['linea'] ?? '' }}" placeholder="Línea de vida B&R" class="w-full px-3 py-2 border border-slate-200 rounded-lg bg-white font-semibold text-sm">
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            {{-- Check list Herramientas --}}
            <div class="rounded-xl border border-teal-900/20 bg-sky-100 px-4 py-2 mb-4">
                <div class="text-xs font-black uppercase tracking-widest text-slate-900">Check list Herramientas</div>
            </div>
            
            <div class="grid grid-cols-12 gap-2 mb-2 text-center text-xs font-bold text-slate-700">
                <div class="col-span-6"></div>
                <div class="col-span-2 bg-teal-700 text-white py-1 rounded">Sí</div>
                <div class="col-span-2 bg-teal-700 text-white py-1 rounded">NO</div>
                <div class="col-span-2 bg-teal-700 text-white py-1 rounded">Novedades</div>
            </div>
            
            <div class="space-y-1">
                @foreach($herramientasChecklist as $key => $label)
                    @php($row = $data['herramientas'][$key] ?? [])
                    @php($yellowBg = in_array($key, ['demoledor_makita', 'combi_lukas', 'cortadora_plasma', 'dremel', 'sierra_sable', 'pistola_airgun', 'taladro_makita', 'esmeril_angular']) ? 'bg-yellow-50' : '')
                    <div class="grid grid-cols-12 gap-2 items-center bg-white rounded border border-slate-200 {{ $yellowBg }}">
                        <div class="col-span-6 px-3 py-2 text-sm font-semibold {{ $yellowBg }}">{{ $label }}</div>
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
        <div class="w-full px-4 py-3 bg-teal-800 border-b border-teal-900">
            <div class="text-xs font-black uppercase tracking-widest text-white">TRAUMA</div>
        </div>
        <div class="p-4 bg-sky-50">
            <div class="grid grid-cols-2 gap-4">
                {{-- Columna Izquierda --}}
                <div class="space-y-2">
                    @foreach($traumaIzquierda as $item)
                        @php($key = $item['key'])
                        @php($row = $data['trauma'][$key] ?? [])
                        <div class="rounded-lg border border-slate-200 bg-white p-2">
                            <div class="font-bold text-sm mb-2">{{ $item['label'] }}</div>
                            @if($item['tipo'] == 'adulto_ped_pct')
                                <div class="grid grid-cols-3 gap-2">
                                    <input type="text" name="data[trauma][{{ $key }}][adulto]" value="{{ $row['adulto'] ?? '' }}" placeholder="Adulto" class="w-full px-2 py-1 text-sm border border-slate-200 rounded">
                                    <input type="text" name="data[trauma][{{ $key }}][ped]" value="{{ $row['ped'] ?? '' }}" placeholder="Ped." class="w-full px-2 py-1 text-sm border border-slate-200 rounded">
                                    <input type="text" name="data[trauma][{{ $key }}][pct]" value="{{ $row['pct'] ?? '' }}" placeholder="%" class="w-full px-2 py-1 text-sm border border-slate-200 rounded">
                                </div>
                            @elseif($item['tipo'] == 'bateria_pct')
                                <div class="grid grid-cols-2 gap-2">
                                    <input type="text" name="data[trauma][{{ $key }}][bateria]" value="{{ $row['bateria'] ?? '' }}" placeholder="Batería" class="w-full px-2 py-1 text-sm border border-slate-200 rounded">
                                    <input type="text" name="data[trauma][{{ $key }}][pct]" value="{{ $row['pct'] ?? '' }}" placeholder="%" class="w-full px-2 py-1 text-sm border border-slate-200 rounded">
                                </div>
                            @elseif($item['tipo'] == 'nivelo_doble')
                                <div class="grid grid-cols-2 gap-2">
                                    <input type="text" name="data[trauma][{{ $key }}][nivelo1]" value="{{ $row['nivelo1'] ?? '' }}" placeholder="NIVEL O." class="w-full px-2 py-1 text-sm border border-slate-200 rounded">
                                    <input type="text" name="data[trauma][{{ $key }}][nivelo2]" value="{{ $row['nivelo2'] ?? '' }}" placeholder="NIVEL O." class="w-full px-2 py-1 text-sm border border-slate-200 rounded">
                                </div>
                            @elseif($item['tipo'] == 'bolso_triage')
                                <div class="grid grid-cols-2 gap-2">
                                    <input type="text" name="data[trauma][{{ $key }}][bolso]" value="{{ $row['bolso'] ?? '' }}" placeholder="Bolso TRIAGE" class="w-full px-2 py-1 text-sm border border-slate-200 rounded">
                                    <input type="text" name="data[trauma][{{ $key }}][tablas]" value="{{ $row['tablas'] ?? '' }}" placeholder="Tablas Largas" class="w-full px-2 py-1 text-sm border border-slate-200 rounded">
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
                            <div class="font-bold text-sm mb-2">{{ $item['label'] }}</div>
                            <input type="text" name="data[trauma][{{ $key }}][valor]" value="{{ $row['valor'] ?? '' }}" class="w-full px-2 py-1 text-sm border border-slate-200 rounded">
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- INDIQUE LA CANTIDAD --}}
    <div class="bg-white rounded-2xl border border-teal-900/20 overflow-hidden">
        <div class="w-full px-4 py-3 bg-teal-800 border-b border-teal-900">
            <div class="text-xs font-black uppercase tracking-widest text-white">INDIQUE LA CANTIDAD</div>
        </div>
        <div class="p-4 bg-sky-50">
            {{-- Items con 3 columnas especiales --}}
            @foreach($cantidadesItems as $idx => $item)
                <div class="mb-3 rounded-lg border border-slate-200 bg-white p-3">
                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <div class="font-bold text-sm mb-2">{{ $item['label'] }}</div>
                            <input type="text" name="data[cantidades][item_{{ $idx }}][valor]" value="{{ $data['cantidades']['item_'.$idx]['valor'] ?? '' }}" class="w-full px-2 py-1 text-sm border border-slate-200 rounded">
                        </div>
                        <div>
                            <div class="font-bold text-sm mb-2">{{ $item['sub'] }}</div>
                            <input type="text" name="data[cantidades][item_{{ $idx }}][sub]" value="{{ $data['cantidades']['item_'.$idx]['sub'] ?? '' }}" class="w-full px-2 py-1 text-sm border border-slate-200 rounded">
                        </div>
                        <div>
                            <div class="font-bold text-sm mb-2">{{ $item['extra'] }}</div>
                            <input type="text" name="data[cantidades][item_{{ $idx }}][extra]" value="{{ $data['cantidades']['item_'.$idx]['extra'] ?? '' }}" class="w-full px-2 py-1 text-sm border border-slate-200 rounded">
                        </div>
                    </div>
                </div>
            @endforeach

            {{-- CUÑAS --}}
            <div class="mb-3 rounded-lg border border-slate-200 bg-white p-3">
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

            {{-- Items con 2 columnas --}}
            @foreach($tresColumnas as $item)
                <div class="mb-3 rounded-lg border border-slate-200 bg-white p-3 {{ in_array($item['label'], ['Eslings Naranjas', 'Eslings Azules', 'Eslings Ojo a ojo']) ? 'bg-cyan-50' : '' }}">
                    <div class="grid grid-cols-3 gap-4">
                        <div class="font-bold text-sm">{{ $item['label'] }}</div>
                        <div>
                            <div class="text-xs text-slate-500 mb-1">{{ $item['cols'][0] }}</div>
                            <input type="text" name="data[cantidades][{{ Str::slug($item['label'], '_') }}][col1]" value="{{ $data['cantidades'][Str::slug($item['label'], '_')]['col1'] ?? '' }}" class="w-full px-2 py-1 text-sm border border-slate-200 rounded">
                        </div>
                        <div>
                            <div class="text-xs text-slate-500 mb-1">{{ $item['cols'][1] }}</div>
                            <input type="text" name="data[cantidades][{{ Str::slug($item['label'], '_') }}][col2]" value="{{ $data['cantidades'][Str::slug($item['label'], '_')]['col2'] ?? '' }}" class="w-full px-2 py-1 text-sm border border-slate-200 rounded">
                        </div>
                    </div>
                </div>
            @endforeach

            {{-- Maleta Sistema Paratech --}}
            <div class="mb-3 rounded-lg border border-slate-200 bg-yellow-50 p-2 border-l-4 border-l-yellow-400">
                <div class="font-bold text-sm">{{ $maletaParatech }}</div>
            </div>

            {{-- Material de cuerdas --}}
            <div class="mb-3 rounded-lg border border-slate-200 bg-green-100 p-2">
                <div class="font-bold text-sm">{{ $materialCuerdas }}</div>
            </div>
        </div>
    </div>

    {{-- General Unidad --}}
    <div class="bg-white rounded-2xl border border-teal-900/20 overflow-hidden">
        <div class="w-full px-4 py-3 bg-teal-800 border-b border-teal-900">
            <div class="text-xs font-black uppercase tracking-widest text-white">General Unidad</div>
        </div>
        <div class="p-4 bg-sky-50">
            @foreach($generalUnidad as $idx => $item)
                <div class="mb-3 rounded-lg border border-slate-200 bg-white p-3">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <div class="font-bold text-sm mb-2">{{ $item['label'] }}</div>
                            <input type="text" name="data[general][{{ $idx }}][label_valor]" value="{{ $data['general'][$idx]['label_valor'] ?? '' }}" class="w-full px-2 py-1 text-sm border border-slate-200 rounded">
                        </div>
                        @if($item['sub'])
                            <div>
                                <div class="font-bold text-sm mb-2">{{ $item['sub'] }}</div>
                                <input type="text" name="data[general][{{ $idx }}][sub_valor]" value="{{ $data['general'][$idx]['sub_valor'] ?? '' }}" class="w-full px-2 py-1 text-sm border border-slate-200 rounded">
                            </div>
                        @endif
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
