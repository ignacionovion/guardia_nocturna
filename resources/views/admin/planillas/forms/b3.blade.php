@php
    $data = $data ?? [];

    // CABINA - primera fila con estructura compleja
    $cabinaPrincipal = [
        'era_msa_g1' => [
            'label' => 'ERA MSA G 1',
            'tipo' => 'numeros_1_7_obac_radios',
            'numeros' => ['1', '2', '3', '4', '5', '6', '7'],
            'extra' => ['OBAC (M7)', 'Radios', '1', '2', 'Motorola']
        ],
        'linterna_nightstick' => [
            'label' => 'Linterna NIGHTSTICK XPR-5568',
            'tipo' => 'funcionamiento_cantidad_linea'
        ],
        'tablet_cargador' => [
            'label' => 'Tablet unidad B-3 y Cargador',
            'tipo' => 'maleta_sci'
        ],
    ];

    // Check list Herramientas
    $herramientasChecklist = [
        'camara_termal' => 'Cámara Termal',
        'baston_tastik' => 'Bastón Tastik',
        'detector_gas' => 'Detector de Gas TIF8900',
        'motosierra_cutter' => 'Motosierra "CUTTER EDGE"',
        'taladro_makita' => 'Taladro Inalámbrico MAKITA',
        'motobomba_rosenbauer' => 'Motobomba Rosenbauer',
        'aspirador_nautilus' => 'Aspirador NAUTILUS 8/1',
        'motoamoladora_m14' => 'Motoamoladora M14 MAKITA',
        'motosierra_stihl' => 'MOTOSIERRA STIHL',
        'motor_electrogeno' => 'Motor electrógeno RS 14',
        'focos_led' => 'Focos LED 100 watt',
        'foco_inalambrico' => 'Foco inalámbrico Makita & trípode',
        'winche_b3' => 'Winche unidad B-3',
        'ventilador_rosenbauer' => 'Ventilador Rosenbauer',
    ];

    // Indique la cantidad - estructura compleja de la foto
    $cantidadesSecciones = [
        'mochila_trauma' => [
            'titulo' => 'Mochila de Trauma y Cilindro O2',
            'columnas' => ['NIVEL O.', 'Kit Inmovilización completo', 'Conos']
        ],
        'mangueras' => [
            'titulo' => 'MANGUERAS',
            'columnas' => ['52', '75', 'L D H', 'Armada Base']
        ],
        'paquete_circular' => [
            'titulo' => 'Paquete Circular',
            'columnas' => ['Herraduras', 'Carretes alimentación (cantidad tiras)']
        ],
        'ataques' => [
            'titulo' => 'ATAQUES',
            'columnas' => ['52', '75', 'Cilindros de recambio MSA']
        ],
        'traspasos' => [
            'titulo' => 'TRASPASOS',
            'columnas' => ['Llave de grifo', 'Traspaso de grifo']
        ],
        'llaves_copla' => [
            'titulo' => 'LLAVES DE COPLA',
            'columnas' => ['Llave de piso', 'Manguera 75 alimentación']
        ],
        'escalas_techo' => [
            'titulo' => 'Escalas de Techo',
            'columnas' => ['Puntas Taladro', 'Napoleón 30"']
        ],
        'combo_8l' => [
            'titulo' => 'Combo de 8 libras',
            'columnas' => ['Bidón Motosierra', 'Hacha bombero']
        ],
        'bicheros' => [
            'titulo' => 'Bicheros',
            'columnas' => ['Barretilla', 'Halligan']
        ],
        'caja_herramientas' => [
            'titulo' => 'Caja de Herramientas',
            'columnas' => ['Hacha suela', 'TNT']
        ],
        'gremio' => [
            'titulo' => 'Gremio',
            'columnas' => ['Kit Entrada Forzada', 'Trifurca']
        ],
        'filtro_aspiracion' => [
            'titulo' => 'Filtro de Aspiración',
            'columnas' => ['Siamesa', 'Pitones de 50']
        ],
        'kit_quemados' => [
            'titulo' => 'Kit quemados / Asistente de Trauma',
            'columnas' => ['Pitón DIN 50', 'Pitones de 70']
        ],
        'bidon_bencina' => [
            'titulo' => 'Bidón de bencina Verde 10L',
            'columnas' => ['Chorizos', 'Pasatiras']
        ],
        'extintor_agua' => [
            'titulo' => 'Extintor de agua',
            'columnas' => ['Carretes de extensión', 'Pitón de espuma']
        ],
    ];

    // Material Forestal
    $materialForestal = 'Material Forestal (1 pala, 1 rozón, 2 rastrillos cegadores, 1 McLeod, 2 bombas de espalda)';

    // Bolso de altura
    $bolsoAltura = 'Bolso de altura (Tira de 38mm 3 metros, Pitón POK, gemelo, reducción, cuerda utilitaria)';

    // General Unidad
    $generalUnidad = [
        ['label' => 'Alarmas Sonoras y Visuales', 'sub' => 'Aseo Interior Cabina Aseo Gavetas y Herramientas'],
        ['label' => 'Nivel Combustible Bidón (Naranjo)', 'sub' => 'Nivel Aceite para mezcla (Jeringa)'],
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
            {{-- ERA MSA G 1 con columnas 1-7 --}}
            <div class="mb-4 rounded-xl border border-slate-200 bg-white p-3">
                <div class="font-bold text-sm mb-3">ERA MSA G 1</div>
                <div class="grid grid-cols-11 gap-1 text-center text-xs">
                    @foreach(['1', '2', '3', '4', '5', '6', '7', 'OBAC (M7)', 'Radios', '1', '2'] as $col)
                        <div class="bg-teal-100 py-1 rounded font-semibold">{{ $col }}</div>
                    @endforeach
                </div>
                <div class="grid grid-cols-11 gap-1 mt-2">
                    @foreach(['1', '2', '3', '4', '5', '6', '7', 'obac_m7', 'radios', 'radio_1', 'radio_2'] as $field)
                        <input type="text" name="data[cabina][era_msa_g1][{{ $field }}]" value="{{ $data['cabina']['era_msa_g1'][$field] ?? '' }}" class="w-full px-1 py-1 text-sm border border-slate-200 rounded text-center">
                    @endforeach
                </div>
                <div class="text-right text-xs mt-1 text-slate-500">Motorola</div>
            </div>

            {{-- Linterna NIGHTSTICK --}}
            <div class="mb-4 rounded-xl border border-slate-200 bg-white p-3">
                <div class="grid grid-cols-12 gap-2 items-center">
                    <div class="col-span-4 font-bold text-sm">Linterna NIGHTSTICK XPR-5568</div>
                    <div class="col-span-3">
                        <div class="text-xs text-slate-500 mb-1">Funcionamiento</div>
                        <select name="data[cabina][linterna][funcionamiento]" class="w-full px-2 py-1 text-sm border border-slate-200 rounded">
                            <option value=""></option>
                            <option value="si" {{ ($data['cabina']['linterna']['funcionamiento'] ?? '') === 'si' ? 'selected' : '' }}>Sí</option>
                            <option value="no" {{ ($data['cabina']['linterna']['funcionamiento'] ?? '') === 'no' ? 'selected' : '' }}>No</option>
                        </select>
                    </div>
                    <div class="col-span-2">
                        <div class="text-xs text-slate-500 mb-1">Cantidad:</div>
                        <input type="text" name="data[cabina][linterna][cantidad]" value="{{ $data['cabina']['linterna']['cantidad'] ?? '' }}" class="w-full px-2 py-1 text-sm border border-slate-200 rounded">
                    </div>
                    <div class="col-span-3">
                        <div class="text-xs text-slate-500 mb-1">Línea de vida B & R</div>
                        <input type="text" name="data[cabina][linterna][linea_vida]" value="{{ $data['cabina']['linterna']['linea_vida'] ?? '' }}" class="w-full px-2 py-1 text-sm border border-slate-200 rounded">
                    </div>
                </div>
            </div>

            {{-- Tablet y Cargador / Maleta SCI --}}
            <div class="mb-4 rounded-xl border border-slate-200 bg-white p-3">
                <div class="grid grid-cols-2 gap-4">
                    <div class="font-bold text-sm">Tablet unidad B-3 y Cargador</div>
                    <div class="font-bold text-sm">Maleta SCI</div>
                </div>
            </div>

            {{-- Check list Herramientas --}}
            <div class="rounded-xl border border-teal-900/20 bg-sky-100 px-4 py-2 mb-4">
                <div class="text-xs font-black uppercase tracking-widest text-slate-900">Check list Herramientas</div>
            </div>
            
            <div class="grid grid-cols-12 gap-2 mb-2 text-center text-xs font-bold">
                <div class="col-span-6"></div>
                <div class="col-span-2 bg-teal-700 text-white py-1 rounded">Sí</div>
                <div class="col-span-2 bg-teal-700 text-white py-1 rounded">NO</div>
                <div class="col-span-2 bg-teal-700 text-white py-1 rounded">Novedades</div>
            </div>
            
            <div class="space-y-1">
                @foreach($herramientasChecklist as $key => $label)
                    @php($row = $data['herramientas'][$key] ?? [])
                    @php($yellowBg = in_array($key, ['detector_gas', 'motosierra_cutter', 'taladro_makita', 'motobomba_rosenbauer', 'aspirador_nautilus', 'motoamoladora_m14']) ? 'bg-yellow-50' : '')
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

    {{-- INDIQUE LA CANTIDAD --}}
    <div class="bg-white rounded-2xl border border-teal-900/20 overflow-hidden">
        <div class="w-full px-4 py-3 bg-teal-800 border-b border-teal-900">
            <div class="text-xs font-black uppercase tracking-widest text-white">INDIQUE LA CANTIDAD</div>
        </div>
        <div class="p-4 bg-sky-50">
            @foreach($cantidadesSecciones as $key => $seccion)
                <div class="mb-3 rounded-lg border border-slate-200 bg-white p-2">
                    <div class="font-bold text-sm mb-2 {{ $seccion['titulo'] == 'MANGUERAS' ? 'bg-teal-100 px-2 py-1 rounded' : '' }}">{{ $seccion['titulo'] }}</div>
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

            {{-- Material Forestal --}}
            <div class="mb-3 rounded-lg border border-slate-200 bg-yellow-50 p-2 border-l-4 border-l-yellow-400">
                <div class="font-bold text-sm">{{ $materialForestal }}</div>
            </div>

            {{-- Bolso de altura --}}
            <div class="mb-3 rounded-lg border border-slate-200 bg-yellow-50 p-2 border-l-4 border-l-yellow-400">
                <div class="font-bold text-sm">{{ $bolsoAltura }}</div>
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
