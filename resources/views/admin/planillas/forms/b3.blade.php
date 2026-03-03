@php
    $data = $data ?? [];
    $readonly = $readonly ?? false;
@endphp

<div class="space-y-2">
    {{-- Header --}}
    <div class="bg-sky-100 rounded-t-lg border border-teal-900/20 p-3 flex justify-between items-center">
        <div class="text-xs font-black uppercase tracking-widest text-slate-900">PLANILLA DE REVISIÓN DE NIVELES</div>
        <div class="text-sm font-black text-slate-900">B - 3</div>
    </div>

    {{-- CABINA --}}
    <div class="overflow-x-auto">
        <table class="w-full border-collapse text-sm" style="min-width: 800px;">
        <tr class="bg-teal-800 text-white">
            <th colspan="12" class="p-2 text-xs font-black uppercase tracking-widest text-left border border-teal-900">CABINA</th>
        </tr>
        <tr class="bg-sky-100">
            <td colspan="12" class="p-1 text-xs font-bold text-center bg-teal-700 text-white border border-teal-800">ERA MSA G 1</td>
        </tr>
        <tr class="bg-sky-100">
            @foreach(['1', '2', '3', '4', '5', '6', '7', 'OBAC (M7)', 'Radios', 'Motorola', '1', '2'] as $label)
                <th class="p-1 text-xs text-center font-bold border border-teal-600 bg-teal-100 text-slate-800">{{ $label }}</th>
            @endforeach
        </tr>
        <tr>
            @for($i = 1; $i <= 7; $i++)
                <td class="p-1 border border-slate-300 text-center">
                    @if($readonly)
                        <div class="font-semibold">{{ $data['cabina']['era_msa_g1']['num_'.$i] ?? '—' }}</div>
                    @else
                        <input type="text" name="data[cabina][era_msa_g1][num_{{ $i }}]" value="{{ $data['cabina']['era_msa_g1']['num_'.$i] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                    @endif
                </td>
            @endfor
            <td class="p-1 border border-slate-300 text-center">
                @if($readonly)
                    <div class="font-semibold">{{ $data['cabina']['era_msa_g1']['obac_m7'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cabina][era_msa_g1][obac_m7]" value="{{ $data['cabina']['era_msa_g1']['obac_m7'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </td>
            <td class="p-1 border border-slate-300 text-center">
                @if($readonly)
                    <div class="font-semibold">{{ $data['cabina']['era_msa_g1']['radios'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cabina][era_msa_g1][radios]" value="{{ $data['cabina']['era_msa_g1']['radios'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </td>
            <td class="p-1 border border-slate-300 bg-sky-100"></td>
            <td class="p-1 border border-slate-300 text-center">
                @if($readonly)
                    <div class="font-semibold">{{ $data['cabina']['era_msa_g1']['radio_1'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cabina][era_msa_g1][radio_1]" value="{{ $data['cabina']['era_msa_g1']['radio_1'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </td>
            <td class="p-1 border border-slate-300 text-center">
                @if($readonly)
                    <div class="font-semibold">{{ $data['cabina']['era_msa_g1']['radio_2'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cabina][era_msa_g1][radio_2]" value="{{ $data['cabina']['era_msa_g1']['radio_2'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </td>
        </tr>

        {{-- Linterna NIGHTSTICK --}}
        <tr>
            <td colspan="3" class="p-1 border border-slate-300 bg-sky-100 font-bold text-sm">Linterna NIGHTSTICK XPR-5568</td>
            <td colspan="3" class="p-1 border border-slate-300 bg-sky-100">
                <div class="text-xs">Funcionamiento</div>
                @if($readonly)
                    <div class="font-semibold text-center">{{ ($data['cabina']['linterna_nightstick']['funcionamiento'] ?? '') === 'si' ? 'Sí' : (($data['cabina']['linterna_nightstick']['funcionamiento'] ?? '') === 'no' ? 'No' : '—') }}</div>
                @else
                    <select name="data[cabina][linterna_nightstick][funcionamiento]" class="w-full text-sm border rounded p-1">
                        <option value=""></option>
                        <option value="si" {{ ($data['cabina']['linterna_nightstick']['funcionamiento'] ?? '') === 'si' ? 'selected' : '' }}>Sí</option>
                        <option value="no" {{ ($data['cabina']['linterna_nightstick']['funcionamiento'] ?? '') === 'no' ? 'selected' : '' }}>No</option>
                    </select>
                @endif
            </td>
            <td colspan="3" class="p-1 border border-slate-300 bg-sky-100">
                <div class="text-xs">Cantidad:</div>
                @if($readonly)
                    <div class="font-semibold text-center">{{ $data['cabina']['linterna_nightstick']['cantidad'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cabina][linterna_nightstick][cantidad]" value="{{ $data['cabina']['linterna_nightstick']['cantidad'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </td>
            <td colspan="3" class="p-1 border border-slate-300 bg-sky-100">
                <div class="text-xs">Línea de vida B & R</div>
                @if($readonly)
                    <div class="font-semibold text-center">{{ $data['cabina']['linterna_nightstick']['linea_vida'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cabina][linterna_nightstick][linea_vida]" value="{{ $data['cabina']['linterna_nightstick']['linea_vida'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </td>
        </tr>

        {{-- Tablet y Maleta --}}
        <tr>
            <td colspan="6" class="p-1 border border-slate-300 bg-sky-100 font-bold text-sm">Tablet unidad B-3 y Cargador</td>
            <td colspan="6" class="p-1 border border-slate-300 bg-sky-100 font-bold text-sm">Maleta SCI</td>
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
                <th colspan="5" class="p-1 text-xs font-bold text-center border border-teal-800">¿Funciona?</th>
                <th class="p-1 text-xs font-bold text-center border border-teal-800 w-12">SÍ</th>
                <th class="p-1 text-xs font-bold text-center border border-teal-800 w-12">NO</th>
                <th colspan="5" class="p-1 text-xs font-bold text-center border border-teal-800">Novedades</th>
            </tr>

        @php
            $herramientas = [
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
            $highlightTools = ['detector_gas', 'motosierra_cutter', 'taladro_makita', 'motobomba_rosenbauer', 'aspirador_nautilus', 'motoamoladora_m14'];
        @endphp

        @foreach($herramientas as $key => $label)
            <tr class="{{ in_array($key, $highlightTools) ? 'bg-yellow-50' : 'bg-sky-50' }}">
                <td colspan="5" class="p-1 border border-slate-300 font-semibold {{ in_array($key, $highlightTools) ? 'bg-yellow-100' : '' }}">{{ $label }}</td>
                <td class="p-1 border border-slate-300 text-center">
                    @if($readonly)
                        {{ ($data['herramientas'][$key]['funciona'] ?? '') === 'si' ? '✓' : '' }}
                    @else
                        <input type="radio" name="data[herramientas][{{ $key }}][funciona]" value="si" {{ ($data['herramientas'][$key]['funciona'] ?? '') === 'si' ? 'checked' : '' }} class="w-4 h-4 text-teal-600">
                    @endif
                </td>
                <td class="p-1 border border-slate-300 text-center">
                    @if($readonly)
                        {{ ($data['herramientas'][$key]['funciona'] ?? '') === 'no' ? '✗' : '' }}
                    @else
                        <input type="radio" name="data[herramientas][{{ $key }}][funciona]" value="no" {{ ($data['herramientas'][$key]['funciona'] ?? '') === 'no' ? 'checked' : '' }} class="w-4 h-4 text-red-600">
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

    {{-- Indique la cantidad --}}
    <div class="overflow-x-auto">
        <table class="w-full border-collapse text-sm mt-2" style="min-width: 800px;">
            <tr class="bg-teal-800 text-white">
                <th colspan="12" class="p-2 text-xs font-black uppercase tracking-widest text-center border border-teal-900">Indique la cantidad</th>
            </tr>

        {{-- Mochila de Trauma --}}
        <tr class="bg-sky-100">
            <td colspan="3" class="p-1 border border-slate-300 font-bold text-sm">Mochila de Trauma y Cilindro O2</td>
            <td colspan="3" class="p-1 border border-slate-300 bg-teal-100">
                <div class="text-xs font-bold text-center">NIVEL O.</div>
                @if($readonly)
                    <div class="font-semibold text-center">{{ $data['cantidades']['mochila_trauma']['nivelo'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][mochila_trauma][nivelo]" value="{{ $data['cantidades']['mochila_trauma']['nivelo'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </td>
            <td colspan="3" class="p-1 border border-slate-300 bg-teal-100">
                <div class="text-xs font-bold text-center">Kit Inmovilización completo</div>
                @if($readonly)
                    <div class="font-semibold text-center">{{ $data['cantidades']['mochila_trauma']['kit_inmovilizacion'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][mochila_trauma][kit_inmovilizacion]" value="{{ $data['cantidades']['mochila_trauma']['kit_inmovilizacion'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </td>
            <td colspan="3" class="p-1 border border-slate-300 bg-teal-100">
                <div class="text-xs font-bold text-center">Conos</div>
                @if($readonly)
                    <div class="font-semibold text-center">{{ $data['cantidades']['mochila_trauma']['conos'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][mochila_trauma][conos]" value="{{ $data['cantidades']['mochila_trauma']['conos'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </td>
        </tr>

        {{-- MANGUERAS --}}
        <tr class="bg-teal-700 text-white">
            <td colspan="12" class="p-1 text-xs font-bold text-center">MANGUERAS</td>
        </tr>
        <tr class="bg-sky-100">
            <td class="p-1 border border-slate-300 bg-teal-100 text-center"><div class="text-xs font-bold">52</div></td>
            <td class="p-1 border border-slate-300 text-center">
                @if($readonly)
                    <div class="font-semibold">{{ $data['cantidades']['mangueras_52'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][mangueras_52]" value="{{ $data['cantidades']['mangueras_52'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </td>
            <td class="p-1 border border-slate-300 bg-teal-100 text-center"><div class="text-xs font-bold">75</div></td>
            <td class="p-1 border border-slate-300 text-center">
                @if($readonly)
                    <div class="font-semibold">{{ $data['cantidades']['mangueras_75'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][mangueras_75]" value="{{ $data['cantidades']['mangueras_75'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
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
            <td class="p-1 border border-slate-300 bg-teal-100 text-center"><div class="text-xs font-bold">Armada Base</div></td>
            <td class="p-1 border border-slate-300 text-center">
                @if($readonly)
                    <div class="font-semibold">{{ $data['cantidades']['mangueras_armada_base'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][mangueras_armada_base]" value="{{ $data['cantidades']['mangueras_armada_base'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </td>
            <td colspan="4" class="p-1 border border-slate-300"></td>
        </tr>

        {{-- Paquete Circular --}}
        <tr>
            <td colspan="3" class="p-1 border border-slate-300 bg-sky-100 font-bold">Paquete Circular</td>
            <td colspan="3" class="p-1 border border-slate-300 bg-sky-100">
                <div class="text-xs">Herraduras</div>
                @if($readonly)
                    <div class="font-semibold text-center">{{ $data['cantidades']['paquete_herraduras'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][paquete_herraduras]" value="{{ $data['cantidades']['paquete_herraduras'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </td>
            <td colspan="6" class="p-1 border border-slate-300 bg-sky-100">
                <div class="text-xs">Carretes alimentación (cantidad tiras)</div>
                @if($readonly)
                    <div class="font-semibold text-center">{{ $data['cantidades']['paquete_carretes'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][paquete_carretes]" value="{{ $data['cantidades']['paquete_carretes'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </td>
        </tr>

        {{-- ATAQUES --}}
        <tr class="bg-teal-700 text-white">
            <td colspan="12" class="p-1 text-xs font-bold text-center">ATAQUES</td>
        </tr>
        <tr class="bg-sky-100">
            <td class="p-1 border border-slate-300 bg-teal-100 text-center"><div class="text-xs font-bold">52</div></td>
            <td class="p-1 border border-slate-300 text-center">
                @if($readonly)
                    <div class="font-semibold">{{ $data['cantidades']['ataques_52'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][ataques_52]" value="{{ $data['cantidades']['ataques_52'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </td>
            <td class="p-1 border border-slate-300 bg-teal-100 text-center"><div class="text-xs font-bold">75</div></td>
            <td class="p-1 border border-slate-300 text-center">
                @if($readonly)
                    <div class="font-semibold">{{ $data['cantidades']['ataques_75'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][ataques_75]" value="{{ $data['cantidades']['ataques_75'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </td>
            <td colspan="4" class="p-1 border border-slate-300 bg-teal-100 text-center">
                <div class="text-xs font-bold">Cilindros de recambio MSA</div>
            </td>
            <td colspan="4" class="p-1 border border-slate-300 text-center">
                @if($readonly)
                    <div class="font-semibold">{{ $data['cantidades']['ataques_cilindros_msa'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][ataques_cilindros_msa]" value="{{ $data['cantidades']['ataques_cilindros_msa'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </td>
        </tr>

        {{-- TRASPASOS --}}
        <tr class="bg-teal-700 text-white">
            <td colspan="12" class="p-1 text-xs font-bold text-center">TRASPASOS</td>
        </tr>
        <tr class="bg-sky-100">
            <td class="p-1 border border-slate-300 bg-teal-100 text-center"><div class="text-xs font-bold">Llave de grifo</div></td>
            <td class="p-1 border border-slate-300 text-center">
                @if($readonly)
                    <div class="font-semibold">{{ $data['cantidades']['traspasos_llave_grifo'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][traspasos_llave_grifo]" value="{{ $data['cantidades']['traspasos_llave_grifo'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </td>
            <td colspan="3" class="p-1 border border-slate-300 bg-teal-100 text-center"><div class="text-xs font-bold">Traspaso de grifo</div></td>
            <td colspan="7" class="p-1 border border-slate-300 text-center">
                @if($readonly)
                    <div class="font-semibold">{{ $data['cantidades']['traspasos_traspaso_grifo'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][traspasos_traspaso_grifo]" value="{{ $data['cantidades']['traspasos_traspaso_grifo'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </td>
        </tr>
        {{-- LLAVES DE COPLA --}}
        <tr class="bg-teal-700 text-white">
            <td colspan="12" class="p-1 text-xs font-bold text-center">LLAVES DE COPLA</td>
        </tr>
        <tr class="bg-sky-100">
            <td colspan="2" class="p-1 border border-slate-300 bg-teal-100 text-center"><div class="text-xs font-bold">Llave de piso</div></td>
            <td colspan="4" class="p-1 border border-slate-300 text-center">
                @if($readonly)
                    <div class="font-semibold">{{ $data['cantidades']['llaves_llave_piso'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][llaves_llave_piso]" value="{{ $data['cantidades']['llaves_llave_piso'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </td>
            <td colspan="2" class="p-1 border border-slate-300 bg-teal-100 text-center"><div class="text-xs font-bold">Manguera 75 alimentación</div></td>
            <td colspan="4" class="p-1 border border-slate-300 text-center">
                @if($readonly)
                    <div class="font-semibold">{{ $data['cantidades']['llaves_manguera_75'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][llaves_manguera_75]" value="{{ $data['cantidades']['llaves_manguera_75'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </td>
        </tr>

        {{-- Escalas de Techo --}}
        <tr class="bg-sky-100">
            <td colspan="2" class="p-1 border border-slate-300 font-bold">Escalas de Techo</td>
            <td colspan="2" class="p-1 border border-slate-300 text-center">
                <div class="text-xs">Puntas Taladro</div>
                @if($readonly)
                    <div class="font-semibold">{{ $data['cantidades']['escalas_puntas_taladro'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][escalas_puntas_taladro]" value="{{ $data['cantidades']['escalas_puntas_taladro'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </td>
            <td colspan="8" class="p-1 border border-slate-300 text-center">
                <div class="text-xs">Napoleón 30"</div>
                @if($readonly)
                    <div class="font-semibold">{{ $data['cantidades']['escalas_napoleon'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][escalas_napoleon]" value="{{ $data['cantidades']['escalas_napoleon'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </td>
        </tr>

        {{-- Combo de 8 libras --}}
        <tr class="bg-sky-100">
            <td colspan="2" class="p-1 border border-slate-300 font-bold">Combo de 8 libras</td>
            <td colspan="2" class="p-1 border border-slate-300 text-center">
                <div class="text-xs">Bidón Motosierra</div>
                @if($readonly)
                    <div class="font-semibold">{{ $data['cantidades']['combo_bidon_motosierra'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][combo_bidon_motosierra]" value="{{ $data['cantidades']['combo_bidon_motosierra'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </td>
            <td colspan="8" class="p-1 border border-slate-300 text-center">
                <div class="text-xs">Hacha bombero</div>
                @if($readonly)
                    <div class="font-semibold">{{ $data['cantidades']['combo_hacha_bombero'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][combo_hacha_bombero]" value="{{ $data['cantidades']['combo_hacha_bombero'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </td>
        </tr>

        {{-- Bicheros --}}
        <tr class="bg-sky-100">
            <td colspan="2" class="p-1 border border-slate-300 font-bold">Bicheros</td>
            <td colspan="2" class="p-1 border border-slate-300 text-center">
                <div class="text-xs">Barretilla</div>
                @if($readonly)
                    <div class="font-semibold">{{ $data['cantidades']['bicheros_barretilla'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][bicheros_barretilla]" value="{{ $data['cantidades']['bicheros_barretilla'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </td>
            <td colspan="8" class="p-1 border border-slate-300 text-center">
                <div class="text-xs">Halligan</div>
                @if($readonly)
                    <div class="font-semibold">{{ $data['cantidades']['bicheros_halligan'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][bicheros_halligan]" value="{{ $data['cantidades']['bicheros_halligan'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </td>
        </tr>

        {{-- Caja de Herramientas --}}
        <tr class="bg-sky-100">
            <td colspan="2" class="p-1 border border-slate-300 font-bold">Caja de Herramientas</td>
            <td colspan="2" class="p-1 border border-slate-300 text-center">
                <div class="text-xs">Hacha suela</div>
                @if($readonly)
                    <div class="font-semibold">{{ $data['cantidades']['caja_hacha_suela'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][caja_hacha_suela]" value="{{ $data['cantidades']['caja_hacha_suela'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </td>
            <td colspan="8" class="p-1 border border-slate-300 text-center">
                <div class="text-xs">TNT</div>
                @if($readonly)
                    <div class="font-semibold">{{ $data['cantidades']['caja_tnt'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][caja_tnt]" value="{{ $data['cantidades']['caja_tnt'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </td>
        </tr>

        {{-- Gremio --}}
        <tr class="bg-sky-100">
            <td colspan="2" class="p-1 border border-slate-300 font-bold">Gremio</td>
            <td colspan="2" class="p-1 border border-slate-300 text-center">
                <div class="text-xs">Kit Entrada Forzada</div>
                @if($readonly)
                    <div class="font-semibold">{{ $data['cantidades']['gremio_kit_entrada'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][gremio_kit_entrada]" value="{{ $data['cantidades']['gremio_kit_entrada'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </td>
            <td colspan="8" class="p-1 border border-slate-300 text-center">
                <div class="text-xs">Trifurca</div>
                @if($readonly)
                    <div class="font-semibold">{{ $data['cantidades']['gremio_trifurca'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][gremio_trifurca]" value="{{ $data['cantidades']['gremio_trifurca'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </td>
        </tr>

        {{-- Filtro de Aspiración --}}
        <tr class="bg-sky-100">
            <td colspan="2" class="p-1 border border-slate-300 font-bold">Filtro de Aspiración</td>
            <td colspan="2" class="p-1 border border-slate-300 text-center">
                <div class="text-xs">Siamesa</div>
                @if($readonly)
                    <div class="font-semibold">{{ $data['cantidades']['filtro_siamesa'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][filtro_siamesa]" value="{{ $data['cantidades']['filtro_siamesa'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </td>
            <td colspan="8" class="p-1 border border-slate-300 text-center">
                <div class="text-xs">Pitones de 50</div>
                @if($readonly)
                    <div class="font-semibold">{{ $data['cantidades']['filtro_pitones_50'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][filtro_pitones_50]" value="{{ $data['cantidades']['filtro_pitones_50'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </td>
        </tr>

        {{-- Kit quemados --}}
        <tr class="bg-sky-100">
            <td colspan="2" class="p-1 border border-slate-300 font-bold">Kit quemados / Asistente de Trauma</td>
            <td colspan="2" class="p-1 border border-slate-300 text-center">
                <div class="text-xs">Pitón DIN 50</div>
                @if($readonly)
                    <div class="font-semibold">{{ $data['cantidades']['kit_piton_din_50'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][kit_piton_din_50]" value="{{ $data['cantidades']['kit_piton_din_50'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </td>
            <td colspan="8" class="p-1 border border-slate-300 text-center">
                <div class="text-xs">Pitones de 70</div>
                @if($readonly)
                    <div class="font-semibold">{{ $data['cantidades']['kit_pitones_70'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][kit_pitones_70]" value="{{ $data['cantidades']['kit_pitones_70'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </td>
        </tr>

        {{-- Bidón de bencina --}}
        <tr class="bg-sky-100">
            <td colspan="2" class="p-1 border border-slate-300 font-bold">Bidón de bencina Verde 10L</td>
            <td colspan="2" class="p-1 border border-slate-300 text-center">
                <div class="text-xs">Chorizos</div>
                @if($readonly)
                    <div class="font-semibold">{{ $data['cantidades']['bidon_chorizos'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][bidon_chorizos]" value="{{ $data['cantidades']['bidon_chorizos'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </td>
            <td colspan="8" class="p-1 border border-slate-300 text-center">
                <div class="text-xs">Pasatiras</div>
                @if($readonly)
                    <div class="font-semibold">{{ $data['cantidades']['bidon_pasatiras'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][bidon_pasatiras]" value="{{ $data['cantidades']['bidon_pasatiras'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </td>
        </tr>

        {{-- Extintor de agua --}}
        <tr class="bg-sky-100">
            <td colspan="2" class="p-1 border border-slate-300 font-bold">Extintor de agua</td>
            <td colspan="2" class="p-1 border border-slate-300 text-center">
                <div class="text-xs">Carretes de extensión</div>
                @if($readonly)
                    <div class="font-semibold">{{ $data['cantidades']['extintor_carretes'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][extintor_carretes]" value="{{ $data['cantidades']['extintor_carretes'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </td>
            <td colspan="8" class="p-1 border border-slate-300 text-center">
                <div class="text-xs">Pitón de espuma</div>
                @if($readonly)
                    <div class="font-semibold">{{ $data['cantidades']['extintor_piton_espuma'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][extintor_piton_espuma]" value="{{ $data['cantidades']['extintor_piton_espuma'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </td>
        </tr>
        </table>
    </div>

    {{-- Material Forestal --}}
    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-2 rounded mt-2">
        <div class="font-bold text-sm">Material Forestal</div>
        <div class="text-xs text-slate-600 italic">(1 pala, 1 rozón, 2 rastrillos cegadores, 1 McLeod, 2 bombas de espalda)</div>
    </div>

    {{-- Bolso de altura --}}
    <div class="bg-green-100 border-l-4 border-green-400 p-2 rounded mt-2">
        <div class="font-bold text-sm">Bolso de altura</div>
        <div class="text-xs text-slate-600 italic">(Tira de 38mm 3 metros, Pitón POK, gemelo, reducción, cuerda utilitaria)</div>
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
                <div class="text-xs font-bold">Nivel Combustible Bidón (Naranjo)</div>
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
