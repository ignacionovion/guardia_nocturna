@php
    $data = $data ?? [];
    $readonly = $readonly ?? false;
@endphp

<div class="space-y-4">
    {{-- Header --}}
    <div class="bg-sky-100 rounded-xl border border-teal-900/20 p-4">
        <div class="flex justify-between items-center">
            <div class="text-xs font-black uppercase tracking-widest text-slate-900">PLANILLA DE REVISIÓN DE NIVELES</div>
            <div class="text-sm font-black text-slate-900">B-3</div>
        </div>
    </div>

    {{-- CABINA --}}
    <div class="bg-teal-800 text-white p-2 text-xs font-black uppercase tracking-widest">CABINA</div>
    
    <div class="space-y-2">
        {{-- ERA MSA G 1 --}}
        <div class="bg-teal-700 text-white p-1 text-xs font-bold text-center">ERA MSA G 1</div>
        <div class="grid grid-cols-12 gap-1 bg-white p-2 rounded border border-slate-200">
            @for($i = 1; $i <= 7; $i++)
                <div>
                    <div class="text-xs text-slate-500 text-center font-bold">{{ $i }}</div>
                    @if($readonly)
                        <div class="font-semibold text-center">{{ $data['cabina']['era_msa_g1']['num_'.$i] ?? '—' }}</div>
                    @else
                        <input type="text" name="data[cabina][era_msa_g1][num_{{ $i }}]" value="{{ $data['cabina']['era_msa_g1']['num_'.$i] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                    @endif
                </div>
            @endfor
            <div>
                <div class="text-xs text-slate-500 text-center font-bold">OBAC (M7)</div>
                @if($readonly)
                    <div class="font-semibold text-center">{{ $data['cabina']['era_msa_g1']['obac_m7'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cabina][era_msa_g1][obac_m7]" value="{{ $data['cabina']['era_msa_g1']['obac_m7'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </div>
            <div>
                <div class="text-xs text-slate-500 text-center font-bold">Radios</div>
                @if($readonly)
                    <div class="font-semibold text-center">{{ $data['cabina']['era_msa_g1']['radios'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cabina][era_msa_g1][radios]" value="{{ $data['cabina']['era_msa_g1']['radios'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </div>
            <div>
                <div class="text-xs text-slate-500 text-center font-bold">1</div>
                @if($readonly)
                    <div class="font-semibold text-center">{{ $data['cabina']['era_msa_g1']['radio_1'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cabina][era_msa_g1][radio_1]" value="{{ $data['cabina']['era_msa_g1']['radio_1'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </div>
            <div>
                <div class="text-xs text-slate-500 text-center font-bold">2</div>
                @if($readonly)
                    <div class="font-semibold text-center">{{ $data['cabina']['era_msa_g1']['radio_2'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cabina][era_msa_g1][radio_2]" value="{{ $data['cabina']['era_msa_g1']['radio_2'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </div>
        </div>
        <div class="text-right text-xs text-slate-500 -mt-1">Motorola</div>

        {{-- Linterna NIGHTSTICK --}}
        <div class="grid grid-cols-12 gap-1 items-center bg-white p-2 rounded border border-slate-200">
            <div class="col-span-4 font-bold text-sm">Linterna NIGHTSTICK XPR-5568</div>
            <div class="col-span-2">
                <div class="text-xs text-slate-500">Funcionamiento</div>
                @if($readonly)
                    <div class="font-semibold">{{ ($data['cabina']['linterna_nightstick']['funcionamiento'] ?? '') === 'si' ? 'Sí' : (($data['cabina']['linterna_nightstick']['funcionamiento'] ?? '') === 'no' ? 'No' : '—') }}</div>
                @else
                    <select name="data[cabina][linterna_nightstick][funcionamiento]" class="w-full text-sm border rounded p-1">
                        <option value=""></option>
                        <option value="si" {{ ($data['cabina']['linterna_nightstick']['funcionamiento'] ?? '') === 'si' ? 'selected' : '' }}>Sí</option>
                        <option value="no" {{ ($data['cabina']['linterna_nightstick']['funcionamiento'] ?? '') === 'no' ? 'selected' : '' }}>No</option>
                    </select>
                @endif
            </div>
            <div class="col-span-2">
                <div class="text-xs text-slate-500">Cantidad:</div>
                @if($readonly)
                    <div class="font-semibold">{{ $data['cabina']['linterna_nightstick']['cantidad'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cabina][linterna_nightstick][cantidad]" value="{{ $data['cabina']['linterna_nightstick']['cantidad'] ?? '' }}" class="w-full text-sm border rounded p-1">
                @endif
            </div>
            <div class="col-span-4">
                <div class="text-xs text-slate-500">Línea de vida B & R</div>
                @if($readonly)
                    <div class="font-semibold">{{ $data['cabina']['linterna_nightstick']['linea_vida'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cabina][linterna_nightstick][linea_vida]" value="{{ $data['cabina']['linterna_nightstick']['linea_vida'] ?? '' }}" class="w-full text-sm border rounded p-1">
                @endif
            </div>
        </div>

        {{-- Tablet y Maleta --}}
        <div class="grid grid-cols-2 gap-4 bg-white p-2 rounded border border-slate-200">
            <div class="font-bold text-sm">Tablet unidad B-3 y Cargador</div>
            <div class="font-bold text-sm">Maleta SCI</div>
        </div>
    </div>

    {{-- Check List Herramientas --}}
    <div class="bg-teal-800 text-white p-2 text-xs font-black uppercase tracking-widest text-center">Check list Herramientas</div>
    
    <div class="bg-sky-100 p-2">
        <div class="grid grid-cols-12 gap-1 text-xs font-bold text-center mb-2">
            <div class="col-span-5"></div>
            <div class="col-span-1 bg-teal-700 text-white p-1 rounded">¿Funciona?</div>
            <div class="col-span-1 bg-teal-700 text-white p-1 rounded">SÍ</div>
            <div class="col-span-1 bg-teal-700 text-white p-1 rounded">NO</div>
            <div class="col-span-4 bg-teal-700 text-white p-1 rounded">Novedades</div>
        </div>
        
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
        @endphp
        
        @foreach($herramientas as $key => $label)
            <div class="grid grid-cols-12 gap-1 items-center bg-white p-1 rounded border border-slate-200 mb-1 {{ in_array($key, ['detector_gas', 'motosierra_cutter', 'taladro_makita', 'motobomba_rosenbauer', 'aspirador_nautilus', 'motoamoladora_m14']) ? 'bg-yellow-50' : '' }}">
                <div class="col-span-5 font-semibold text-sm {{ in_array($key, ['detector_gas', 'motosierra_cutter', 'taladro_makita', 'motobomba_rosenbauer', 'aspirador_nautilus', 'motoamoladora_m14']) ? 'bg-yellow-100 p-1 rounded' : '' }}">{{ $label }}</div>
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
                <div class="col-span-4">
                    @if($readonly)
                        <div class="font-semibold text-sm">{{ $data['herramientas'][$key]['novedades'] ?? '—' }}</div>
                    @else
                        <input type="text" name="data[herramientas][{{ $key }}][novedades]" value="{{ $data['herramientas'][$key]['novedades'] ?? '' }}" class="w-full text-sm border rounded p-1">
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    {{-- Indique la cantidad --}}
    <div class="bg-teal-800 text-white p-2 text-xs font-black uppercase tracking-widest text-center">Indique la cantidad</div>
    
    <div class="space-y-2 bg-sky-100 p-2">
        {{-- Mochila de Trauma --}}
        <div class="bg-white p-2 rounded border border-slate-200">
            <div class="font-bold text-sm bg-teal-100 p-1 rounded mb-2">Mochila de Trauma y Cilindro O2</div>
            <div class="grid grid-cols-3 gap-2">
                <div>
                    <div class="text-xs text-slate-500 text-center font-bold bg-teal-100 p-1 rounded">NIVEL O.</div>
                    @if($readonly)
                        <div class="font-semibold text-center">{{ $data['cantidades']['mochila_trauma']['nivelo'] ?? '—' }}</div>
                    @else
                        <input type="text" name="data[cantidades][mochila_trauma][nivelo]" value="{{ $data['cantidades']['mochila_trauma']['nivelo'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                    @endif
                </div>
                <div>
                    <div class="text-xs text-slate-500 text-center font-bold bg-teal-100 p-1 rounded">Kit Inmovilización completo</div>
                    @if($readonly)
                        <div class="font-semibold text-center">{{ $data['cantidades']['mochila_trauma']['kit_inmovilizacion'] ?? '—' }}</div>
                    @else
                        <input type="text" name="data[cantidades][mochila_trauma][kit_inmovilizacion]" value="{{ $data['cantidades']['mochila_trauma']['kit_inmovilizacion'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                    @endif
                </div>
                <div>
                    <div class="text-xs text-slate-500 text-center font-bold bg-teal-100 p-1 rounded">Conos</div>
                    @if($readonly)
                        <div class="font-semibold text-center">{{ $data['cantidades']['mochila_trauma']['conos'] ?? '—' }}</div>
                    @else
                        <input type="text" name="data[cantidades][mochila_trauma][conos]" value="{{ $data['cantidades']['mochila_trauma']['conos'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                    @endif
                </div>
            </div>
        </div>

        {{-- MANGUERAS --}}
        <div class="bg-teal-700 text-white p-1 text-xs font-bold text-center">MANGUERAS</div>
        <div class="grid grid-cols-5 gap-1 bg-white p-2 rounded border border-slate-200">
            <div>
                <div class="text-xs text-slate-500 text-center font-bold bg-teal-100 p-1 rounded">52</div>
                @if($readonly)
                    <div class="font-semibold text-center">{{ $data['cantidades']['mangueras_52'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][mangueras_52]" value="{{ $data['cantidades']['mangueras_52'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </div>
            <div>
                <div class="text-xs text-slate-500 text-center font-bold bg-teal-100 p-1 rounded">75</div>
                @if($readonly)
                    <div class="font-semibold text-center">{{ $data['cantidades']['mangueras_75'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][mangueras_75]" value="{{ $data['cantidades']['mangueras_75'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </div>
            <div>
                <div class="text-xs text-slate-500 text-center font-bold bg-teal-100 p-1 rounded">L D H</div>
                @if($readonly)
                    <div class="font-semibold text-center">{{ $data['cantidades']['mangueras_ldh'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][mangueras_ldh]" value="{{ $data['cantidades']['mangueras_ldh'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </div>
            <div>
                <div class="text-xs text-slate-500 text-center font-bold bg-teal-100 p-1 rounded">Armada Base</div>
                @if($readonly)
                    <div class="font-semibold text-center">{{ $data['cantidades']['mangueras_armada_base'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][mangueras_armada_base]" value="{{ $data['cantidades']['mangueras_armada_base'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </div>
            <div></div>
        </div>

        {{-- Paquete Circular --}}
        <div class="grid grid-cols-3 gap-1 bg-white p-2 rounded border border-slate-200">
            <div class="font-bold text-sm">Paquete Circular</div>
            <div>
                <div class="text-xs text-slate-500 text-center">Herraduras</div>
                @if($readonly)
                    <div class="font-semibold text-center">{{ $data['cantidades']['paquete_herraduras'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][paquete_herraduras]" value="{{ $data['cantidades']['paquete_herraduras'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </div>
            <div>
                <div class="text-xs text-slate-500 text-center">Carretes alimentación (cantidad tiras)</div>
                @if($readonly)
                    <div class="font-semibold text-center">{{ $data['cantidades']['paquete_carretes'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][paquete_carretes]" value="{{ $data['cantidades']['paquete_carretes'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </div>
        </div>

        {{-- ATAQUES --}}
        <div class="bg-teal-700 text-white p-1 text-xs font-bold text-center">ATAQUES</div>
        <div class="grid grid-cols-4 gap-1 bg-white p-2 rounded border border-slate-200">
            <div>
                <div class="text-xs text-slate-500 text-center font-bold bg-teal-100 p-1 rounded">52</div>
                @if($readonly)
                    <div class="font-semibold text-center">{{ $data['cantidades']['ataques_52'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][ataques_52]" value="{{ $data['cantidades']['ataques_52'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </div>
            <div>
                <div class="text-xs text-slate-500 text-center font-bold bg-teal-100 p-1 rounded">75</div>
                @if($readonly)
                    <div class="font-semibold text-center">{{ $data['cantidades']['ataques_75'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][ataques_75]" value="{{ $data['cantidades']['ataques_75'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </div>
            <div>
                <div class="text-xs text-slate-500 text-center">Cilindros de recambio MSA</div>
                @if($readonly)
                    <div class="font-semibold text-center">{{ $data['cantidades']['ataques_cilindros_msa'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][ataques_cilindros_msa]" value="{{ $data['cantidades']['ataques_cilindros_msa'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </div>
            <div></div>
        </div>

        {{-- TRASPASOS --}}
        <div class="bg-teal-700 text-white p-1 text-xs font-bold text-center">TRASPASOS</div>
        <div class="grid grid-cols-3 gap-1 bg-white p-2 rounded border border-slate-200">
            <div>
                <div class="text-xs text-slate-500 text-center font-bold bg-teal-100 p-1 rounded">Llave de grifo</div>
                @if($readonly)
                    <div class="font-semibold text-center">{{ $data['cantidades']['traspasos_llave_grifo'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][traspasos_llave_grifo]" value="{{ $data['cantidades']['traspasos_llave_grifo'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </div>
            <div>
                <div class="text-xs text-slate-500 text-center font-bold bg-teal-100 p-1 rounded">Traspaso de grifo</div>
                @if($readonly)
                    <div class="font-semibold text-center">{{ $data['cantidades']['traspasos_traspaso_grifo'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][traspasos_traspaso_grifo]" value="{{ $data['cantidades']['traspasos_traspaso_grifo'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </div>
            <div></div>
        </div>

        {{-- LLAVES DE COPLA --}}
        <div class="bg-teal-700 text-white p-1 text-xs font-bold text-center">LLAVES DE COPLA</div>
        <div class="grid grid-cols-2 gap-1 bg-white p-2 rounded border border-slate-200">
            <div>
                <div class="text-xs text-slate-500 text-center font-bold bg-teal-100 p-1 rounded">Llave de piso</div>
                @if($readonly)
                    <div class="font-semibold text-center">{{ $data['cantidades']['llaves_llave_piso'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][llaves_llave_piso]" value="{{ $data['cantidades']['llaves_llave_piso'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </div>
            <div>
                <div class="text-xs text-slate-500 text-center font-bold bg-teal-100 p-1 rounded">Manguera 75 alimentación</div>
                @if($readonly)
                    <div class="font-semibold text-center">{{ $data['cantidades']['llaves_manguera_75'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][llaves_manguera_75]" value="{{ $data['cantidades']['llaves_manguera_75'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </div>
        </div>

        {{-- Escalas de Techo --}}
        <div class="grid grid-cols-3 gap-1 bg-white p-2 rounded border border-slate-200">
            <div class="font-bold text-sm">Escalas de Techo</div>
            <div>
                <div class="text-xs text-slate-500 text-center">Puntas Taladro</div>
                @if($readonly)
                    <div class="font-semibold text-center">{{ $data['cantidades']['escalas_puntas_taladro'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][escalas_puntas_taladro]" value="{{ $data['cantidades']['escalas_puntas_taladro'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </div>
            <div>
                <div class="text-xs text-slate-500 text-center">Napoleón 30"</div>
                @if($readonly)
                    <div class="font-semibold text-center">{{ $data['cantidades']['escalas_napoleon'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][escalas_napoleon]" value="{{ $data['cantidades']['escalas_napoleon'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </div>
        </div>

        {{-- Combo de 8 libras --}}
        <div class="grid grid-cols-3 gap-1 bg-white p-2 rounded border border-slate-200">
            <div class="font-bold text-sm">Combo de 8 libras</div>
            <div>
                <div class="text-xs text-slate-500 text-center">Bidón Motosierra</div>
                @if($readonly)
                    <div class="font-semibold text-center">{{ $data['cantidades']['combo_bidon_motosierra'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][combo_bidon_motosierra]" value="{{ $data['cantidades']['combo_bidon_motosierra'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </div>
            <div>
                <div class="text-xs text-slate-500 text-center">Hacha bombero</div>
                @if($readonly)
                    <div class="font-semibold text-center">{{ $data['cantidades']['combo_hacha_bombero'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][combo_hacha_bombero]" value="{{ $data['cantidades']['combo_hacha_bombero'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </div>
        </div>

        {{-- Bicheros --}}
        <div class="grid grid-cols-3 gap-1 bg-white p-2 rounded border border-slate-200">
            <div class="font-bold text-sm">Bicheros</div>
            <div>
                <div class="text-xs text-slate-500 text-center">Barretilla</div>
                @if($readonly)
                    <div class="font-semibold text-center">{{ $data['cantidades']['bicheros_barretilla'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][bicheros_barretilla]" value="{{ $data['cantidades']['bicheros_barretilla'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </div>
            <div>
                <div class="text-xs text-slate-500 text-center">Halligan</div>
                @if($readonly)
                    <div class="font-semibold text-center">{{ $data['cantidades']['bicheros_halligan'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][bicheros_halligan]" value="{{ $data['cantidades']['bicheros_halligan'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </div>
        </div>

        {{-- Caja de Herramientas --}}
        <div class="grid grid-cols-3 gap-1 bg-white p-2 rounded border border-slate-200">
            <div class="font-bold text-sm">Caja de Herramientas</div>
            <div>
                <div class="text-xs text-slate-500 text-center">Hacha suela</div>
                @if($readonly)
                    <div class="font-semibold text-center">{{ $data['cantidades']['caja_hacha_suela'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][caja_hacha_suela]" value="{{ $data['cantidades']['caja_hacha_suela'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </div>
            <div>
                <div class="text-xs text-slate-500 text-center">TNT</div>
                @if($readonly)
                    <div class="font-semibold text-center">{{ $data['cantidades']['caja_tnt'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][caja_tnt]" value="{{ $data['cantidades']['caja_tnt'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </div>
        </div>

        {{-- Gremio --}}
        <div class="grid grid-cols-3 gap-1 bg-white p-2 rounded border border-slate-200">
            <div class="font-bold text-sm">Gremio</div>
            <div>
                <div class="text-xs text-slate-500 text-center">Kit Entrada Forzada</div>
                @if($readonly)
                    <div class="font-semibold text-center">{{ $data['cantidades']['gremio_kit_entrada'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][gremio_kit_entrada]" value="{{ $data['cantidades']['gremio_kit_entrada'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </div>
            <div>
                <div class="text-xs text-slate-500 text-center">Trifurca</div>
                @if($readonly)
                    <div class="font-semibold text-center">{{ $data['cantidades']['gremio_trifurca'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][gremio_trifurca]" value="{{ $data['cantidades']['gremio_trifurca'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </div>
        </div>

        {{-- Filtro de Aspiración --}}
        <div class="grid grid-cols-3 gap-1 bg-white p-2 rounded border border-slate-200">
            <div class="font-bold text-sm">Filtro de Aspiración</div>
            <div>
                <div class="text-xs text-slate-500 text-center">Siamesa</div>
                @if($readonly)
                    <div class="font-semibold text-center">{{ $data['cantidades']['filtro_siamesa'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][filtro_siamesa]" value="{{ $data['cantidades']['filtro_siamesa'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </div>
            <div>
                <div class="text-xs text-slate-500 text-center">Pitones de 50</div>
                @if($readonly)
                    <div class="font-semibold text-center">{{ $data['cantidades']['filtro_pitones_50'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][filtro_pitones_50]" value="{{ $data['cantidades']['filtro_pitones_50'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </div>
        </div>

        {{-- Kit quemados --}}
        <div class="grid grid-cols-3 gap-1 bg-white p-2 rounded border border-slate-200">
            <div class="font-bold text-sm">Kit quemados / Asistente de Trauma</div>
            <div>
                <div class="text-xs text-slate-500 text-center">Pitón DIN 50</div>
                @if($readonly)
                    <div class="font-semibold text-center">{{ $data['cantidades']['kit_piton_din_50'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][kit_piton_din_50]" value="{{ $data['cantidades']['kit_piton_din_50'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </div>
            <div>
                <div class="text-xs text-slate-500 text-center">Pitones de 70</div>
                @if($readonly)
                    <div class="font-semibold text-center">{{ $data['cantidades']['kit_pitones_70'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][kit_pitones_70]" value="{{ $data['cantidades']['kit_pitones_70'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </div>
        </div>

        {{-- Bidón de bencina --}}
        <div class="grid grid-cols-3 gap-1 bg-white p-2 rounded border border-slate-200">
            <div class="font-bold text-sm">Bidón de bencina Verde 10L</div>
            <div>
                <div class="text-xs text-slate-500 text-center">Chorizos</div>
                @if($readonly)
                    <div class="font-semibold text-center">{{ $data['cantidades']['bidon_chorizos'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][bidon_chorizos]" value="{{ $data['cantidades']['bidon_chorizos'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </div>
            <div>
                <div class="text-xs text-slate-500 text-center">Pasatiras</div>
                @if($readonly)
                    <div class="font-semibold text-center">{{ $data['cantidades']['bidon_pasatiras'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][bidon_pasatiras]" value="{{ $data['cantidades']['bidon_pasatiras'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </div>
        </div>

        {{-- Extintor de agua --}}
        <div class="grid grid-cols-3 gap-1 bg-white p-2 rounded border border-slate-200">
            <div class="font-bold text-sm">Extintor de agua</div>
            <div>
                <div class="text-xs text-slate-500 text-center">Carretes de extensión</div>
                @if($readonly)
                    <div class="font-semibold text-center">{{ $data['cantidades']['extintor_carretes'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][extintor_carretes]" value="{{ $data['cantidades']['extintor_carretes'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </div>
            <div>
                <div class="text-xs text-slate-500 text-center">Pitón de espuma</div>
                @if($readonly)
                    <div class="font-semibold text-center">{{ $data['cantidades']['extintor_piton_espuma'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][extintor_piton_espuma]" value="{{ $data['cantidades']['extintor_piton_espuma'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </div>
        </div>
    </div>

    {{-- Material Forestal --}}
    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-3 rounded">
        <div class="font-bold text-sm">Material Forestal</div>
        <div class="text-xs text-slate-600 italic">(1 pala, 1 rozón, 2 rastrillos cegadores, 1 McLeod, 2 bombas de espalda)</div>
    </div>

    {{-- Bolso de altura --}}
    <div class="bg-green-100 border-l-4 border-green-400 p-3 rounded">
        <div class="font-bold text-sm">Bolso de altura</div>
        <div class="text-xs text-slate-600 italic">(Tira de 38mm 3 metros, Pitón POK, gemelo, reducción, cuerda utilitaria)</div>
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
                <div class="text-xs font-bold">Nivel Combustible Bidón (Naranjo)</div>
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
