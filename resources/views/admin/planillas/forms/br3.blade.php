@php
    $data = $data ?? [];
    $readonly = $readonly ?? false;
@endphp

<div class="space-y-2">
    {{-- Header --}}
    <div class="bg-sky-100 rounded-t-lg border border-teal-900/20 p-3 flex justify-between items-center">
        <div class="text-xs font-black uppercase tracking-widest text-slate-900">PLANILLA DE REVISIÓN DE NIVELES</div>
        <div class="text-sm font-black text-slate-900">BR-3</div>
    </div>

    {{-- CABINA --}}
    <div class="overflow-x-auto">
        <table class="w-full border-collapse text-sm" style="min-width: 800px;">
            <tr class="bg-teal-800 text-white">
                <th colspan="12" class="p-2 text-xs font-black uppercase tracking-widest text-left border border-teal-900">CABINA</th>
            </tr>
            {{-- Linterna --}}
            <tr class="bg-sky-100">
                <td colspan="3" class="p-1 border border-slate-300 font-bold text-sm">Linterna NIGHTSTICK</td>
                <td colspan="2" class="p-1 border border-slate-300">
                    <div class="text-xs">Funcionamiento</div>
                    @if($readonly)
                        <div class="font-semibold">{{ ($data['cabina']['linterna_nightstick']['funcionamiento'] ?? '') === 'si' ? 'Sí' : (($data['cabina']['linterna_nightstick']['funcionamiento'] ?? '') === 'no' ? 'No' : '—') }}</div>
                    @else
                        <select name="data[cabina][linterna_nightstick][funcionamiento]" class="w-full text-sm border rounded p-1">
                            <option value=""></option>
                            <option value="si" {{ ($data['cabina']['linterna_nightstick']['funcionamiento'] ?? '') === 'si' ? 'selected' : '' }}>Sí</option>
                            <option value="no" {{ ($data['cabina']['linterna_nightstick']['funcionamiento'] ?? '') === 'no' ? 'selected' : '' }}>No</option>
                        </select>
                    @endif
                </td>
                <td colspan="1" class="p-1 border border-slate-300 bg-sky-100"></td>
                <td colspan="2" class="p-1 border border-slate-300 font-bold text-sm">Radios Baoffeng</td>
                <td colspan="2" class="p-1 border border-slate-300">
                    <div class="text-xs">Nº</div>
                    @if($readonly)
                        <div class="font-semibold">{{ $data['cabina']['radios_baofeng']['numero'] ?? '—' }}</div>
                    @else
                        <input type="text" name="data[cabina][radios_baofeng][numero]" value="{{ $data['cabina']['radios_baofeng']['numero'] ?? '' }}" class="w-full text-sm border rounded p-1">
                    @endif
                </td>
                <td colspan="2" class="p-1 border border-slate-300">
                    <div class="text-xs">Bat</div>
                    @if($readonly)
                        <div class="font-semibold">{{ $data['cabina']['radios_baofeng']['bateria'] ?? '—' }}</div>
                    @else
                        <input type="text" name="data[cabina][radios_baofeng][bateria]" value="{{ $data['cabina']['radios_baofeng']['bateria'] ?? '' }}" class="w-full text-sm border rounded p-1">
                    @endif
                </td>
            </tr>
            {{-- ERA SCOTT --}}
            <tr class="bg-sky-100">
                <td colspan="3" class="p-1 border border-slate-300 font-bold text-sm">ERA SCOTT 4.5</td>
                <td colspan="2" class="p-1 border border-slate-300">
                    <div class="text-xs">Cantidad</div>
                    @if($readonly)
                        <div class="font-semibold">{{ $data['cabina']['era_scott_4_5']['cantidad'] ?? '—' }}</div>
                    @else
                        <input type="text" name="data[cabina][era_scott_4_5][cantidad]" value="{{ $data['cabina']['era_scott_4_5']['cantidad'] ?? '' }}" class="w-full text-sm border rounded p-1">
                    @endif
                </td>
                <td colspan="1" class="p-1 border border-slate-300 bg-sky-100"></td>
                <td colspan="2" class="p-1 border border-slate-300">
                    <div class="text-xs">Funcionamiento</div>
                    @if($readonly)
                        <div class="font-semibold">{{ ($data['cabina']['era_scott_4_5']['funcionamiento'] ?? '') === 'si' ? 'Sí' : (($data['cabina']['era_scott_4_5']['funcionamiento'] ?? '') === 'no' ? 'No' : '—') }}</div>
                    @else
                        <select name="data[cabina][era_scott_4_5][funcionamiento]" class="w-full text-sm border rounded p-1">
                            <option value=""></option>
                            <option value="si" {{ ($data['cabina']['era_scott_4_5']['funcionamiento'] ?? '') === 'si' ? 'selected' : '' }}>Sí</option>
                            <option value="no" {{ ($data['cabina']['era_scott_4_5']['funcionamiento'] ?? '') === 'no' ? 'selected' : '' }}>No</option>
                        </select>
                    @endif
                </td>
                <td colspan="4" class="p-1 border border-slate-300">
                    <div class="text-xs">Nivel de aire</div>
                    @if($readonly)
                        <div class="font-semibold">{{ $data['cabina']['era_scott_4_5']['nivel_aire'] ?? '—' }}</div>
                    @else
                        <input type="text" name="data[cabina][era_scott_4_5][nivel_aire]" value="{{ $data['cabina']['era_scott_4_5']['nivel_aire'] ?? '' }}" class="w-full text-sm border rounded p-1">
                    @endif
                </td>
            </tr>
            {{-- Chaquetillas --}}
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
                <td colspan="3" class="p-1 border border-slate-300 font-bold text-sm">Lona organizadora material</td>
                <td colspan="3" class="p-1 border border-slate-300">
                    @if($readonly)
                        <div class="font-semibold">{{ $data['cabina']['lona_organizadora']['cantidad'] ?? '—' }}</div>
                    @else
                        <input type="text" name="data[cabina][lona_organizadora][cantidad]" value="{{ $data['cabina']['lona_organizadora']['cantidad'] ?? '' }}" class="w-full text-sm border rounded p-1" placeholder="Cantidad">
                    @endif
                </td>
            </tr>
            {{-- Tablet --}}
            <tr class="bg-sky-100">
                <td colspan="3" class="p-1 border border-slate-300 font-bold text-sm">Tablet unidad BR-3 y Cargador</td>
                <td colspan="2" class="p-1 border border-slate-300">
                    <div class="text-xs">Batería</div>
                    @if($readonly)
                        <div class="font-semibold">{{ ($data['cabina']['tablet_br3']['bateria'] ?? '') === 'si' ? 'Sí' : (($data['cabina']['tablet_br3']['bateria'] ?? '') === 'no' ? 'No' : '—') }}</div>
                    @else
                        <select name="data[cabina][tablet_br3][bateria]" class="w-full text-sm border rounded p-1">
                            <option value=""></option>
                            <option value="si" {{ ($data['cabina']['tablet_br3']['bateria'] ?? '') === 'si' ? 'selected' : '' }}>Sí</option>
                            <option value="no" {{ ($data['cabina']['tablet_br3']['bateria'] ?? '') === 'no' ? 'selected' : '' }}>No</option>
                        </select>
                    @endif
                </td>
                <td colspan="1" class="p-1 border border-slate-300 bg-sky-100"></td>
                <td colspan="3" class="p-1 border border-slate-300 font-bold text-sm">Maleta SCI</td>
                <td colspan="3" class="p-1 border border-slate-300">
                    @if($readonly)
                        <div class="font-semibold">{{ $data['cabina']['maleta_sci']['cantidad'] ?? '—' }}</div>
                    @else
                        <input type="text" name="data[cabina][maleta_sci][cantidad]" value="{{ $data['cabina']['maleta_sci']['cantidad'] ?? '' }}" class="w-full text-sm border rounded p-1" placeholder="Cantidad">
                    @endif
                </td>
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
                'ripper' => 'RIPPER (corta parabrisas)',
                'baston_tastik' => 'Bastón Tastik',
                'generador_honda' => 'Generador eléctrico Honda',
                'motosierra_stihl' => 'Motosierra Stihl MS170',
                'motor_holmatro' => 'Motor HOLMATRO y herramientas',
                'combi_lukas' => 'Combi Lukas E-Draulik GMBH',
                'motoamoladora' => 'Motoamoladora MAKITA',
                'sierra_sable' => 'Sierra sable HILTI',
                'dremel' => 'Dremel y accesorios',
                'martillo_neumatico' => 'Martillo Neumático',
                'taladro_makita' => 'Taladro inalámbrico MAKITA',
                'control_vetter' => 'Control cojines VETTER',
                'esmeril_angular' => 'Esmeril angular',
            ];
            $highlightTools = ['ripper', 'baston_tastik', 'generador_honda', 'motosierra_stihl', 'motor_holmatro', 'combi_lukas', 'motoamoladora', 'sierra_sable'];
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
            {{-- Bolso Oxigenoterapia --}}
            <tr class="bg-sky-100">
                <td colspan="3" class="p-1 border border-slate-300 font-bold text-sm">Bolso Oxigenoterapia</td>
                <td colspan="2" class="p-1 border border-slate-300 bg-teal-100">
                    <div class="text-xs font-bold text-center">NIVEL O.</div>
                    @if($readonly)
                        <div class="font-semibold text-center">{{ $data['trauma']['bolso_oxigenoterapia']['nivelo1'] ?? '—' }}</div>
                    @else
                        <input type="text" name="data[trauma][bolso_oxigenoterapia][nivelo1]" value="{{ $data['trauma']['bolso_oxigenoterapia']['nivelo1'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                    @endif
                </td>
                <td colspan="2" class="p-1 border border-slate-300 bg-teal-100">
                    <div class="text-xs font-bold text-center">NIVEL O.</div>
                    @if($readonly)
                        <div class="font-semibold text-center">{{ $data['trauma']['bolso_oxigenoterapia']['nivelo2'] ?? '—' }}</div>
                    @else
                        <input type="text" name="data[trauma][bolso_oxigenoterapia][nivelo2]" value="{{ $data['trauma']['bolso_oxigenoterapia']['nivelo2'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
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
            {{-- Chalecos extricación --}}
            <tr class="bg-sky-100">
                <td colspan="3" class="p-1 border border-slate-300 font-bold text-sm">Chalecos de extricación</td>
                <td colspan="2" class="p-1 border border-slate-300">
                    <div class="text-xs">Adulto</div>
                    @if($readonly)
                        <div class="font-semibold">{{ $data['trauma']['chalecos_extricacion']['adulto'] ?? '—' }}</div>
                    @else
                        <input type="text" name="data[trauma][chalecos_extricacion][adulto]" value="{{ $data['trauma']['chalecos_extricacion']['adulto'] ?? '' }}" class="w-full text-sm border rounded p-1">
                    @endif
                </td>
                <td colspan="2" class="p-1 border border-slate-300">
                    <div class="text-xs">PED</div>
                    @if($readonly)
                        <div class="font-semibold">{{ $data['trauma']['chalecos_extricacion']['ped'] ?? '—' }}</div>
                    @else
                        <input type="text" name="data[trauma][chalecos_extricacion][ped]" value="{{ $data['trauma']['chalecos_extricacion']['ped'] ?? '' }}" class="w-full text-sm border rounded p-1">
                    @endif
                </td>
                <td colspan="2" class="p-1 border border-slate-300">
                    <div class="text-xs">G2</div>
                    @if($readonly)
                        <div class="font-semibold">{{ $data['trauma']['chalecos_extricacion']['g2'] ?? '—' }}</div>
                    @else
                        <input type="text" name="data[trauma][chalecos_extricacion][g2]" value="{{ $data['trauma']['chalecos_extricacion']['g2'] ?? '' }}" class="w-full text-sm border rounded p-1">
                    @endif
                </td>
                <td colspan="3" class="p-1 border border-slate-300 font-bold text-sm">Férulas</td>
            </tr>
            {{-- Tablas Largas y Tabla Scoop --}}
            <tr class="bg-sky-100">
                <td colspan="3" class="p-1 border border-slate-300 font-bold text-sm">Tablas Largas</td>
                <td colspan="2" class="p-1 border border-slate-300">
                    <div class="text-xs">Techo</div>
                    @if($readonly)
                        <div class="font-semibold">{{ $data['trauma']['tablas_largas']['techo'] ?? '—' }}</div>
                    @else
                        <input type="text" name="data[trauma][tablas_largas][techo]" value="{{ $data['trauma']['tablas_largas']['techo'] ?? '' }}" class="w-full text-sm border rounded p-1">
                    @endif
                </td>
                <td colspan="2" class="p-1 border border-slate-300 bg-sky-100"></td>
                <td colspan="2" class="p-1 border border-slate-300 font-bold text-sm">Tabla Scoop</td>
                <td colspan="3" class="p-1 border border-slate-300">
                    @if($readonly)
                        <div class="font-semibold">{{ $data['trauma']['tabla_scoop'] ?? '—' }}</div>
                    @else
                        <input type="text" name="data[trauma][tabla_scoop]" value="{{ $data['trauma']['tabla_scoop'] ?? '' }}" class="w-full text-sm border rounded p-1">
                    @endif
                </td>
            </tr>
            {{-- Tabla pediátrica --}}
            <tr class="bg-sky-100">
                <td colspan="3" class="p-1 border border-slate-300 font-bold text-sm">Tabla pediátrica</td>
                <td colspan="2" class="p-1 border border-slate-300">
                    <div class="text-xs">Tabla corta</div>
                    @if($readonly)
                        <div class="font-semibold">{{ $data['trauma']['tabla_pediatrica']['tabla_corta'] ?? '—' }}</div>
                    @else
                        <input type="text" name="data[trauma][tabla_pediatrica][tabla_corta]" value="{{ $data['trauma']['tabla_pediatrica']['tabla_corta'] ?? '' }}" class="w-full text-sm border rounded p-1">
                    @endif
                </td>
                <td colspan="2" class="p-1 border border-slate-300 bg-sky-100"></td>
                <td colspan="2" class="p-1 border border-slate-300 font-bold text-sm">Laterales</td>
                <td colspan="3" class="p-1 border border-slate-300">
                    @if($readonly)
                        <div class="font-semibold">{{ $data['trauma']['laterales'] ?? '—' }}</div>
                    @else
                        <input type="text" name="data[trauma][laterales]" value="{{ $data['trauma']['laterales'] ?? '' }}" class="w-full text-sm border rounded p-1">
                    @endif
                </td>
            </tr>
            {{-- Pulpos y Bolso TRIAGE --}}
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
            <tr class="bg-sky-100">
                <td colspan="6" class="p-1 border border-slate-300 bg-sky-100"></td>
                <td colspan="2" class="p-1 border border-slate-300 font-bold text-sm">Bolso TRIAGE</td>
                <td colspan="4" class="p-1 border border-slate-300">
                    @if($readonly)
                        <div class="font-semibold">{{ $data['trauma']['bolso_triage'] ?? '—' }}</div>
                    @else
                        <input type="text" name="data[trauma][bolso_triage]" value="{{ $data['trauma']['bolso_triage'] ?? '' }}" class="w-full text-sm border rounded p-1">
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

            {{-- MANGUERAS --}}
            <tr class="bg-teal-700 text-white">
                <td colspan="12" class="p-1 text-xs font-bold text-center border border-teal-800">MANGUERAS</td>
            </tr>
            <tr class="bg-sky-100">
                <td class="p-1 border border-slate-300 bg-teal-100 text-center"><div class="text-xs font-bold">38mm</div></td>
                <td class="p-1 border border-slate-300 text-center">
                    @if($readonly)
                        <div class="font-semibold">{{ $data['cantidades']['mangueras_38mm'] ?? '—' }}</div>
                    @else
                        <input type="text" name="data[cantidades][mangueras_38mm]" value="{{ $data['cantidades']['mangueras_38mm'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                    @endif
                </td>
                <td class="p-1 border border-slate-300 bg-teal-100 text-center"><div class="text-xs font-bold">52mm</div></td>
                <td class="p-1 border border-slate-300 text-center">
                    @if($readonly)
                        <div class="font-semibold">{{ $data['cantidades']['mangueras_52mm'] ?? '—' }}</div>
                    @else
                        <input type="text" name="data[cantidades][mangueras_52mm]" value="{{ $data['cantidades']['mangueras_52mm'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                    @endif
                </td>
                <td class="p-1 border border-slate-300 bg-teal-100 text-center"><div class="text-xs font-bold">75mm</div></td>
                <td class="p-1 border border-slate-300 text-center">
                    @if($readonly)
                        <div class="font-semibold">{{ $data['cantidades']['mangueras_75mm'] ?? '—' }}</div>
                    @else
                        <input type="text" name="data[cantidades][mangueras_75mm]" value="{{ $data['cantidades']['mangueras_75mm'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                    @endif
                </td>
                <td class="p-1 border border-slate-300 bg-teal-100 text-center"><div class="text-xs font-bold">L D H</div></td>
                <td class="p-1 border border-slate-300 text-center">
                    @if($readonly)
                        <div class="font-semibold">{{ $data['cantidades']['mangueras_ldh'] ?? '—' }}</div>
                    @else
                        <input type="text" name="data[cantidades][mangueras_ldh]" value="{{ $data['cantidades']['mangueras_ldh'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                    @endif
                </td>
                <td colspan="2" class="p-1 border border-slate-300 bg-teal-100 text-center"><div class="text-xs font-bold">Armada Base</div></td>
                <td colspan="2" class="p-1 border border-slate-300 text-center">
                    @if($readonly)
                        <div class="font-semibold">{{ $data['cantidades']['mangueras_armada_base'] ?? '—' }}</div>
                    @else
                        <input type="text" name="data[cantidades][mangueras_armada_base]" value="{{ $data['cantidades']['mangueras_armada_base'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                    @endif
                </td>
            </tr>

        {{-- HERRADURAS --}}
        <tr class="bg-teal-700 text-white">
            <td colspan="12" class="p-1 text-xs font-bold text-center border border-teal-800">HERRADURAS</td>
        </tr>
        <tr class="bg-sky-100">
            <td colspan="2" class="p-1 border border-slate-300 bg-teal-100 text-center"><div class="text-xs font-bold">Cantidad</div></td>
            <td colspan="2" class="p-1 border border-slate-300 text-center">
                @if($readonly)
                    <div class="font-semibold">{{ $data['cantidades']['herraduras_cantidad'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][herraduras_cantidad]" value="{{ $data['cantidades']['herraduras_cantidad'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </td>
            <td colspan="3" class="p-1 border border-slate-300 bg-teal-100 text-center"><div class="text-xs font-bold">Llaves de copla</div></td>
            <td colspan="2" class="p-1 border border-slate-300 text-center">
                @if($readonly)
                    <div class="font-semibold">{{ $data['cantidades']['herraduras_llaves_copla'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][herraduras_llaves_copla]" value="{{ $data['cantidades']['herraduras_llaves_copla'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </td>
            <td colspan="3" class="p-1 border border-slate-300 bg-teal-100 text-center"><div class="text-xs font-bold">Pitón Rosenbauer 52</div></td>
        </tr>

        {{-- ATAQUES --}}
        <tr class="bg-teal-700 text-white">
            <td colspan="12" class="p-1 text-xs font-bold text-center border border-teal-800">ATAQUES</td>
        </tr>
        <tr class="bg-sky-100">
            <td class="p-1 border border-slate-300 bg-teal-100 text-center"><div class="text-xs font-bold">52mm</div></td>
            <td class="p-1 border border-slate-300 text-center">
                @if($readonly)
                    <div class="font-semibold">{{ $data['cantidades']['ataques_52mm'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][ataques_52mm]" value="{{ $data['cantidades']['ataques_52mm'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </td>
            <td class="p-1 border border-slate-300 bg-teal-100 text-center"><div class="text-xs font-bold">75mm</div></td>
            <td class="p-1 border border-slate-300 text-center">
                @if($readonly)
                    <div class="font-semibold">{{ $data['cantidades']['ataques_75mm'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][ataques_75mm]" value="{{ $data['cantidades']['ataques_75mm'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </td>
            <td colspan="4" class="p-1 border border-slate-300 bg-teal-100 text-center"><div class="text-xs font-bold">Manguera L D H</div></td>
            <td colspan="4" class="p-1 border border-slate-300 text-center">
                @if($readonly)
                    <div class="font-semibold">{{ $data['cantidades']['ataques_manguera_ldh'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][ataques_manguera_ldh]" value="{{ $data['cantidades']['ataques_manguera_ldh'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </td>
        </tr>

        {{-- TRASPASOS --}}
        <tr class="bg-teal-700 text-white">
            <td colspan="12" class="p-1 text-xs font-bold text-center border border-teal-800">TRASPASOS</td>
        </tr>
        <tr class="bg-sky-100">
            <td colspan="2" class="p-1 border border-slate-300 bg-teal-100 text-center"><div class="text-xs font-bold">Cantidad</div></td>
            <td colspan="2" class="p-1 border border-slate-300 text-center">
                @if($readonly)
                    <div class="font-semibold">{{ $data['cantidades']['traspasos_cantidad'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][traspasos_cantidad]" value="{{ $data['cantidades']['traspasos_cantidad'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </td>
            <td colspan="2" class="p-1 border border-slate-300 bg-teal-100 text-center"><div class="text-xs font-bold">Llave de grifo</div></td>
            <td colspan="2" class="p-1 border border-slate-300 text-center">
                @if($readonly)
                    <div class="font-semibold">{{ $data['cantidades']['traspasos_llave_grifo'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][traspasos_llave_grifo]" value="{{ $data['cantidades']['traspasos_llave_grifo'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </td>
            <td colspan="2" class="p-1 border border-slate-300 bg-teal-100 text-center"><div class="text-xs font-bold">Trifurca</div></td>
            <td colspan="2" class="p-1 border border-slate-300 text-center">
                @if($readonly)
                    <div class="font-semibold">{{ $data['cantidades']['traspasos_trifurca'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][traspasos_trifurca]" value="{{ $data['cantidades']['traspasos_trifurca'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </td>
        </tr>
        <tr class="bg-sky-100">
            <td colspan="4" class="p-1 border border-slate-300 bg-sky-100"></td>
            <td colspan="4" class="p-1 border border-slate-300 bg-teal-100 text-center"><div class="text-xs font-bold">Traspaso grifo</div></td>
            <td colspan="4" class="p-1 border border-slate-300 text-center">
                @if($readonly)
                    <div class="font-semibold">{{ $data['cantidades']['traspasos_traspaso_grifo'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][traspasos_traspaso_grifo]" value="{{ $data['cantidades']['traspasos_traspaso_grifo'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </td>
        </tr>

        {{-- Protecciones --}}
        <tr class="bg-sky-100">
            <td colspan="3" class="p-1 border border-slate-300 font-bold text-sm">Protecciones duras para paciente</td>
            <td colspan="3" class="p-1 border border-slate-300 bg-teal-100 text-center"><div class="text-xs font-bold">Cojines VETTER</div></td>
            <td colspan="3" class="p-1 border border-slate-300 bg-teal-100 text-center"><div class="text-xs font-bold">Eslings</div></td>
            <td colspan="3" class="p-1 border border-slate-300 bg-sky-100"></td>
        </tr>
        <tr class="bg-sky-100">
            <td colspan="3" class="p-1 border border-slate-300 bg-sky-100"></td>
            <td colspan="3" class="p-1 border border-slate-300 text-center">
                @if($readonly)
                    <div class="font-semibold">{{ $data['cantidades']['cojines_vetter'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][cojines_vetter]" value="{{ $data['cantidades']['cojines_vetter'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </td>
            <td colspan="3" class="p-1 border border-slate-300 text-center">
                @if($readonly)
                    <div class="font-semibold">{{ $data['cantidades']['eslings'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][eslings]" value="{{ $data['cantidades']['eslings'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </td>
            <td colspan="3" class="p-1 border border-slate-300 bg-sky-100"></td>
        </tr>

        {{-- Tecle y caja --}}
        <tr class="bg-sky-100">
            <td colspan="4" class="p-1 border border-slate-300 bg-teal-100 text-center"><div class="text-xs font-bold">Tecle para cadena 2000kg</div></td>
            <td colspan="2" class="p-1 border border-slate-300 text-center">
                @if($readonly)
                    <div class="font-semibold">{{ $data['cantidades']['tecle_cadena'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][tecle_cadena]" value="{{ $data['cantidades']['tecle_cadena'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </td>
            <td colspan="3" class="p-1 border border-slate-300 bg-teal-100 text-center"><div class="text-xs font-bold">Caja de Herramientas</div></td>
            <td colspan="3" class="p-1 border border-slate-300 text-center">
                @if($readonly)
                    <div class="font-semibold">{{ $data['cantidades']['caja_herramientas'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][caja_herramientas]" value="{{ $data['cantidades']['caja_herramientas'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </td>
        </tr>
        <tr class="bg-sky-100">
            <td colspan="6" class="p-1 border border-slate-300 bg-sky-100"></td>
            <td colspan="3" class="p-1 border border-slate-300 bg-teal-100 text-center"><div class="text-xs font-bold">Cubre Airbag</div></td>
            <td colspan="3" class="p-1 border border-slate-300 text-center">
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

        {{-- CAJONERAS LATERALES --}}
        <tr class="bg-teal-700 text-white">
            <td colspan="12" class="p-1 text-xs font-bold text-center border border-teal-800">CAJONERAS LATERALES</td>
        </tr>
        <tr class="bg-sky-100">
            <td colspan="3" class="p-1 border border-slate-300 bg-teal-100 text-center"><div class="text-xs font-bold">Barretilla</div></td>
            <td colspan="3" class="p-1 border border-slate-300 text-center">
                @if($readonly)
                    <div class="font-semibold">{{ $data['cantidades']['cajoneras_barretilla'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][cajoneras_barretilla]" value="{{ $data['cantidades']['cajoneras_barretilla'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </td>
            <td colspan="3" class="p-1 border border-slate-300 bg-teal-100 text-center"><div class="text-xs font-bold">Napoleón</div></td>
            <td colspan="3" class="p-1 border border-slate-300 text-center">
                @if($readonly)
                    <div class="font-semibold">{{ $data['cantidades']['cajoneras_napoleon'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][cajoneras_napoleon]" value="{{ $data['cantidades']['cajoneras_napoleon'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </td>
        </tr>
        <tr class="bg-sky-100">
            <td colspan="3" class="p-1 border border-slate-300 bg-teal-100 text-center"><div class="text-xs font-bold">Set Stab Fast XL</div></td>
            <td colspan="3" class="p-1 border border-slate-300 text-center">
                @if($readonly)
                    <div class="font-semibold">{{ $data['cantidades']['cajoneras_stab_fast'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][cajoneras_stab_fast]" value="{{ $data['cantidades']['cajoneras_stab_fast'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </td>
            <td colspan="3" class="p-1 border border-slate-300 bg-teal-100 text-center"><div class="text-xs font-bold">First Responder Jack</div></td>
            <td colspan="3" class="p-1 border border-slate-300 text-center">
                @if($readonly)
                    <div class="font-semibold">{{ $data['cantidades']['cajoneras_jack'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][cajoneras_jack]" value="{{ $data['cantidades']['cajoneras_jack'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </td>
        </tr>

        {{-- ESCALAS --}}
        <tr class="bg-teal-700 text-white">
            <td colspan="12" class="p-1 text-xs font-bold text-center border border-teal-800">ESCALAS</td>
        </tr>
        <tr class="bg-sky-100">
            <td colspan="4" class="p-1 border border-slate-300 bg-teal-100 text-center"><div class="text-xs font-bold">2 cuerpos 12m</div></td>
            <td colspan="2" class="p-1 border border-slate-300 text-center">
                @if($readonly)
                    <div class="font-semibold">{{ $data['cantidades']['escalas_2c_12m'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][escalas_2c_12m]" value="{{ $data['cantidades']['escalas_2c_12m'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </td>
            <td colspan="4" class="p-1 border border-slate-300 bg-teal-100 text-center"><div class="text-xs font-bold">2 cuerpos 8m</div></td>
            <td colspan="2" class="p-1 border border-slate-300 text-center">
                @if($readonly)
                    <div class="font-semibold">{{ $data['cantidades']['escalas_2c_8m'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][escalas_2c_8m]" value="{{ $data['cantidades']['escalas_2c_8m'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </td>
        </tr>
        <tr class="bg-sky-100">
            <td colspan="6" class="p-1 border border-slate-300 bg-sky-100"></td>
            <td colspan="4" class="p-1 border border-slate-300 bg-teal-100 text-center"><div class="text-xs font-bold">Escala plegable</div></td>
            <td colspan="2" class="p-1 border border-slate-300 text-center">
                @if($readonly)
                    <div class="font-semibold">{{ $data['cantidades']['escalas_plegable'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][escalas_plegable]" value="{{ $data['cantidades']['escalas_plegable'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </td>
        </tr>

        {{-- Set lona cubre pilares --}}
        <tr class="bg-sky-100">
            <td colspan="4" class="p-1 border border-slate-300 font-bold text-sm">Set lona cubre pilares</td>
            <td colspan="4" class="p-1 border border-slate-300 bg-teal-100 text-center"><div class="text-xs font-bold">Rozón</div></td>
            <td colspan="4" class="p-1 border border-slate-300 bg-teal-100 text-center"><div class="text-xs font-bold">Chuzo</div></td>
        </tr>
        <tr class="bg-sky-100">
            <td colspan="4" class="p-1 border border-slate-300 bg-sky-100"></td>
            <td colspan="4" class="p-1 border border-slate-300 text-center">
                @if($readonly)
                    <div class="font-semibold">{{ $data['cantidades']['lona_pilares_rozon'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][lona_pilares_rozon]" value="{{ $data['cantidades']['lona_pilares_rozon'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </td>
            <td colspan="4" class="p-1 border border-slate-300 text-center">
                @if($readonly)
                    <div class="font-semibold">{{ $data['cantidades']['lona_pilares_chuzo'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][lona_pilares_chuzo]" value="{{ $data['cantidades']['lona_pilares_chuzo'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </td>
        </tr>

        {{-- Bicheros --}}
        <tr class="bg-sky-100">
            <td colspan="4" class="p-1 border border-slate-300 font-bold text-sm">Bicheros</td>
            <td colspan="4" class="p-1 border border-slate-300 bg-teal-100 text-center"><div class="text-xs font-bold">Conos</div></td>
            <td colspan="4" class="p-1 border border-slate-300 bg-teal-100 text-center"><div class="text-xs font-bold">Halligan</div></td>
        </tr>
        <tr class="bg-sky-100">
            <td colspan="4" class="p-1 border border-slate-300 bg-sky-100"></td>
            <td colspan="4" class="p-1 border border-slate-300 text-center">
                @if($readonly)
                    <div class="font-semibold">{{ $data['cantidades']['bicheros_conos'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][bicheros_conos]" value="{{ $data['cantidades']['bicheros_conos'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </td>
            <td colspan="4" class="p-1 border border-slate-300 text-center">
                @if($readonly)
                    <div class="font-semibold">{{ $data['cantidades']['bicheros_halligan'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][bicheros_halligan]" value="{{ $data['cantidades']['bicheros_halligan'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </td>
        </tr>

        {{-- Focos LED --}}
        <tr class="bg-sky-100">
            <td colspan="4" class="p-1 border border-slate-300 font-bold text-sm">Focos LED con cable y trípode</td>
            <td colspan="4" class="p-1 border border-slate-300 bg-teal-100 text-center"><div class="text-xs font-bold">Hacha suela</div></td>
            <td colspan="4" class="p-1 border border-slate-300 bg-teal-100 text-center"><div class="text-xs font-bold">TNT</div></td>
        </tr>
        <tr class="bg-sky-100">
            <td colspan="4" class="p-1 border border-slate-300 bg-sky-100"></td>
            <td colspan="4" class="p-1 border border-slate-300 text-center">
                @if($readonly)
                    <div class="font-semibold">{{ $data['cantidades']['focos_led_hacha_suela'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][focos_led_hacha_suela]" value="{{ $data['cantidades']['focos_led_hacha_suela'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </td>
            <td colspan="4" class="p-1 border border-slate-300 text-center">
                @if($readonly)
                    <div class="font-semibold">{{ $data['cantidades']['focos_led_tnt'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][focos_led_tnt]" value="{{ $data['cantidades']['focos_led_tnt'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </td>
        </tr>

        {{-- Carrete cable --}}
        <tr class="bg-sky-100">
            <td colspan="4" class="p-1 border border-slate-300 font-bold text-sm">Carrete cable eléctrico</td>
            <td colspan="4" class="p-1 border border-slate-300 bg-teal-100 text-center"><div class="text-xs font-bold">Hacha bombero</div></td>
            <td colspan="4" class="p-1 border border-slate-300 bg-teal-100 text-center"><div class="text-xs font-bold">Halligan</div></td>
        </tr>
        <tr class="bg-sky-100">
            <td colspan="4" class="p-1 border border-slate-300 bg-sky-100"></td>
            <td colspan="4" class="p-1 border border-slate-300 text-center">
                @if($readonly)
                    <div class="font-semibold">{{ $data['cantidades']['carrete_hacha_bombero'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][carrete_hacha_bombero]" value="{{ $data['cantidades']['carrete_hacha_bombero'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </td>
            <td colspan="4" class="p-1 border border-slate-300 text-center">
                @if($readonly)
                    <div class="font-semibold">{{ $data['cantidades']['carrete_halligan'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][carrete_halligan]" value="{{ $data['cantidades']['carrete_halligan'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </td>
        </tr>

        {{-- Cadenas Holmatro --}}
        <tr class="bg-sky-100">
            <td colspan="4" class="p-1 border border-slate-300 font-bold text-sm">Cadenas y puntas Holmatro</td>
            <td colspan="4" class="p-1 border border-slate-300 bg-teal-100 text-center"><div class="text-xs font-bold">Extintor de agua</div></td>
            <td colspan="4" class="p-1 border border-slate-300 bg-teal-100 text-center"><div class="text-xs font-bold">Pitón monitor</div></td>
        </tr>
        <tr class="bg-sky-100">
            <td colspan="4" class="p-1 border border-slate-300 bg-sky-100"></td>
            <td colspan="4" class="p-1 border border-slate-300 text-center">
                @if($readonly)
                    <div class="font-semibold">{{ $data['cantidades']['cadenas_extintor_agua'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][cadenas_extintor_agua]" value="{{ $data['cantidades']['cadenas_extintor_agua'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </td>
            <td colspan="4" class="p-1 border border-slate-300 text-center">
                @if($readonly)
                    <div class="font-semibold">{{ $data['cantidades']['cadenas_piton_monitor'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][cadenas_piton_monitor]" value="{{ $data['cantidades']['cadenas_piton_monitor'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </td>
        </tr>

        {{-- Corta parabrisas --}}
        <tr class="bg-sky-100">
            <td colspan="3" class="p-1 border border-slate-300 font-bold text-sm">Corta parabrisas manual</td>
            <td colspan="3" class="p-1 border border-slate-300 bg-teal-100 text-center"><div class="text-xs font-bold">Force</div></td>
            <td colspan="2" class="p-1 border border-slate-300 text-center">
                @if($readonly)
                    <div class="font-semibold">{{ $data['cantidades']['corta_parabrisas_force'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][corta_parabrisas_force]" value="{{ $data['cantidades']['corta_parabrisas_force'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </td>
            <td colspan="2" class="p-1 border border-slate-300 bg-teal-100 text-center"><div class="text-xs font-bold">Cámara Termal</div></td>
            <td colspan="2" class="p-1 border border-slate-300 text-center">
                @if($readonly)
                    <div class="font-semibold">{{ $data['cantidades']['corta_parabrisas_camara'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][corta_parabrisas_camara]" value="{{ $data['cantidades']['corta_parabrisas_camara'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </td>
        </tr>
        <tr class="bg-sky-100">
            <td colspan="9" class="p-1 border border-slate-300 bg-sky-100"></td>
            <td colspan="3" class="p-1 border border-slate-300 bg-teal-100 text-center"><div class="text-xs font-bold">Bomba Espalda</div></td>
        </tr>
        <tr class="bg-sky-100">
            <td colspan="9" class="p-1 border border-slate-300 bg-sky-100"></td>
            <td colspan="3" class="p-1 border border-slate-300 text-center">
                @if($readonly)
                    <div class="font-semibold">{{ $data['cantidades']['corta_parabrisas_bomba'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][corta_parabrisas_bomba]" value="{{ $data['cantidades']['corta_parabrisas_bomba'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </td>
        </tr>
        </table>
    </div>

    {{-- Material del Techo --}}
    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-2 rounded mt-2">
        <div class="font-bold text-sm">Material del Techo</div>
        <div class="text-xs text-slate-600 italic">(4 palas, 2 pasatiras, 2 McLeod, 3 rastrillos cegadores, 2 Chorizos, 1 Filtro de aspiración)</div>
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
        </table>
    </div>
</div>
