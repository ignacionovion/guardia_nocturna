@php
    $data = $data ?? [];
    $readonly = $readonly ?? false;
@endphp

<div class="space-y-4">
    {{-- Header --}}
    <div class="bg-sky-100 rounded-xl border border-teal-900/20 p-4">
        <div class="flex justify-between items-center">
            <div class="text-xs font-black uppercase tracking-widest text-slate-900">PLANILLA DE REVISIÓN DE NIVELES</div>
            <div class="text-sm font-black text-slate-900">RX-3</div>
        </div>
    </div>

    {{-- CABINA --}}
    <div class="bg-teal-800 text-white p-2 text-xs font-black uppercase tracking-widest">CABINA</div>
    
    <div class="space-y-2">
        {{-- Bastón Tastik / ERA MSA G1 --}}
        <div class="grid grid-cols-12 gap-1 items-center bg-white p-2 rounded border border-slate-200">
            <div class="col-span-3 font-bold text-sm">Bastón Tastik</div>
            <div class="col-span-2">
                <div class="text-xs text-slate-500">Funcionamiento</div>
                @if($readonly)
                    <div class="font-semibold">{{ ($data['cabina']['baston_tastik']['funcionamiento'] ?? '') === 'si' ? 'Sí' : (($data['cabina']['baston_tastik']['funcionamiento'] ?? '') === 'no' ? 'No' : '—') }}</div>
                @else
                    <select name="data[cabina][baston_tastik][funcionamiento]" class="w-full text-sm border rounded p-1">
                        <option value=""></option>
                        <option value="si" {{ ($data['cabina']['baston_tastik']['funcionamiento'] ?? '') === 'si' ? 'selected' : '' }}>Sí</option>
                        <option value="no" {{ ($data['cabina']['baston_tastik']['funcionamiento'] ?? '') === 'no' ? 'selected' : '' }}>No</option>
                    </select>
                @endif
            </div>
            <div class="col-span-1"></div>
            <div class="col-span-3">
                <div class="text-xs text-slate-500">Linternas APASO L 3000</div>
            </div>
            <div class="col-span-2">
                <div class="text-xs text-slate-500 text-center">Nivel de aire</div>
                @if($readonly)
                    <div class="font-semibold text-center">{{ $data['cabina']['linternas_apaso']['nivel_aire'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cabina][linternas_apaso][nivel_aire]" value="{{ $data['cabina']['linternas_apaso']['nivel_aire'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </div>
        </div>

        {{-- ERA MSA G1 --}}
        <div class="grid grid-cols-12 gap-1 items-center bg-white p-2 rounded border border-slate-200">
            <div class="col-span-2 font-bold text-sm">ERA MSA G1</div>
            <div class="col-span-2">
                <div class="text-xs text-slate-500">Cantidad</div>
                @if($readonly)
                    <div class="font-semibold">{{ $data['cabina']['era_msa_g1']['cantidad'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cabina][era_msa_g1][cantidad]" value="{{ $data['cabina']['era_msa_g1']['cantidad'] ?? '' }}" class="w-full text-sm border rounded p-1">
                @endif
            </div>
            <div class="col-span-2">
                <div class="text-xs text-slate-500">Funcionamiento</div>
                @if($readonly)
                    <div class="font-semibold">{{ ($data['cabina']['era_msa_g1']['funcionamiento'] ?? '') === 'si' ? 'Sí' : (($data['cabina']['era_msa_g1']['funcionamiento'] ?? '') === 'no' ? 'No' : '—') }}</div>
                @else
                    <select name="data[cabina][era_msa_g1][funcionamiento]" class="w-full text-sm border rounded p-1">
                        <option value=""></option>
                        <option value="si" {{ ($data['cabina']['era_msa_g1']['funcionamiento'] ?? '') === 'si' ? 'selected' : '' }}>Sí</option>
                        <option value="no" {{ ($data['cabina']['era_msa_g1']['funcionamiento'] ?? '') === 'no' ? 'selected' : '' }}>No</option>
                    </select>
                @endif
            </div>
            <div class="col-span-2">
                <div class="text-xs text-slate-500 text-center">Nivel de aire</div>
                @if($readonly)
                    <div class="font-semibold text-center">{{ $data['cabina']['era_msa_g1']['nivel_aire'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cabina][era_msa_g1][nivel_aire]" value="{{ $data['cabina']['era_msa_g1']['nivel_aire'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </div>
            <div class="col-span-3"></div>
        </div>

        {{-- Chaquetillas STEX --}}
        <div class="grid grid-cols-12 gap-1 items-center bg-white p-2 rounded border border-slate-200">
            <div class="col-span-3 font-bold text-sm">Chaquetillas STEX</div>
            <div class="col-span-2">
                <div class="text-xs text-slate-500">Cantidad</div>
                @if($readonly)
                    <div class="font-semibold">{{ $data['cabina']['chaquetillas_stex']['cantidad'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cabina][chaquetillas_stex][cantidad]" value="{{ $data['cabina']['chaquetillas_stex']['cantidad'] ?? '' }}" class="w-full text-sm border rounded p-1">
                @endif
            </div>
            <div class="col-span-2"></div>
            <div class="col-span-4 font-bold text-sm">Línea de vida B&R</div>
        </div>

        {{-- Tablet RX-3 --}}
        <div class="grid grid-cols-12 gap-1 items-center bg-white p-2 rounded border border-slate-200">
            <div class="col-span-4 font-bold text-sm">Tablet unidad RX-3 y Cargador</div>
            <div class="col-span-2">
                <div class="text-xs text-slate-500">Batería</div>
                @if($readonly)
                    <div class="font-semibold">{{ ($data['cabina']['tablet_rx3']['bateria'] ?? '') === 'si' ? 'Sí' : (($data['cabina']['tablet_rx3']['bateria'] ?? '') === 'no' ? 'No' : '—') }}</div>
                @else
                    <select name="data[cabina][tablet_rx3][bateria]" class="w-full text-sm border rounded p-1">
                        <option value=""></option>
                        <option value="si" {{ ($data['cabina']['tablet_rx3']['bateria'] ?? '') === 'si' ? 'selected' : '' }}>Sí</option>
                        <option value="no" {{ ($data['cabina']['tablet_rx3']['bateria'] ?? '') === 'no' ? 'selected' : '' }}>No</option>
                    </select>
                @endif
            </div>
            <div class="col-span-2">
                <div class="text-xs text-slate-500">Radios</div>
                @if($readonly)
                    <div class="font-semibold">{{ $data['cabina']['tablet_rx3']['radios'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cabina][tablet_rx3][radios]" value="{{ $data['cabina']['tablet_rx3']['radios'] ?? '' }}" class="w-full text-sm border rounded p-1">
                @endif
            </div>
            <div class="col-span-4 font-bold text-sm">Cortacinturón</div>
        </div>
    </div>

    {{-- Check List Herramientas --}}
    <div class="bg-teal-800 text-white p-2 text-xs font-black uppercase tracking-widest text-center">Check list Herramientas</div>
    
    <div class="bg-sky-100 p-2">
        <div class="grid grid-cols-12 gap-1 text-xs font-bold text-center mb-2">
            <div class="col-span-4"></div>
            <div class="col-span-1 bg-teal-700 text-white p-1 rounded">¿Funciona?</div>
            <div class="col-span-1 bg-teal-700 text-white p-1 rounded">SÍ</div>
            <div class="col-span-1 bg-teal-700 text-white p-1 rounded">NO</div>
            <div class="col-span-5 bg-teal-700 text-white p-1 rounded">NOVEDADES</div>
        </div>
        
        @php
            $herramientas = [
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
        @endphp
        
        @foreach($herramientas as $key => $label)
            <div class="grid grid-cols-12 gap-1 items-center bg-white p-1 rounded border border-slate-200 mb-1 {{ in_array($key, ['demoledor_makita', 'combi_lukas', 'cortadora_plasma', 'dremel', 'sierra_sable', 'pistola_airgun', 'taladro_makita', 'esmeril_angular', 'sierra_circular', 'rotomartillo']) ? 'bg-yellow-50' : '' }}">
                <div class="col-span-4 font-semibold text-sm {{ in_array($key, ['demoledor_makita', 'combi_lukas', 'cortadora_plasma', 'dremel', 'sierra_sable', 'pistola_airgun', 'taladro_makita', 'esmeril_angular', 'sierra_circular', 'rotomartillo']) ? 'bg-yellow-100 p-1 rounded' : '' }}">{{ $label }}</div>
                <div class="col-span-1"></div>
                <div class="col-span-1 text-center">
                    @if($readonly)
                        {{ ($data['herramientas'][$key]['funciona'] ?? '') === 'si' ? '✓' : '' }}
                    @else
                        <input type="radio" name="data[herramientas][{{ $key }}][funciona]" value="si" {{ ($data['herramientas'][$key]['funciona'] ?? '') === 'si' ? 'checked' : '' }} class="w-4 h-4 text-teal-600">
                    @endif
                </div>
                <div class="col-span-1 text-center">
                    @if($readonly)
                        {{ ($data['herramientas'][$key]['funciona'] ?? '') === 'no' ? '✗' : '' }}
                    @else
                        <input type="radio" name="data[herramientas][{{ $key }}][funciona]" value="no" {{ ($data['herramientas'][$key]['funciona'] ?? '') === 'no' ? 'checked' : '' }} class="w-4 h-4 text-red-600">
                    @endif
                </div>
                <div class="col-span-5">
                    @if($readonly)
                        <div class="font-semibold text-sm">{{ $data['herramientas'][$key]['novedades'] ?? '—' }}</div>
                    @else
                        <input type="text" name="data[herramientas][{{ $key }}][novedades]" value="{{ $data['herramientas'][$key]['novedades'] ?? '' }}" class="w-full text-sm border rounded p-1">
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    {{-- TRAUMA --}}
    <div class="bg-teal-800 text-white p-2 text-xs font-black uppercase tracking-widest">TRAUMA</div>
    
    <div class="grid grid-cols-2 gap-2 bg-sky-100 p-2">
        {{-- Columna Izquierda --}}
        <div class="space-y-2">
            {{-- Collares cervicales --}}
            <div class="bg-white p-2 rounded border border-slate-200">
                <div class="font-bold text-sm mb-1">Collares cervicales</div>
                <div class="grid grid-cols-3 gap-1">
                    <div>
                        <div class="text-xs text-slate-500">Adulto</div>
                        @if($readonly)
                            <div class="font-semibold">{{ $data['trauma']['collares_cervicales']['adulto'] ?? '—' }}</div>
                        @else
                            <input type="text" name="data[trauma][collares_cervicales][adulto]" value="{{ $data['trauma']['collares_cervicales']['adulto'] ?? '' }}" class="w-full text-sm border rounded p-1">
                        @endif
                    </div>
                    <div>
                        <div class="text-xs text-slate-500">Ped.</div>
                        @if($readonly)
                            <div class="font-semibold">{{ $data['trauma']['collares_cervicales']['ped'] ?? '—' }}</div>
                        @else
                            <input type="text" name="data[trauma][collares_cervicales][ped]" value="{{ $data['trauma']['collares_cervicales']['ped'] ?? '' }}" class="w-full text-sm border rounded p-1">
                        @endif
                    </div>
                    <div></div>
                </div>
            </div>

            {{-- DEA --}}
            <div class="bg-white p-2 rounded border border-slate-200">
                <div class="font-bold text-sm mb-1">DEA</div>
                <div class="grid grid-cols-2 gap-1">
                    <div>
                        <div class="text-xs text-slate-500">Batería</div>
                        @if($readonly)
                            <div class="font-semibold">{{ ($data['trauma']['dea']['bateria'] ?? '') === 'si' ? 'Sí' : (($data['trauma']['dea']['bateria'] ?? '') === 'no' ? 'No' : '—') }}</div>
                        @else
                            <select name="data[trauma][dea][bateria]" class="w-full text-sm border rounded p-1">
                                <option value=""></option>
                                <option value="si" {{ ($data['trauma']['dea']['bateria'] ?? '') === 'si' ? 'selected' : '' }}>Sí</option>
                                <option value="no" {{ ($data['trauma']['dea']['bateria'] ?? '') === 'no' ? 'selected' : '' }}>No</option>
                            </select>
                        @endif
                    </div>
                    <div>
                        <div class="text-xs text-slate-500">%</div>
                        @if($readonly)
                            <div class="font-semibold">{{ $data['trauma']['dea']['pct'] ?? '—' }}</div>
                        @else
                            <input type="text" name="data[trauma][dea][pct]" value="{{ $data['trauma']['dea']['pct'] ?? '' }}" class="w-full text-sm border rounded p-1">
                        @endif
                    </div>
                </div>
            </div>

            {{-- 2 Bolsos Oxigenoterapia --}}
            <div class="bg-white p-2 rounded border border-slate-200">
                <div class="font-bold text-sm mb-1">2 Bolsos Oxigenoterapia</div>
                <div class="grid grid-cols-2 gap-1">
                    <div>
                        <div class="text-xs text-slate-500 bg-teal-100 p-1 rounded text-center">NIVEL O.</div>
                        @if($readonly)
                            <div class="font-semibold">{{ $data['trauma']['bolsos_oxigenoterapia']['nivelo1'] ?? '—' }}</div>
                        @else
                            <input type="text" name="data[trauma][bolsos_oxigenoterapia][nivelo1]" value="{{ $data['trauma']['bolsos_oxigenoterapia']['nivelo1'] ?? '' }}" class="w-full text-sm border rounded p-1">
                        @endif
                    </div>
                    <div>
                        <div class="text-xs text-slate-500 bg-teal-100 p-1 rounded text-center">NIVEL O.</div>
                        @if($readonly)
                            <div class="font-semibold">{{ $data['trauma']['bolsos_oxigenoterapia']['nivelo2'] ?? '—' }}</div>
                        @else
                            <input type="text" name="data[trauma][bolsos_oxigenoterapia][nivelo2]" value="{{ $data['trauma']['bolsos_oxigenoterapia']['nivelo2'] ?? '' }}" class="w-full text-sm border rounded p-1">
                        @endif
                    </div>
                </div>
            </div>

            {{-- Chalecos de extricación --}}
            <div class="bg-white p-2 rounded border border-slate-200">
                <div class="font-bold text-sm mb-1">Chalecos de extricación</div>
                <div class="grid grid-cols-3 gap-1">
                    <div>
                        <div class="text-xs text-slate-500 bg-teal-100 p-1 rounded text-center">Bolso TRIAGE</div>
                        @if($readonly)
                            <div class="font-semibold">{{ $data['trauma']['chalecos_extricacion']['bolso_triage'] ?? '—' }}</div>
                        @else
                            <input type="text" name="data[trauma][chalecos_extricacion][bolso_triage]" value="{{ $data['trauma']['chalecos_extricacion']['bolso_triage'] ?? '' }}" class="w-full text-sm border rounded p-1">
                        @endif
                    </div>
                    <div>
                        <div class="text-xs text-slate-500 bg-teal-100 p-1 rounded text-center">Tabla corta</div>
                        @if($readonly)
                            <div class="font-semibold">{{ $data['trauma']['chalecos_extricacion']['tabla_corta'] ?? '—' }}</div>
                        @else
                            <input type="text" name="data[trauma][chalecos_extricacion][tabla_corta]" value="{{ $data['trauma']['chalecos_extricacion']['tabla_corta'] ?? '' }}" class="w-full text-sm border rounded p-1">
                        @endif
                    </div>
                    <div></div>
                </div>
            </div>

            {{-- Maleta Primeros Auxilios Quemados --}}
            <div class="bg-white p-2 rounded border border-slate-200">
                <div class="font-bold text-sm mb-1">Maleta Primeros Auxilios Quemados</div>
                @if($readonly)
                    <div class="font-semibold">{{ $data['trauma']['maleta_quemados'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[trauma][maleta_quemados]" value="{{ $data['trauma']['maleta_quemados'] ?? '' }}" class="w-full text-sm border rounded p-1">
                @endif
            </div>
        </div>

        {{-- Columna Derecha --}}
        <div class="space-y-2">
            <div class="bg-white p-2 rounded border border-slate-200">
                <div class="font-bold text-sm mb-1">Mochila Trauma</div>
                @if($readonly)
                    <div class="font-semibold">{{ $data['trauma']['mochila_trauma'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[trauma][mochila_trauma]" value="{{ $data['trauma']['mochila_trauma'] ?? '' }}" class="w-full text-sm border rounded p-1">
                @endif
            </div>

            <div class="bg-white p-2 rounded border border-slate-200">
                <div class="font-bold text-sm mb-1">Cajas de guantes</div>
                @if($readonly)
                    <div class="font-semibold">{{ $data['trauma']['cajas_guantes'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[trauma][cajas_guantes]" value="{{ $data['trauma']['cajas_guantes'] ?? '' }}" class="w-full text-sm border rounded p-1">
                @endif
            </div>

            <div class="bg-white p-2 rounded border border-slate-200">
                <div class="font-bold text-sm mb-1">Férulas</div>
                @if($readonly)
                    <div class="font-semibold">{{ $data['trauma']['ferulas'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[trauma][ferulas]" value="{{ $data['trauma']['ferulas'] ?? '' }}" class="w-full text-sm border rounded p-1">
                @endif
            </div>

            <div class="bg-white p-2 rounded border border-slate-200">
                <div class="font-bold text-sm mb-1">Tablas Largas</div>
                @if($readonly)
                    <div class="font-semibold">{{ $data['trauma']['tablas_largas'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[trauma][tablas_largas]" value="{{ $data['trauma']['tablas_largas'] ?? '' }}" class="w-full text-sm border rounded p-1">
                @endif
            </div>

            <div class="bg-white p-2 rounded border border-slate-200">
                <div class="grid grid-cols-2 gap-1">
                    <div>
                        <div class="font-bold text-sm mb-1">Laterales</div>
                        @if($readonly)
                            <div class="font-semibold">{{ $data['trauma']['laterales'] ?? '—' }}</div>
                        @else
                            <input type="text" name="data[trauma][laterales]" value="{{ $data['trauma']['laterales'] ?? '' }}" class="w-full text-sm border rounded p-1">
                        @endif
                    </div>
                    <div>
                        <div class="font-bold text-sm mb-1">Pulpos</div>
                        @if($readonly)
                            <div class="font-semibold">{{ $data['trauma']['pulpos'] ?? '—' }}</div>
                        @else
                            <input type="text" name="data[trauma][pulpos]" value="{{ $data['trauma']['pulpos'] ?? '' }}" class="w-full text-sm border rounded p-1">
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Indique la cantidad --}}
    <div class="bg-teal-800 text-white p-2 text-xs font-black uppercase tracking-widest text-center">Indique la cantidad</div>
    
    <div class="space-y-2 bg-sky-100 p-2">
        {{-- Cilindros para cojines --}}
        <div class="grid grid-cols-3 gap-1 bg-white p-2 rounded border border-slate-200">
            <div class="font-bold text-sm">Cilindros para cojines de levante</div>
            <div>
                <div class="text-xs text-slate-500 text-center font-bold bg-teal-100 p-1 rounded">Cojines Paratech</div>
                @if($readonly)
                    <div class="font-semibold text-center">{{ $data['cantidades']['cojines_paratech'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][cojines_paratech]" value="{{ $data['cantidades']['cojines_paratech'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </div>
            <div>
                <div class="text-xs text-slate-500 text-center font-bold bg-teal-100 p-1 rounded">Tirfor de Rescate</div>
                @if($readonly)
                    <div class="font-semibold text-center">{{ $data['cantidades']['tirfor_rescate'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][tirfor_rescate]" value="{{ $data['cantidades']['tirfor_rescate'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </div>
        </div>

        {{-- Focos de 1000W --}}
        <div class="grid grid-cols-3 gap-1 bg-white p-2 rounded border border-slate-200">
            <div class="font-bold text-sm">Focos de 1000W y trípode</div>
            <div>
                <div class="text-xs text-slate-500 text-center font-bold bg-teal-100 p-1 rounded">Caja de Herramientas</div>
                @if($readonly)
                    <div class="font-semibold text-center">{{ $data['cantidades']['caja_herramientas'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][caja_herramientas]" value="{{ $data['cantidades']['caja_herramientas'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </div>
            <div>
                <div class="text-xs text-slate-500 text-center font-bold bg-teal-100 p-1 rounded">Cubre Airbag</div>
                @if($readonly)
                    <div class="font-semibold text-center">{{ $data['cantidades']['cubre_airbag'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][cubre_airbag]" value="{{ $data['cantidades']['cubre_airbag'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </div>
        </div>

        {{-- CUÑAS --}}
        <div class="bg-teal-700 text-white p-1 text-xs font-bold text-center">CUÑAS</div>
        <div class="grid grid-cols-5 gap-1 bg-white p-2 rounded border border-slate-200">
            <div>
                <div class="text-xs text-slate-500 text-center font-bold bg-teal-100 p-1 rounded">Biseladas</div>
                @if($readonly)
                    <div class="font-semibold text-center">{{ $data['cantidades']['cunas_biseladas'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][cunas_biseladas]" value="{{ $data['cantidades']['cunas_biseladas'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </div>
            <div>
                <div class="text-xs text-slate-500 text-center font-bold bg-teal-100 p-1 rounded">Bloques</div>
                @if($readonly)
                    <div class="font-semibold text-center">{{ $data['cantidades']['cunas_bloques'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][cunas_bloques]" value="{{ $data['cantidades']['cunas_bloques'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </div>
            <div>
                <div class="text-xs text-slate-500 text-center font-bold bg-teal-100 p-1 rounded">Escalonadas</div>
                @if($readonly)
                    <div class="font-semibold text-center">{{ $data['cantidades']['cunas_escalonadas'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][cunas_escalonadas]" value="{{ $data['cantidades']['cunas_escalonadas'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </div>
            <div>
                <div class="text-xs text-slate-500 text-center font-bold bg-teal-100 p-1 rounded">Planas</div>
                @if($readonly)
                    <div class="font-semibold text-center">{{ $data['cantidades']['cunas_plan'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][cunas_plan]" value="{{ $data['cantidades']['cunas_plan'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </div>
            <div>
                <div class="text-xs text-slate-500 text-center font-bold bg-teal-100 p-1 rounded">Combos de 2 libras</div>
                @if($readonly)
                    <div class="font-semibold text-center">{{ $data['cantidades']['cunas_combos_2l'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][cunas_combos_2l]" value="{{ $data['cantidades']['cunas_combos_2l'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </div>
        </div>

        {{-- Set lona cubre pilares --}}
        <div class="grid grid-cols-3 gap-1 bg-white p-2 rounded border border-slate-200">
            <div class="font-bold text-sm">Set lona cubre pilares</div>
            <div>
                <div class="text-xs text-slate-500 text-center font-bold bg-teal-100 p-1 rounded">Force</div>
                @if($readonly)
                    <div class="font-semibold text-center">{{ $data['cantidades']['lona_force'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][lona_force]" value="{{ $data['cantidades']['lona_force'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </div>
            <div>
                <div class="text-xs text-slate-500 text-center font-bold bg-teal-100 p-1 rounded">Combo 8 libras</div>
                @if($readonly)
                    <div class="font-semibold text-center">{{ $data['cantidades']['combo_8l'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][combo_8l]" value="{{ $data['cantidades']['combo_8l'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </div>
        </div>

        {{-- Eslings Naranjas --}}
        <div class="grid grid-cols-3 gap-1 bg-cyan-50 p-2 rounded border border-slate-200">
            <div class="font-bold text-sm">Eslings Naranjas</div>
            <div>
                <div class="text-xs text-slate-500 text-center font-bold bg-teal-100 p-1 rounded">Barretilla</div>
                @if($readonly)
                    <div class="font-semibold text-center">{{ $data['cantidades']['eslings_naranjas_barretilla'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][eslings_naranjas_barretilla]" value="{{ $data['cantidades']['eslings_naranjas_barretilla'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </div>
            <div>
                <div class="text-xs text-slate-500 text-center font-bold bg-teal-100 p-1 rounded">Halligan</div>
                @if($readonly)
                    <div class="font-semibold text-center">{{ $data['cantidades']['eslings_naranjas_halligan'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][eslings_naranjas_halligan]" value="{{ $data['cantidades']['eslings_naranjas_halligan'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </div>
        </div>

        {{-- Eslings Azules --}}
        <div class="grid grid-cols-3 gap-1 bg-cyan-50 p-2 rounded border border-slate-200">
            <div class="font-bold text-sm">Eslings Azules</div>
            <div>
                <div class="text-xs text-slate-500 text-center font-bold bg-teal-100 p-1 rounded">Napoleón 24"</div>
                @if($readonly)
                    <div class="font-semibold text-center">{{ $data['cantidades']['eslings_azules_napoleon'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][eslings_azules_napoleon]" value="{{ $data['cantidades']['eslings_azules_napoleon'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </div>
            <div>
                <div class="text-xs text-slate-500 text-center font-bold bg-teal-100 p-1 rounded">TNT</div>
                @if($readonly)
                    <div class="font-semibold text-center">{{ $data['cantidades']['eslings_azules_tnt'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][eslings_azules_tnt]" value="{{ $data['cantidades']['eslings_azules_tnt'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </div>
        </div>

        {{-- Eslings Ojo a ojo --}}
        <div class="grid grid-cols-3 gap-1 bg-cyan-50 p-2 rounded border border-slate-200">
            <div class="font-bold text-sm">Eslings Ojo a ojo</div>
            <div>
                <div class="text-xs text-slate-500 text-center font-bold bg-teal-100 p-1 rounded">Hacha bombero</div>
                @if($readonly)
                    <div class="font-semibold text-center">{{ $data['cantidades']['eslings_ojo_hacha'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][eslings_ojo_hacha]" value="{{ $data['cantidades']['eslings_ojo_hacha'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </div>
            <div>
                <div class="text-xs text-slate-500 text-center font-bold bg-teal-100 p-1 rounded">Estacas de fierro</div>
                @if($readonly)
                    <div class="font-semibold text-center">{{ $data['cantidades']['eslings_ojo_estacas'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][eslings_ojo_estacas]" value="{{ $data['cantidades']['eslings_ojo_estacas'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </div>
        </div>

        {{-- Cadenas WEBER --}}
        <div class="grid grid-cols-3 gap-1 bg-cyan-50 p-2 rounded border border-slate-200">
            <div class="font-bold text-sm">Cadenas WEBER</div>
            <div>
                <div class="text-xs text-slate-500 text-center font-bold bg-teal-100 p-1 rounded">Soporte RAM WEBER</div>
                @if($readonly)
                    <div class="font-semibold text-center">{{ $data['cantidades']['cadenas_soporte_weber'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][cadenas_soporte_weber]" value="{{ $data['cantidades']['cadenas_soporte_weber'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </div>
            <div>
                <div class="text-xs text-slate-500 text-center font-bold bg-teal-100 p-1 rounded">Muela RAM Lukas</div>
                @if($readonly)
                    <div class="font-semibold text-center">{{ $data['cantidades']['cadenas_muela_lukas'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][cadenas_muela_lukas]" value="{{ $data['cantidades']['cadenas_muela_lukas'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </div>
        </div>

        {{-- Estabilizadores PARATECH --}}
        <div class="grid grid-cols-3 gap-1 bg-orange-50 p-2 rounded border border-slate-200">
            <div class="font-bold text-sm">Estabilizadores PARATECH</div>
            <div>
                <div class="text-xs text-slate-500 text-center font-bold bg-teal-100 p-1 rounded">Extensiones</div>
                @if($readonly)
                    <div class="font-semibold text-center">{{ $data['cantidades']['paratech_extensiones'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][paratech_extensiones]" value="{{ $data['cantidades']['paratech_extensiones'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </div>
            <div>
                <div class="text-xs text-slate-500 text-center font-bold bg-teal-100 p-1 rounded">Bases</div>
                @if($readonly)
                    <div class="font-semibold text-center">{{ $data['cantidades']['paratech_bases'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][paratech_bases]" value="{{ $data['cantidades']['paratech_bases'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </div>
        </div>

        {{-- Puntas --}}
        <div class="grid grid-cols-3 gap-1 bg-orange-50 p-2 rounded border border-slate-200">
            <div class="font-bold text-sm">Puntas</div>
            <div>
                <div class="text-xs text-slate-500 text-center font-bold bg-teal-100 p-1 rounded">Llaveros</div>
                @if($readonly)
                    <div class="font-semibold text-center">{{ $data['cantidades']['puntas_llaveros'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][puntas_llaveros]" value="{{ $data['cantidades']['puntas_llaveros'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </div>
            <div>
                <div class="text-xs text-slate-500 text-center font-bold bg-teal-100 p-1 rounded">Eslings</div>
                @if($readonly)
                    <div class="font-semibold text-center">{{ $data['cantidades']['puntas_eslings'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][puntas_eslings]" value="{{ $data['cantidades']['puntas_eslings'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </div>
        </div>

        {{-- Plataforma de Rescate --}}
        <div class="grid grid-cols-3 gap-1 bg-white p-2 rounded border border-slate-200">
            <div class="font-bold text-sm">Plataforma de Rescate</div>
            <div>
                <div class="text-xs text-slate-500 text-center font-bold bg-teal-100 p-1 rounded">Escalas</div>
                @if($readonly)
                    <div class="font-semibold text-center">{{ $data['cantidades']['plataforma_escalas'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][plataforma_escalas]" value="{{ $data['cantidades']['plataforma_escalas'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </div>
            <div>
                <div class="text-xs text-slate-500 text-center font-bold bg-teal-100 p-1 rounded">Conos</div>
                @if($readonly)
                    <div class="font-semibold text-center">{{ $data['cantidades']['plataforma_conos'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][plataforma_conos]" value="{{ $data['cantidades']['plataforma_conos'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </div>
        </div>

        {{-- Colchón Vetter --}}
        <div class="grid grid-cols-3 gap-1 bg-white p-2 rounded border border-slate-200">
            <div class="font-bold text-sm">Colchón Vetter baja presión</div>
            <div>
                <div class="text-xs text-slate-500 text-center font-bold bg-teal-100 p-1 rounded">Set apertura puertas</div>
                @if($readonly)
                    <div class="font-semibold text-center">{{ $data['cantidades']['vetter_apertura_puertas'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][vetter_apertura_puertas]" value="{{ $data['cantidades']['vetter_apertura_puertas'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </div>
            <div>
                <div class="text-xs text-slate-500 text-center font-bold bg-teal-100 p-1 rounded">Palas</div>
                @if($readonly)
                    <div class="font-semibold text-center">{{ $data['cantidades']['vetter_palas'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][vetter_palas]" value="{{ $data['cantidades']['vetter_palas'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </div>
        </div>

        {{-- Paquete Circular --}}
        <div class="grid grid-cols-3 gap-1 bg-white p-2 rounded border border-slate-200">
            <div class="font-bold text-sm">Paquete Circular</div>
            <div>
                <div class="text-xs text-slate-500 text-center font-bold bg-teal-100 p-1 rounded">Llave de grifo</div>
                @if($readonly)
                    <div class="font-semibold text-center">{{ $data['cantidades']['paquete_llave_grifo'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][paquete_llave_grifo]" value="{{ $data['cantidades']['paquete_llave_grifo'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </div>
            <div>
                <div class="text-xs text-slate-500 text-center font-bold bg-teal-100 p-1 rounded">Traspaso de grifo</div>
                @if($readonly)
                    <div class="font-semibold text-center">{{ $data['cantidades']['paquete_traspaso_grifo'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][paquete_traspaso_grifo]" value="{{ $data['cantidades']['paquete_traspaso_grifo'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </div>
        </div>

        {{-- Manguera de alimentación --}}
        <div class="grid grid-cols-3 gap-1 bg-white p-2 rounded border border-slate-200">
            <div class="font-bold text-sm">Manguera de alimentación</div>
            <div>
                <div class="text-xs text-slate-500 text-center font-bold bg-teal-100 p-1 rounded">Traspasos</div>
                @if($readonly)
                    <div class="font-semibold text-center">{{ $data['cantidades']['manguera_traspasos'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][manguera_traspasos]" value="{{ $data['cantidades']['manguera_traspasos'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </div>
            <div>
                <div class="text-xs text-slate-500 text-center font-bold bg-teal-100 p-1 rounded">Llaves de copla</div>
                @if($readonly)
                    <div class="font-semibold text-center">{{ $data['cantidades']['manguera_llaves_copla'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][manguera_llaves_copla]" value="{{ $data['cantidades']['manguera_llaves_copla'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </div>
        </div>
    </div>

    {{-- Maleta Sistema Paratech --}}
    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-3 rounded">
        <div class="font-bold text-sm">Maleta Sistema Paratech</div>
        <div class="text-xs text-slate-600 italic">(mando dual, regulador de presión, mangueras de 10m, válvulas de retención)</div>
    </div>

    {{-- Material de cuerdas --}}
    <div class="bg-green-100 border-l-4 border-green-400 p-3 rounded">
        <div class="font-bold text-sm">Material de cuerdas</div>
        <div class="text-xs text-slate-600 italic">(Jumar, mosquetones, poleas simples y dobles, ascensores Buddy, freno microtraction, plato multiplicador de anclaje, descendedor en 8)</div>
    </div>

    {{-- General Unidad --}}
    <div class="bg-teal-800 text-white p-2 text-xs font-black uppercase tracking-widest">General Unidad</div>
    
    <div class="space-y-2 bg-sky-100 p-2">
        <div class="grid grid-cols-2 gap-2 bg-white p-2 rounded border border-slate-200">
            <div>
                <div class="text-xs font-bold">Alarmas Sonoras y Visuales</div>
                @if($readonly)
                    <div class="font-semibold">{{ ($data['general']['alarmas'] ?? '') === 'si' ? 'Sí' : (($data['general']['alarmas'] ?? '') === 'no' ? 'No' : '—') }}</div>
                @else
                    <select name="data[general][alarmas]" class="w-full text-sm border rounded p-1">
                        <option value=""></option>
                        <option value="si" {{ ($data['general']['alarmas'] ?? '') === 'si' ? 'selected' : '' }}>Sí</option>
                        <option value="no" {{ ($data['general']['alarmas'] ?? '') === 'no' ? 'selected' : '' }}>No</option>
                    </select>
                @endif
            </div>
            <div>
                <div class="text-xs font-bold">Aseo Interior Cabina Aseo Gavetas y Herramientas</div>
                @if($readonly)
                    <div class="font-semibold">{{ ($data['general']['aseo'] ?? '') === 'si' ? 'Sí' : (($data['general']['aseo'] ?? '') === 'no' ? 'No' : '—') }}</div>
                @else
                    <select name="data[general][aseo]" class="w-full text-sm border rounded p-1">
                        <option value=""></option>
                        <option value="si" {{ ($data['general']['aseo'] ?? '') === 'si' ? 'selected' : '' }}>Sí</option>
                        <option value="no" {{ ($data['general']['aseo'] ?? '') === 'no' ? 'selected' : '' }}>No</option>
                    </select>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-2 gap-2 bg-white p-2 rounded border border-slate-200">
            <div>
                <div class="text-xs font-bold">Nivel Combustible del Bidón</div>
                @if($readonly)
                    <div class="font-semibold">{{ $data['general']['nivel_combustible_bidon'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[general][nivel_combustible_bidon]" value="{{ $data['general']['nivel_combustible_bidon'] ?? '' }}" class="w-full text-sm border rounded p-1">
                @endif
            </div>
            <div>
                <div class="text-xs font-bold">Nivel Aceite para mezcla (Jeringa)</div>
                @if($readonly)
                    <div class="font-semibold">{{ $data['general']['nivel_aceite_mezcla'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[general][nivel_aceite_mezcla]" value="{{ $data['general']['nivel_aceite_mezcla'] ?? '' }}" class="w-full text-sm border rounded p-1">
                @endif
            </div>
        </div>

        <div class="grid grid-cols-2 gap-2 bg-white p-2 rounded border border-slate-200">
            <div>
                <div class="text-xs font-bold">Nivel Combustible de la Unidad</div>
                @if($readonly)
                    <div class="font-semibold">{{ $data['general']['nivel_combustible_unidad'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[general][nivel_combustible_unidad]" value="{{ $data['general']['nivel_combustible_unidad'] ?? '' }}" class="w-full text-sm border rounded p-1">
                @endif
            </div>
            <div>
                <div class="text-xs font-bold">Nivel de agua de la unidad</div>
                @if($readonly)
                    <div class="font-semibold">{{ $data['general']['nivel_agua_unidad'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[general][nivel_agua_unidad]" value="{{ $data['general']['nivel_agua_unidad'] ?? '' }}" class="w-full text-sm border rounded p-1">
                @endif
            </div>
        </div>

        <div class="bg-white p-2 rounded border border-slate-200">
            <div class="text-xs font-bold">Nivel de aceite para cadena</div>
            @if($readonly)
                <div class="font-semibold">{{ $data['general']['nivel_aceite_cadena'] ?? '—' }}</div>
            @else
                <input type="text" name="data[general][nivel_aceite_cadena]" value="{{ $data['general']['nivel_aceite_cadena'] ?? '' }}" class="w-full text-sm border rounded p-1">
            @endif
        </div>
    </div>
</div>
