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
    <div class="overflow-x-auto">
        <table class="w-full border-collapse text-sm" style="min-width: 800px;">
            <tr class="bg-teal-800 text-white">
                <th colspan="12" class="p-2 text-xs font-black uppercase tracking-widest text-left border border-teal-900">CABINA</th>
            </tr>

            {{-- Bastón Tastik / Linternas APASO --}}
            <tr class="bg-sky-100">
                <td colspan="3" class="p-1 border border-slate-300 font-bold text-sm">Bastón Tastik</td>
                <td colspan="2" class="p-1 border border-slate-300">
                    <div class="text-xs">Funcionamiento</div>
                    @if($readonly)
                        <div class="font-semibold">{{ ($data['cabina']['baston_tastik']['funcionamiento'] ?? '') === 'si' ? 'Sí' : (($data['cabina']['baston_tastik']['funcionamiento'] ?? '') === 'no' ? 'No' : '—') }}</div>
                    @else
                        <select name="data[cabina][baston_tastik][funcionamiento]" class="w-full text-sm border rounded p-1">
                            <option value=""></option>
                            <option value="si" {{ ($data['cabina']['baston_tastik']['funcionamiento'] ?? '') === 'si' ? 'selected' : '' }}>Sí</option>
                            <option value="no" {{ ($data['cabina']['baston_tastik']['funcionamiento'] ?? '') === 'no' ? 'selected' : '' }}>No</option>
                        </select>
                    @endif
                </td>
                <td colspan="1" class="p-1 border border-slate-300 bg-sky-100"></td>
                <td colspan="3" class="p-1 border border-slate-300 font-bold text-sm">Linternas APASO L 3000</td>
                <td colspan="3" class="p-1 border border-slate-300">
                    <div class="text-xs">Nivel de aire</div>
                    @if($readonly)
                        <div class="font-semibold">{{ $data['cabina']['linternas_apaso']['nivel_aire'] ?? '—' }}</div>
                    @else
                        <input type="text" name="data[cabina][linternas_apaso][nivel_aire]" value="{{ $data['cabina']['linternas_apaso']['nivel_aire'] ?? '' }}" class="w-full text-sm border rounded p-1">
                    @endif
                </td>
            </tr>

            {{-- ERA MSA G1 --}}
            <tr class="bg-sky-100">
                <td colspan="3" class="p-1 border border-slate-300 font-bold text-sm">ERA MSA G1</td>
                <td colspan="2" class="p-1 border border-slate-300">
                    <div class="text-xs">Cantidad</div>
                    @if($readonly)
                        <div class="font-semibold">{{ $data['cabina']['era_msa_g1']['cantidad'] ?? '—' }}</div>
                    @else
                        <input type="text" name="data[cabina][era_msa_g1][cantidad]" value="{{ $data['cabina']['era_msa_g1']['cantidad'] ?? '' }}" class="w-full text-sm border rounded p-1">
                    @endif
                </td>
                <td colspan="1" class="p-1 border border-slate-300 bg-sky-100"></td>
                <td colspan="2" class="p-1 border border-slate-300">
                    <div class="text-xs">Funcionamiento</div>
                    @if($readonly)
                        <div class="font-semibold">{{ ($data['cabina']['era_msa_g1']['funcionamiento'] ?? '') === 'si' ? 'Sí' : (($data['cabina']['era_msa_g1']['funcionamiento'] ?? '') === 'no' ? 'No' : '—') }}</div>
                    @else
                        <select name="data[cabina][era_msa_g1][funcionamiento]" class="w-full text-sm border rounded p-1">
                            <option value=""></option>
                            <option value="si" {{ ($data['cabina']['era_msa_g1']['funcionamiento'] ?? '') === 'si' ? 'selected' : '' }}>Sí</option>
                            <option value="no" {{ ($data['cabina']['era_msa_g1']['funcionamiento'] ?? '') === 'no' ? 'selected' : '' }}>No</option>
                        </select>
                    @endif
                </td>
                <td colspan="1" class="p-1 border border-slate-300 bg-sky-100"></td>
                <td colspan="3" class="p-1 border border-slate-300">
                    <div class="text-xs">Nivel de aire</div>
                    @if($readonly)
                        <div class="font-semibold">{{ $data['cabina']['era_msa_g1']['nivel_aire'] ?? '—' }}</div>
                    @else
                        <input type="text" name="data[cabina][era_msa_g1][nivel_aire]" value="{{ $data['cabina']['era_msa_g1']['nivel_aire'] ?? '' }}" class="w-full text-sm border rounded p-1">
                    @endif
                </td>
            </tr>

            {{-- Chaquetillas STEX / Línea de vida --}}
            <tr class="bg-sky-100">
                <td colspan="3" class="p-1 border border-slate-300 font-bold text-sm">Chaquetillas STEX</td>
                <td colspan="2" class="p-1 border border-slate-300">
                    <div class="text-xs">Cantidad</div>
                    @if($readonly)
                        <div class="font-semibold">{{ $data['cabina']['chaquetillas_stex']['cantidad'] ?? '—' }}</div>
                    @else
                        <input type="text" name="data[cabina][chaquetillas_stex][cantidad]" value="{{ $data['cabina']['chaquetillas_stex']['cantidad'] ?? '' }}" class="w-full text-sm border rounded p-1">
                    @endif
                </td>
                <td colspan="1" class="p-1 border border-slate-300 bg-sky-100"></td>
                <td colspan="6" class="p-1 border border-slate-300 font-bold text-sm">Línea de vida B&R</td>
            </tr>

            {{-- Tablet RX-3 --}}
            <tr class="bg-sky-100">
                <td colspan="3" class="p-1 border border-slate-300 font-bold text-sm">Tablet unidad RX-3 y Cargador</td>
                <td colspan="2" class="p-1 border border-slate-300">
                    <div class="text-xs">Batería</div>
                    @if($readonly)
                        <div class="font-semibold">{{ ($data['cabina']['tablet_rx3']['bateria'] ?? '') === 'si' ? 'Sí' : (($data['cabina']['tablet_rx3']['bateria'] ?? '') === 'no' ? 'No' : '—') }}</div>
                    @else
                        <select name="data[cabina][tablet_rx3][bateria]" class="w-full text-sm border rounded p-1">
                            <option value=""></option>
                            <option value="si" {{ ($data['cabina']['tablet_rx3']['bateria'] ?? '') === 'si' ? 'selected' : '' }}>Sí</option>
                            <option value="no" {{ ($data['cabina']['tablet_rx3']['bateria'] ?? '') === 'no' ? 'selected' : '' }}>No</option>
                        </select>
                    @endif
                </td>
                <td colspan="1" class="p-1 border border-slate-300 bg-sky-100"></td>
                <td colspan="2" class="p-1 border border-slate-300">
                    <div class="text-xs">Radios</div>
                    @if($readonly)
                        <div class="font-semibold">{{ $data['cabina']['tablet_rx3']['radios'] ?? '—' }}</div>
                    @else
                        <input type="text" name="data[cabina][tablet_rx3][radios]" value="{{ $data['cabina']['tablet_rx3']['radios'] ?? '' }}" class="w-full text-sm border rounded p-1">
                    @endif
                </td>
                <td colspan="1" class="p-1 border border-slate-300 bg-sky-100"></td>
                <td colspan="3" class="p-1 border border-slate-300 font-bold text-sm">Cortacinturón</td>
            </tr>
        </table>
    </div>

    {{-- Check List Herramientas --}}
    <div class="overflow-x-auto">
        <table class="w-full border-collapse text-sm mt-2" style="min-width: 600px;">
            <tr class="bg-teal-800 text-white">
                <th colspan="12" class="p-2 text-xs font-black uppercase tracking-widest text-center border border-teal-900">Check list Herramientas</th>
            </tr>
            <tr class="bg-teal-700 text-white">
                <th colspan="5" class="p-1 text-xs font-bold text-left border border-teal-800">Herramienta</th>
                <th colspan="2" class="p-1 text-xs font-bold text-center border border-teal-800">¿Funciona?</th>
                <th colspan="5" class="p-1 text-xs font-bold text-center border border-teal-800">Novedades</th>
            </tr>

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
            $highlightTools = ['demoledor_makita', 'combi_lukas', 'cortadora_plasma', 'dremel', 'sierra_sable', 'pistola_airgun', 'taladro_makita', 'esmeril_angular', 'sierra_circular', 'rotomartillo'];
        @endphp

        @foreach($herramientas as $key => $label)
            <tr class="{{ in_array($key, $highlightTools) ? 'bg-yellow-50' : 'bg-sky-50' }}">
                <td colspan="5" class="p-1 border border-slate-300 font-semibold {{ in_array($key, $highlightTools) ? 'bg-yellow-100' : '' }}">{{ $label }}</td>
                <td colspan="2" class="p-1 border border-slate-300 text-center">
                    @if($readonly)
                        @php $v = $data['herramientas'][$key]['funciona'] ?? ''; @endphp
                        <span class="font-semibold {{ $v === 'si' ? 'text-teal-700' : ($v === 'no' ? 'text-red-600' : 'text-slate-400') }}">
                            {{ $v === 'si' ? 'SÍ' : ($v === 'no' ? 'NO' : '—') }}
                        </span>
                    @else
                        <select name="data[herramientas][{{ $key }}][funciona]" class="w-full text-sm border rounded p-1">
                            <option value="">—</option>
                            <option value="si" {{ ($data['herramientas'][$key]['funciona'] ?? '') === 'si' ? 'selected' : '' }}>SÍ</option>
                            <option value="no" {{ ($data['herramientas'][$key]['funciona'] ?? '') === 'no' ? 'selected' : '' }}>NO</option>
                        </select>
                    @endif
                </td>
                <td colspan="5" class="p-1 border border-slate-300">
                    @if($readonly)
                        <div class="font-semibold text-sm">{{ $data['herramientas'][$key]['novedades'] ?? '—' }}</div>
                    @else
                        <input type="text" name="data[herramientas][{{ $key }}][novedades]" value="{{ $data['herramientas'][$key]['novedades'] ?? '' }}" class="w-full text-sm border rounded p-1">
                    @endif
                </td>
            </tr>
        @endforeach
        </table>
    </div>

    {{-- TRAUMA --}}
    <div class="overflow-x-auto">
        <table class="w-full border-collapse text-sm mt-2" style="min-width: 800px;">
            <tr class="bg-teal-800 text-white">
                <th colspan="12" class="p-2 text-xs font-black uppercase tracking-widest text-left border border-teal-900">TRAUMA</th>
            </tr>

            {{-- Collares cervicales --}}
            <tr class="bg-sky-100">
                <td colspan="3" class="p-1 border border-slate-300 font-bold text-sm">Collares cervicales</td>
                <td colspan="2" class="p-1 border border-slate-300">
                    <div class="text-xs">Adulto</div>
                    @if($readonly)
                        <div class="font-semibold">{{ $data['trauma']['collares_cervicales']['adulto'] ?? '—' }}</div>
                    @else
                        <input type="text" name="data[trauma][collares_cervicales][adulto]" value="{{ $data['trauma']['collares_cervicales']['adulto'] ?? '' }}" class="w-full text-sm border rounded p-1">
                    @endif
                </td>
                <td colspan="2" class="p-1 border border-slate-300">
                    <div class="text-xs">Ped.</div>
                    @if($readonly)
                        <div class="font-semibold">{{ $data['trauma']['collares_cervicales']['ped'] ?? '—' }}</div>
                    @else
                        <input type="text" name="data[trauma][collares_cervicales][ped]" value="{{ $data['trauma']['collares_cervicales']['ped'] ?? '' }}" class="w-full text-sm border rounded p-1">
                    @endif
                </td>
                <td colspan="5" class="p-1 border border-slate-300 bg-sky-100"></td>
            </tr>

            {{-- DEA --}}
            <tr class="bg-sky-100">
                <td colspan="3" class="p-1 border border-slate-300 font-bold text-sm">DEA</td>
                <td colspan="2" class="p-1 border border-slate-300">
                    <div class="text-xs">Batería</div>
                    @if($readonly)
                        <div class="font-semibold">{{ ($data['trauma']['dea']['bateria'] ?? '') === 'si' ? 'Sí' : (($data['trauma']['dea']['bateria'] ?? '') === 'no' ? 'No' : '—') }}</div>
                    @else
                        <select name="data[trauma][dea][bateria]" class="w-full text-sm border rounded p-1">
                            <option value=""></option>
                            <option value="si" {{ ($data['trauma']['dea']['bateria'] ?? '') === 'si' ? 'selected' : '' }}>Sí</option>
                            <option value="no" {{ ($data['trauma']['dea']['bateria'] ?? '') === 'no' ? 'selected' : '' }}>No</option>
                        </select>
                    @endif
                </td>
                <td colspan="2" class="p-1 border border-slate-300">
                    <div class="text-xs">%</div>
                    @if($readonly)
                        <div class="font-semibold">{{ $data['trauma']['dea']['pct'] ?? '—' }}</div>
                    @else
                        <input type="text" name="data[trauma][dea][pct]" value="{{ $data['trauma']['dea']['pct'] ?? '' }}" class="w-full text-sm border rounded p-1">
                    @endif
                </td>
                <td colspan="2" class="p-1 border border-slate-300 font-bold text-sm">Mochila Trauma</td>
                <td colspan="3" class="p-1 border border-slate-300">
                    @if($readonly)
                        <div class="font-semibold">{{ $data['trauma']['mochila_trauma'] ?? '—' }}</div>
                    @else
                        <input type="text" name="data[trauma][mochila_trauma]" value="{{ $data['trauma']['mochila_trauma'] ?? '' }}" class="w-full text-sm border rounded p-1">
                    @endif
                </td>
            </tr>

            {{-- 2 Bolsos Oxigenoterapia --}}
            <tr class="bg-sky-100">
                <td colspan="3" class="p-1 border border-slate-300 font-bold text-sm">2 Bolsos Oxigenoterapia</td>
                <td colspan="2" class="p-1 border border-slate-300 bg-teal-100">
                    <div class="text-xs font-bold text-center">NIVEL O.</div>
                    @if($readonly)
                        <div class="font-semibold text-center">{{ $data['trauma']['bolsos_oxigenoterapia']['nivelo1'] ?? '—' }}</div>
                    @else
                        <input type="text" name="data[trauma][bolsos_oxigenoterapia][nivelo1]" value="{{ $data['trauma']['bolsos_oxigenoterapia']['nivelo1'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                    @endif
                </td>
                <td colspan="2" class="p-1 border border-slate-300 bg-teal-100">
                    <div class="text-xs font-bold text-center">NIVEL O.</div>
                    @if($readonly)
                        <div class="font-semibold text-center">{{ $data['trauma']['bolsos_oxigenoterapia']['nivelo2'] ?? '—' }}</div>
                    @else
                        <input type="text" name="data[trauma][bolsos_oxigenoterapia][nivelo2]" value="{{ $data['trauma']['bolsos_oxigenoterapia']['nivelo2'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                    @endif
                </td>
                <td colspan="2" class="p-1 border border-slate-300 font-bold text-sm">Cajas de guantes</td>
                <td colspan="3" class="p-1 border border-slate-300">
                    @if($readonly)
                        <div class="font-semibold">{{ $data['trauma']['cajas_guantes'] ?? '—' }}</div>
                    @else
                        <input type="text" name="data[trauma][cajas_guantes]" value="{{ $data['trauma']['cajas_guantes'] ?? '' }}" class="w-full text-sm border rounded p-1">
                    @endif
                </td>
            </tr>

            {{-- Chalecos de extricación --}}
            <tr class="bg-sky-100">
                <td colspan="3" class="p-1 border border-slate-300 font-bold text-sm">Chalecos de extricación</td>
                <td colspan="2" class="p-1 border border-slate-300 bg-teal-100">
                    <div class="text-xs font-bold text-center">Bolso TRIAGE</div>
                    @if($readonly)
                        <div class="font-semibold text-center">{{ $data['trauma']['chalecos_extricacion']['bolso_triage'] ?? '—' }}</div>
                    @else
                        <input type="text" name="data[trauma][chalecos_extricacion][bolso_triage]" value="{{ $data['trauma']['chalecos_extricacion']['bolso_triage'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                    @endif
                </td>
                <td colspan="2" class="p-1 border border-slate-300 bg-teal-100">
                    <div class="text-xs font-bold text-center">Tabla corta</div>
                    @if($readonly)
                        <div class="font-semibold text-center">{{ $data['trauma']['chalecos_extricacion']['tabla_corta'] ?? '—' }}</div>
                    @else
                        <input type="text" name="data[trauma][chalecos_extricacion][tabla_corta]" value="{{ $data['trauma']['chalecos_extricacion']['tabla_corta'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                    @endif
                </td>
                <td colspan="2" class="p-1 border border-slate-300 font-bold text-sm">Férulas</td>
                <td colspan="3" class="p-1 border border-slate-300 bg-sky-100"></td>
            </tr>

            {{-- Maleta Primeros Auxilios Quemados --}}
            <tr class="bg-sky-100">
                <td colspan="3" class="p-1 border border-slate-300 font-bold text-sm">Maleta Primeros Auxilios Quemados</td>
                <td colspan="3" class="p-1 border border-slate-300">
                    @if($readonly)
                        <div class="font-semibold">{{ $data['trauma']['maleta_quemados'] ?? '—' }}</div>
                    @else
                        <input type="text" name="data[trauma][maleta_quemados]" value="{{ $data['trauma']['maleta_quemados'] ?? '' }}" class="w-full text-sm border rounded p-1">
                    @endif
                </td>
                <td colspan="2" class="p-1 border border-slate-300 font-bold text-sm">Tablas Largas</td>
                <td colspan="4" class="p-1 border border-slate-300">
                    @if($readonly)
                        <div class="font-semibold">{{ $data['trauma']['tablas_largas'] ?? '—' }}</div>
                    @else
                        <input type="text" name="data[trauma][tablas_largas]" value="{{ $data['trauma']['tablas_largas'] ?? '' }}" class="w-full text-sm border rounded p-1">
                    @endif
                </td>
            </tr>

            {{-- Laterales y Pulpos --}}
            <tr class="bg-sky-100">
                <td colspan="6" class="p-1 border border-slate-300 bg-sky-100"></td>
                <td colspan="2" class="p-1 border border-slate-300 font-bold text-sm">Laterales</td>
                <td colspan="4" class="p-1 border border-slate-300">
                    @if($readonly)
                        <div class="font-semibold">{{ $data['trauma']['laterales'] ?? '—' }}</div>
                    @else
                        <input type="text" name="data[trauma][laterales]" value="{{ $data['trauma']['laterales'] ?? '' }}" class="w-full text-sm border rounded p-1">
                    @endif
                </td>
            </tr>
            <tr class="bg-sky-100">
                <td colspan="6" class="p-1 border border-slate-300 bg-sky-100"></td>
                <td colspan="2" class="p-1 border border-slate-300 font-bold text-sm">Pulpos</td>
                <td colspan="4" class="p-1 border border-slate-300">
                    @if($readonly)
                        <div class="font-semibold">{{ $data['trauma']['pulpos'] ?? '—' }}</div>
                    @else
                        <input type="text" name="data[trauma][pulpos]" value="{{ $data['trauma']['pulpos'] ?? '' }}" class="w-full text-sm border rounded p-1">
                    @endif
                </td>
            </tr>
        </table>
    </div>

    {{-- Indique la cantidad --}}
    <div class="overflow-x-auto">
        <table class="w-full border-collapse text-sm mt-2" style="min-width: 800px;">
            <tr class="bg-teal-800 text-white">
                <th colspan="12" class="p-2 text-xs font-black uppercase tracking-widest text-center border border-teal-900">Indique la cantidad</th>
            </tr>

            {{-- Cilindros para cojines --}}
            <tr class="bg-sky-100">
                <td colspan="4" class="p-1 border border-slate-300 font-bold text-sm">Cilindros para cojines de levante</td>
                <td colspan="4" class="p-1 border border-slate-300 bg-teal-100 text-center"><div class="text-xs font-bold">Cojines Paratech</div></td>
                <td colspan="4" class="p-1 border border-slate-300 bg-teal-100 text-center"><div class="text-xs font-bold">Tirfor de Rescate</div></td>
            </tr>
            <tr class="bg-sky-100">
                <td colspan="4" class="p-1 border border-slate-300 bg-sky-100"></td>
                <td colspan="4" class="p-1 border border-slate-300 text-center">
                    @if($readonly)
                        <div class="font-semibold">{{ $data['cantidades']['cojines_paratech'] ?? '—' }}</div>
                    @else
                        <input type="text" name="data[cantidades][cojines_paratech]" value="{{ $data['cantidades']['cojines_paratech'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                    @endif
                </td>
                <td colspan="4" class="p-1 border border-slate-300 text-center">
                    @if($readonly)
                        <div class="font-semibold">{{ $data['cantidades']['tirfor_rescate'] ?? '—' }}</div>
                    @else
                        <input type="text" name="data[cantidades][tirfor_rescate]" value="{{ $data['cantidades']['tirfor_rescate'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                    @endif
                </td>
            </tr>

            {{-- Focos de 1000W --}}
            <tr class="bg-sky-100">
                <td colspan="4" class="p-1 border border-slate-300 font-bold text-sm">Focos de 1000W y trípode</td>
                <td colspan="4" class="p-1 border border-slate-300 bg-teal-100 text-center"><div class="text-xs font-bold">Caja de Herramientas</div></td>
                <td colspan="4" class="p-1 border border-slate-300 bg-teal-100 text-center"><div class="text-xs font-bold">Cubre Airbag</div></td>
            </tr>
            <tr class="bg-sky-100">
                <td colspan="4" class="p-1 border border-slate-300 bg-sky-100"></td>
                <td colspan="4" class="p-1 border border-slate-300 text-center">
                    @if($readonly)
                        <div class="font-semibold">{{ $data['cantidades']['caja_herramientas'] ?? '—' }}</div>
                    @else
                        <input type="text" name="data[cantidades][caja_herramientas]" value="{{ $data['cantidades']['caja_herramientas'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                    @endif
                </td>
                <td colspan="4" class="p-1 border border-slate-300 text-center">
                    @if($readonly)
                        <div class="font-semibold">{{ $data['cantidades']['cubre_airbag'] ?? '—' }}</div>
                    @else
                        <input type="text" name="data[cantidades][cubre_airbag]" value="{{ $data['cantidades']['cubre_airbag'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                    @endif
                </td>
            </tr>

            {{-- CUÑAS --}}
            <tr class="bg-teal-700 text-white">
                <td colspan="12" class="p-1 text-xs font-bold text-center border border-teal-800">CUÑAS</td>
            </tr>
            <tr class="bg-sky-100">
                <td class="p-1 border border-slate-300 bg-teal-100 text-center"><div class="text-xs font-bold">Biseladas</div></td>
                <td class="p-1 border border-slate-300 text-center">
                    @if($readonly)
                        <div class="font-semibold">{{ $data['cantidades']['cunas_biseladas'] ?? '—' }}</div>
                    @else
                        <input type="text" name="data[cantidades][cunas_biseladas]" value="{{ $data['cantidades']['cunas_biseladas'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                    @endif
                </td>
                <td class="p-1 border border-slate-300 bg-teal-100 text-center"><div class="text-xs font-bold">Bloques</div></td>
                <td class="p-1 border border-slate-300 text-center">
                    @if($readonly)
                        <div class="font-semibold">{{ $data['cantidades']['cunas_bloques'] ?? '—' }}</div>
                    @else
                        <input type="text" name="data[cantidades][cunas_bloques]" value="{{ $data['cantidades']['cunas_bloques'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                    @endif
                </td>
                <td class="p-1 border border-slate-300 bg-teal-100 text-center"><div class="text-xs font-bold">Escalonadas</div></td>
                <td class="p-1 border border-slate-300 text-center">
                    @if($readonly)
                        <div class="font-semibold">{{ $data['cantidades']['cunas_escalonadas'] ?? '—' }}</div>
                    @else
                        <input type="text" name="data[cantidades][cunas_escalonadas]" value="{{ $data['cantidades']['cunas_escalonadas'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                    @endif
                </td>
                <td class="p-1 border border-slate-300 bg-teal-100 text-center"><div class="text-xs font-bold">Planas</div></td>
                <td class="p-1 border border-slate-300 text-center">
                    @if($readonly)
                        <div class="font-semibold">{{ $data['cantidades']['cunas_plan'] ?? '—' }}</div>
                    @else
                        <input type="text" name="data[cantidades][cunas_plan]" value="{{ $data['cantidades']['cunas_plan'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                    @endif
                </td>
                <td colspan="2" class="p-1 border border-slate-300 bg-teal-100 text-center"><div class="text-xs font-bold">Combos de 2 libras</div></td>
                <td colspan="2" class="p-1 border border-slate-300 text-center">
                    @if($readonly)
                        <div class="font-semibold">{{ $data['cantidades']['cunas_combos_2l'] ?? '—' }}</div>
                    @else
                        <input type="text" name="data[cantidades][cunas_combos_2l]" value="{{ $data['cantidades']['cunas_combos_2l'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                    @endif
                </td>
            </tr>

            {{-- Set lona cubre pilares --}}
            <tr class="bg-sky-100">
                <td colspan="4" class="p-1 border border-slate-300 font-bold text-sm">Set lona cubre pilares</td>
                <td colspan="4" class="p-1 border border-slate-300 bg-teal-100 text-center"><div class="text-xs font-bold">Force</div></td>
                <td colspan="4" class="p-1 border border-slate-300 bg-teal-100 text-center"><div class="text-xs font-bold">Combo 8 libras</div></td>
            </tr>
            <tr class="bg-sky-100">
                <td colspan="4" class="p-1 border border-slate-300 bg-sky-100"></td>
                <td colspan="4" class="p-1 border border-slate-300 text-center">
                    @if($readonly)
                        <div class="font-semibold">{{ $data['cantidades']['lona_force'] ?? '—' }}</div>
                    @else
                        <input type="text" name="data[cantidades][lona_force]" value="{{ $data['cantidades']['lona_force'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                    @endif
                </td>
                <td colspan="4" class="p-1 border border-slate-300 text-center">
                    @if($readonly)
                        <div class="font-semibold">{{ $data['cantidades']['combo_8l'] ?? '—' }}</div>
                    @else
                        <input type="text" name="data[cantidades][combo_8l]" value="{{ $data['cantidades']['combo_8l'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                    @endif
                </td>
            </tr>

            {{-- Eslings Naranjas --}}
            <tr class="bg-cyan-50">
                <td colspan="4" class="p-1 border border-slate-300 font-bold text-sm">Eslings Naranjas</td>
                <td colspan="4" class="p-1 border border-slate-300 bg-teal-100 text-center"><div class="text-xs font-bold">Barretilla</div></td>
                <td colspan="4" class="p-1 border border-slate-300 bg-teal-100 text-center"><div class="text-xs font-bold">Halligan</div></td>
            </tr>
            <tr class="bg-cyan-50">
                <td colspan="4" class="p-1 border border-slate-300 bg-cyan-50"></td>
                <td colspan="4" class="p-1 border border-slate-300 text-center">
                    @if($readonly)
                        <div class="font-semibold">{{ $data['cantidades']['eslings_naranjas_barretilla'] ?? '—' }}</div>
                    @else
                        <input type="text" name="data[cantidades][eslings_naranjas_barretilla]" value="{{ $data['cantidades']['eslings_naranjas_barretilla'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                    @endif
                </td>
                <td colspan="4" class="p-1 border border-slate-300 text-center">
                    @if($readonly)
                        <div class="font-semibold">{{ $data['cantidades']['eslings_naranjas_halligan'] ?? '—' }}</div>
                    @else
                        <input type="text" name="data[cantidades][eslings_naranjas_halligan]" value="{{ $data['cantidades']['eslings_naranjas_halligan'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                    @endif
                </td>
            </tr>

            {{-- Eslings Azules --}}
            <tr class="bg-cyan-50">
                <td colspan="4" class="p-1 border border-slate-300 font-bold text-sm">Eslings Azules</td>
                <td colspan="4" class="p-1 border border-slate-300 bg-teal-100 text-center"><div class="text-xs font-bold">Napoleón 24"</div></td>
                <td colspan="4" class="p-1 border border-slate-300 bg-teal-100 text-center"><div class="text-xs font-bold">TNT</div></td>
            </tr>
            <tr class="bg-cyan-50">
                <td colspan="4" class="p-1 border border-slate-300 bg-cyan-50"></td>
                <td colspan="4" class="p-1 border border-slate-300 text-center">
                    @if($readonly)
                        <div class="font-semibold">{{ $data['cantidades']['eslings_azules_napoleon'] ?? '—' }}</div>
                    @else
                        <input type="text" name="data[cantidades][eslings_azules_napoleon]" value="{{ $data['cantidades']['eslings_azules_napoleon'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                    @endif
                </td>
                <td colspan="4" class="p-1 border border-slate-300 text-center">
                    @if($readonly)
                        <div class="font-semibold">{{ $data['cantidades']['eslings_azules_tnt'] ?? '—' }}</div>
                    @else
                        <input type="text" name="data[cantidades][eslings_azules_tnt]" value="{{ $data['cantidades']['eslings_azules_tnt'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                    @endif
                </td>
            </tr>

            {{-- Eslings Ojo a ojo --}}
            <tr class="bg-cyan-50">
                <td colspan="4" class="p-1 border border-slate-300 font-bold text-sm">Eslings Ojo a ojo</td>
                <td colspan="4" class="p-1 border border-slate-300 bg-teal-100 text-center"><div class="text-xs font-bold">Hacha bombero</div></td>
                <td colspan="4" class="p-1 border border-slate-300 bg-teal-100 text-center"><div class="text-xs font-bold">Estacas de fierro</div></td>
            </tr>
            <tr class="bg-cyan-50">
                <td colspan="4" class="p-1 border border-slate-300 bg-cyan-50"></td>
                <td colspan="4" class="p-1 border border-slate-300 text-center">
                    @if($readonly)
                        <div class="font-semibold">{{ $data['cantidades']['eslings_ojo_hacha'] ?? '—' }}</div>
                    @else
                        <input type="text" name="data[cantidades][eslings_ojo_hacha]" value="{{ $data['cantidades']['eslings_ojo_hacha'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                    @endif
                </td>
                <td colspan="4" class="p-1 border border-slate-300 text-center">
                    @if($readonly)
                        <div class="font-semibold">{{ $data['cantidades']['eslings_ojo_estacas'] ?? '—' }}</div>
                    @else
                        <input type="text" name="data[cantidades][eslings_ojo_estacas]" value="{{ $data['cantidades']['eslings_ojo_estacas'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                    @endif
                </td>
            </tr>

            {{-- Cadenas WEBER --}}
            <tr class="bg-cyan-50">
                <td colspan="4" class="p-1 border border-slate-300 font-bold text-sm">Cadenas WEBER</td>
                <td colspan="4" class="p-1 border border-slate-300 bg-teal-100 text-center"><div class="text-xs font-bold">Soporte RAM WEBER</div></td>
                <td colspan="4" class="p-1 border border-slate-300 bg-teal-100 text-center"><div class="text-xs font-bold">Muela RAM Lukas</div></td>
            </tr>
            <tr class="bg-cyan-50">
                <td colspan="4" class="p-1 border border-slate-300 bg-cyan-50"></td>
                <td colspan="4" class="p-1 border border-slate-300 text-center">
                    @if($readonly)
                        <div class="font-semibold">{{ $data['cantidades']['cadenas_soporte_weber'] ?? '—' }}</div>
                    @else
                        <input type="text" name="data[cantidades][cadenas_soporte_weber]" value="{{ $data['cantidades']['cadenas_soporte_weber'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                    @endif
                </td>
                <td colspan="4" class="p-1 border border-slate-300 text-center">
                    @if($readonly)
                        <div class="font-semibold">{{ $data['cantidades']['cadenas_muela_lukas'] ?? '—' }}</div>
                    @else
                        <input type="text" name="data[cantidades][cadenas_muela_lukas]" value="{{ $data['cantidades']['cadenas_muela_lukas'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                    @endif
                </td>
            </tr>

            {{-- Estabilizadores PARATECH --}}
            <tr class="bg-orange-50">
                <td colspan="4" class="p-1 border border-slate-300 font-bold text-sm">Estabilizadores PARATECH</td>
                <td colspan="4" class="p-1 border border-slate-300 bg-teal-100 text-center"><div class="text-xs font-bold">Extensiones</div></td>
                <td colspan="4" class="p-1 border border-slate-300 bg-teal-100 text-center"><div class="text-xs font-bold">Bases</div></td>
            </tr>
            <tr class="bg-orange-50">
                <td colspan="4" class="p-1 border border-slate-300 bg-orange-50"></td>
                <td colspan="4" class="p-1 border border-slate-300 text-center">
                    @if($readonly)
                        <div class="font-semibold">{{ $data['cantidades']['paratech_extensiones'] ?? '—' }}</div>
                    @else
                        <input type="text" name="data[cantidades][paratech_extensiones]" value="{{ $data['cantidades']['paratech_extensiones'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                    @endif
                </td>
                <td colspan="4" class="p-1 border border-slate-300 text-center">
                    @if($readonly)
                        <div class="font-semibold">{{ $data['cantidades']['paratech_bases'] ?? '—' }}</div>
                    @else
                        <input type="text" name="data[cantidades][paratech_bases]" value="{{ $data['cantidades']['paratech_bases'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                    @endif
                </td>
            </tr>

            {{-- Puntas --}}
            <tr class="bg-orange-50">
                <td colspan="4" class="p-1 border border-slate-300 font-bold text-sm">Puntas</td>
                <td colspan="4" class="p-1 border border-slate-300 bg-teal-100 text-center"><div class="text-xs font-bold">Llaveros</div></td>
                <td colspan="4" class="p-1 border border-slate-300 bg-teal-100 text-center"><div class="text-xs font-bold">Eslings</div></td>
            </tr>
            <tr class="bg-orange-50">
                <td colspan="4" class="p-1 border border-slate-300 bg-orange-50"></td>
                <td colspan="4" class="p-1 border border-slate-300 text-center">
                    @if($readonly)
                        <div class="font-semibold">{{ $data['cantidades']['puntas_llaveros'] ?? '—' }}</div>
                    @else
                        <input type="text" name="data[cantidades][puntas_llaveros]" value="{{ $data['cantidades']['puntas_llaveros'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                    @endif
                </td>
                <td colspan="4" class="p-1 border border-slate-300 text-center">
                    @if($readonly)
                        <div class="font-semibold">{{ $data['cantidades']['puntas_eslings'] ?? '—' }}</div>
                    @else
                        <input type="text" name="data[cantidades][puntas_eslings]" value="{{ $data['cantidades']['puntas_eslings'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                    @endif
                </td>
            </tr>

            {{-- Plataforma de Rescate --}}
            <tr class="bg-sky-100">
                <td colspan="4" class="p-1 border border-slate-300 font-bold text-sm">Plataforma de Rescate</td>
                <td colspan="4" class="p-1 border border-slate-300 bg-teal-100 text-center"><div class="text-xs font-bold">Escalas</div></td>
                <td colspan="4" class="p-1 border border-slate-300 bg-teal-100 text-center"><div class="text-xs font-bold">Conos</div></td>
            </tr>
            <tr class="bg-sky-100">
                <td colspan="4" class="p-1 border border-slate-300 bg-sky-100"></td>
                <td colspan="4" class="p-1 border border-slate-300 text-center">
                    @if($readonly)
                        <div class="font-semibold">{{ $data['cantidades']['plataforma_escalas'] ?? '—' }}</div>
                    @else
                        <input type="text" name="data[cantidades][plataforma_escalas]" value="{{ $data['cantidades']['plataforma_escalas'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                    @endif
                </td>
                <td colspan="4" class="p-1 border border-slate-300 text-center">
                    @if($readonly)
                        <div class="font-semibold">{{ $data['cantidades']['plataforma_conos'] ?? '—' }}</div>
                    @else
                        <input type="text" name="data[cantidades][plataforma_conos]" value="{{ $data['cantidades']['plataforma_conos'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                    @endif
                </td>
            </tr>

            {{-- Colchón Vetter --}}
            <tr class="bg-sky-100">
                <td colspan="4" class="p-1 border border-slate-300 font-bold text-sm">Colchón Vetter baja presión</td>
                <td colspan="4" class="p-1 border border-slate-300 bg-teal-100 text-center"><div class="text-xs font-bold">Set apertura puertas</div></td>
                <td colspan="4" class="p-1 border border-slate-300 bg-teal-100 text-center"><div class="text-xs font-bold">Palas</div></td>
            </tr>
            <tr class="bg-sky-100">
                <td colspan="4" class="p-1 border border-slate-300 bg-sky-100"></td>
                <td colspan="4" class="p-1 border border-slate-300 text-center">
                    @if($readonly)
                        <div class="font-semibold">{{ $data['cantidades']['vetter_apertura_puertas'] ?? '—' }}</div>
                    @else
                        <input type="text" name="data[cantidades][vetter_apertura_puertas]" value="{{ $data['cantidades']['vetter_apertura_puertas'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                    @endif
                </td>
                <td colspan="4" class="p-1 border border-slate-300 text-center">
                    @if($readonly)
                        <div class="font-semibold">{{ $data['cantidades']['vetter_palas'] ?? '—' }}</div>
                    @else
                        <input type="text" name="data[cantidades][vetter_palas]" value="{{ $data['cantidades']['vetter_palas'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                    @endif
                </td>
            </tr>

            {{-- Paquete Circular --}}
            <tr class="bg-sky-100">
                <td colspan="4" class="p-1 border border-slate-300 font-bold text-sm">Paquete Circular</td>
                <td colspan="4" class="p-1 border border-slate-300 bg-teal-100 text-center"><div class="text-xs font-bold">Llave de grifo</div></td>
                <td colspan="4" class="p-1 border border-slate-300 bg-teal-100 text-center"><div class="text-xs font-bold">Traspaso de grifo</div></td>
            </tr>
            <tr class="bg-sky-100">
                <td colspan="4" class="p-1 border border-slate-300 bg-sky-100"></td>
                <td colspan="4" class="p-1 border border-slate-300 text-center">
                    @if($readonly)
                        <div class="font-semibold">{{ $data['cantidades']['paquete_llave_grifo'] ?? '—' }}</div>
                    @else
                        <input type="text" name="data[cantidades][paquete_llave_grifo]" value="{{ $data['cantidades']['paquete_llave_grifo'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                    @endif
                </td>
                <td colspan="4" class="p-1 border border-slate-300 text-center">
                    @if($readonly)
                        <div class="font-semibold">{{ $data['cantidades']['paquete_traspaso_grifo'] ?? '—' }}</div>
                    @else
                        <input type="text" name="data[cantidades][paquete_traspaso_grifo]" value="{{ $data['cantidades']['paquete_traspaso_grifo'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                    @endif
                </td>
            </tr>

            {{-- Manguera de alimentación --}}
            <tr class="bg-sky-100">
                <td colspan="4" class="p-1 border border-slate-300 font-bold text-sm">Manguera de alimentación</td>
                <td colspan="4" class="p-1 border border-slate-300 bg-teal-100 text-center"><div class="text-xs font-bold">Traspasos</div></td>
                <td colspan="4" class="p-1 border border-slate-300 bg-teal-100 text-center"><div class="text-xs font-bold">Llaves de copla</div></td>
            </tr>
            <tr class="bg-sky-100">
                <td colspan="4" class="p-1 border border-slate-300 bg-sky-100"></td>
                <td colspan="4" class="p-1 border border-slate-300 text-center">
                    @if($readonly)
                        <div class="font-semibold">{{ $data['cantidades']['manguera_traspasos'] ?? '—' }}</div>
                    @else
                        <input type="text" name="data[cantidades][manguera_traspasos]" value="{{ $data['cantidades']['manguera_traspasos'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                    @endif
                </td>
                <td colspan="4" class="p-1 border border-slate-300 text-center">
                    @if($readonly)
                        <div class="font-semibold">{{ $data['cantidades']['manguera_llaves_copla'] ?? '—' }}</div>
                    @else
                        <input type="text" name="data[cantidades][manguera_llaves_copla]" value="{{ $data['cantidades']['manguera_llaves_copla'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                    @endif
                </td>
            </tr>
        </table>
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
    <div class="overflow-x-auto">
        <table class="w-full border-collapse text-sm mt-2" style="min-width: 600px;">
            <tr class="bg-teal-800 text-white">
                <th colspan="12" class="p-2 text-xs font-black uppercase tracking-widest text-left border border-teal-900">General Unidad</th>
            </tr>

            <tr class="bg-sky-100">
                <td colspan="6" class="p-1 border border-slate-300">
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
                </td>
                <td colspan="6" class="p-1 border border-slate-300">
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
                </td>
            </tr>

            <tr class="bg-sky-100">
                <td colspan="6" class="p-1 border border-slate-300">
                    <div class="text-xs font-bold">Nivel Combustible del Bidón</div>
                    @if($readonly)
                        <div class="font-semibold">{{ $data['general']['nivel_combustible_bidon'] ?? '—' }}</div>
                    @else
                        <input type="text" name="data[general][nivel_combustible_bidon]" value="{{ $data['general']['nivel_combustible_bidon'] ?? '' }}" class="w-full text-sm border rounded p-1">
                    @endif
                </td>
                <td colspan="6" class="p-1 border border-slate-300">
                    <div class="text-xs font-bold">Nivel Aceite para mezcla (Jeringa)</div>
                    @if($readonly)
                        <div class="font-semibold">{{ $data['general']['nivel_aceite_mezcla'] ?? '—' }}</div>
                    @else
                        <input type="text" name="data[general][nivel_aceite_mezcla]" value="{{ $data['general']['nivel_aceite_mezcla'] ?? '' }}" class="w-full text-sm border rounded p-1">
                    @endif
                </td>
            </tr>

            <tr class="bg-sky-100">
                <td colspan="6" class="p-1 border border-slate-300">
                    <div class="text-xs font-bold">Nivel Combustible de la Unidad</div>
                    @if($readonly)
                        <div class="font-semibold">{{ $data['general']['nivel_combustible_unidad'] ?? '—' }}</div>
                    @else
                        <input type="text" name="data[general][nivel_combustible_unidad]" value="{{ $data['general']['nivel_combustible_unidad'] ?? '' }}" class="w-full text-sm border rounded p-1">
                    @endif
                </td>
                <td colspan="6" class="p-1 border border-slate-300">
                    <div class="text-xs font-bold">Nivel de agua de la unidad</div>
                    @if($readonly)
                        <div class="font-semibold">{{ $data['general']['nivel_agua_unidad'] ?? '—' }}</div>
                    @else
                        <input type="text" name="data[general][nivel_agua_unidad]" value="{{ $data['general']['nivel_agua_unidad'] ?? '' }}" class="w-full text-sm border rounded p-1">
                    @endif
                </td>
            </tr>

            <tr class="bg-sky-100">
                <td colspan="12" class="p-1 border border-slate-300">
                    <div class="text-xs font-bold">Nivel de aceite para cadena</div>
                    @if($readonly)
                        <div class="font-semibold">{{ $data['general']['nivel_aceite_cadena'] ?? '—' }}</div>
                    @else
                        <input type="text" name="data[general][nivel_aceite_cadena]" value="{{ $data['general']['nivel_aceite_cadena'] ?? '' }}" class="w-full text-sm border rounded p-1">
                    @endif
                </td>
            </tr>
        </table>
    </div>
</div>
