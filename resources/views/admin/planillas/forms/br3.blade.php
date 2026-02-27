@php
    $data = $data ?? [];
    $readonly = $readonly ?? false;
@endphp

<div class="space-y-4">
    {{-- Header --}}
    <div class="bg-sky-100 rounded-xl border border-teal-900/20 p-4">
        <div class="flex justify-between items-center">
            <div class="text-xs font-black uppercase tracking-widest text-slate-900">PLANILLA DE REVISIÓN DE NIVELES</div>
            <div class="text-sm font-black text-slate-900">BR-3</div>
        </div>
    </div>

    {{-- CABINA --}}
    <div class="bg-teal-800 text-white p-2 text-xs font-black uppercase tracking-widest">CABINA</div>
    
    <div class="space-y-2">
        {{-- Linterna --}}
        <div class="grid grid-cols-12 gap-1 items-center bg-white p-2 rounded border border-slate-200">
            <div class="col-span-4 font-bold text-sm">Linterna NIGHTSTICK</div>
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
            <div class="col-span-2"></div>
            <div class="col-span-2">
                <div class="text-xs text-slate-500">Radios Baoffeng</div>
            </div>
            <div class="col-span-1">
                <div class="text-xs text-slate-500">Nº</div>
                @if($readonly)
                    <div class="font-semibold">{{ $data['cabina']['radios_baofeng']['numero'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cabina][radios_baofeng][numero]" value="{{ $data['cabina']['radios_baofeng']['numero'] ?? '' }}" class="w-full text-sm border rounded p-1">
                @endif
            </div>
            <div class="col-span-1">
                <div class="text-xs text-slate-500">Bat</div>
                @if($readonly)
                    <div class="font-semibold">{{ $data['cabina']['radios_baofeng']['bateria'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cabina][radios_baofeng][bateria]" value="{{ $data['cabina']['radios_baofeng']['bateria'] ?? '' }}" class="w-full text-sm border rounded p-1">
                @endif
            </div>
        </div>

        {{-- ERA SCOTT --}}
        <div class="grid grid-cols-12 gap-1 items-center bg-white p-2 rounded border border-slate-200">
            <div class="col-span-4 font-bold text-sm">ERA SCOTT 4.5</div>
            <div class="col-span-2">
                <div class="text-xs text-slate-500">Cantidad</div>
                @if($readonly)
                    <div class="font-semibold">{{ $data['cabina']['era_scott_4_5']['cantidad'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cabina][era_scott_4_5][cantidad]" value="{{ $data['cabina']['era_scott_4_5']['cantidad'] ?? '' }}" class="w-full text-sm border rounded p-1">
                @endif
            </div>
            <div class="col-span-2">
                <div class="text-xs text-slate-500">Funcionamiento</div>
                @if($readonly)
                    <div class="font-semibold">{{ ($data['cabina']['era_scott_4_5']['funcionamiento'] ?? '') === 'si' ? 'Sí' : (($data['cabina']['era_scott_4_5']['funcionamiento'] ?? '') === 'no' ? 'No' : '—') }}</div>
                @else
                    <select name="data[cabina][era_scott_4_5][funcionamiento]" class="w-full text-sm border rounded p-1">
                        <option value=""></option>
                        <option value="si" {{ ($data['cabina']['era_scott_4_5']['funcionamiento'] ?? '') === 'si' ? 'selected' : '' }}>Sí</option>
                        <option value="no" {{ ($data['cabina']['era_scott_4_5']['funcionamiento'] ?? '') === 'no' ? 'selected' : '' }}>No</option>
                    </select>
                @endif
            </div>
            <div class="col-span-2">
                <div class="text-xs text-slate-500">Nivel de aire</div>
                @if($readonly)
                    <div class="font-semibold">{{ $data['cabina']['era_scott_4_5']['nivel_aire'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cabina][era_scott_4_5][nivel_aire]" value="{{ $data['cabina']['era_scott_4_5']['nivel_aire'] ?? '' }}" class="w-full text-sm border rounded p-1">
                @endif
            </div>
        </div>

        {{-- Chaquetillas STEX --}}
        <div class="grid grid-cols-12 gap-1 items-center bg-white p-2 rounded border border-slate-200">
            <div class="col-span-4 font-bold text-sm">Chaquetillas STEX</div>
            <div class="col-span-2">
                <div class="text-xs text-slate-500">Cantidad</div>
                @if($readonly)
                    <div class="font-semibold">{{ $data['cabina']['chaquetillas_stex']['cantidad'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cabina][chaquetillas_stex][cantidad]" value="{{ $data['cabina']['chaquetillas_stex']['cantidad'] ?? '' }}" class="w-full text-sm border rounded p-1">
                @endif
            </div>
            <div class="col-span-2"></div>
            <div class="col-span-4 font-bold text-sm">Lona organizadora material</div>
        </div>

        {{-- Tablet BR-3 --}}
        <div class="grid grid-cols-12 gap-1 items-center bg-white p-2 rounded border border-slate-200">
            <div class="col-span-4 font-bold text-sm">Tablet unidad BR-3 y Cargador</div>
            <div class="col-span-2">
                <div class="text-xs text-slate-500">Batería</div>
                @if($readonly)
                    <div class="font-semibold">{{ ($data['cabina']['tablet_br3']['bateria'] ?? '') === 'si' ? 'Sí' : (($data['cabina']['tablet_br3']['bateria'] ?? '') === 'no' ? 'No' : '—') }}</div>
                @else
                    <select name="data[cabina][tablet_br3][bateria]" class="w-full text-sm border rounded p-1">
                        <option value=""></option>
                        <option value="si" {{ ($data['cabina']['tablet_br3']['bateria'] ?? '') === 'si' ? 'selected' : '' }}>Sí</option>
                        <option value="no" {{ ($data['cabina']['tablet_br3']['bateria'] ?? '') === 'no' ? 'selected' : '' }}>No</option>
                    </select>
                @endif
            </div>
            <div class="col-span-2"></div>
            <div class="col-span-4 font-bold text-sm">Maleta SCI</div>
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
            <div class="col-span-5 bg-teal-700 text-white p-1 rounded">Novedades</div>
        </div>
        
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
        @endphp
        
        @foreach($herramientas as $key => $label)
            <div class="grid grid-cols-12 gap-1 items-center bg-white p-1 rounded border border-slate-200 mb-1 {{ in_array($key, ['ripper', 'baston_tastik', 'generador_honda', 'motosierra_stihl', 'motor_holmatro', 'combi_lukas', 'motoamoladora', 'sierra_sable']) ? 'bg-yellow-50' : '' }}">
                <div class="col-span-4 font-semibold text-sm {{ in_array($key, ['ripper', 'baston_tastik', 'generador_honda', 'motosierra_stihl', 'motor_holmatro', 'combi_lukas', 'motoamoladora', 'sierra_sable']) ? 'bg-yellow-100 p-1 rounded' : '' }}">{{ $label }}</div>
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

            {{-- Bolso Oxigenoterapia --}}
            <div class="bg-white p-2 rounded border border-slate-200">
                <div class="font-bold text-sm mb-1">Bolso Oxigenoterapia</div>
                <div class="grid grid-cols-2 gap-1">
                    <div>
                        <div class="text-xs text-slate-500 bg-teal-100 p-1 rounded text-center">NIVEL O.</div>
                        @if($readonly)
                            <div class="font-semibold">{{ $data['trauma']['bolso_oxigenoterapia']['nivelo1'] ?? '—' }}</div>
                        @else
                            <input type="text" name="data[trauma][bolso_oxigenoterapia][nivelo1]" value="{{ $data['trauma']['bolso_oxigenoterapia']['nivelo1'] ?? '' }}" class="w-full text-sm border rounded p-1">
                        @endif
                    </div>
                    <div>
                        <div class="text-xs text-slate-500 bg-teal-100 p-1 rounded text-center">NIVEL O.</div>
                        @if($readonly)
                            <div class="font-semibold">{{ $data['trauma']['bolso_oxigenoterapia']['nivelo2'] ?? '—' }}</div>
                        @else
                            <input type="text" name="data[trauma][bolso_oxigenoterapia][nivelo2]" value="{{ $data['trauma']['bolso_oxigenoterapia']['nivelo2'] ?? '' }}" class="w-full text-sm border rounded p-1">
                        @endif
                    </div>
                </div>
            </div>

            {{-- Chalecos de extricación --}}
            <div class="bg-white p-2 rounded border border-slate-200">
                <div class="font-bold text-sm mb-1">Chalecos de extricación</div>
                <div class="grid grid-cols-3 gap-1">
                    <div>
                        <div class="text-xs text-slate-500">Adulto</div>
                        @if($readonly)
                            <div class="font-semibold">{{ $data['trauma']['chalecos_extricacion']['adulto'] ?? '—' }}</div>
                        @else
                            <input type="text" name="data[trauma][chalecos_extricacion][adulto]" value="{{ $data['trauma']['chalecos_extricacion']['adulto'] ?? '' }}" class="w-full text-sm border rounded p-1">
                        @endif
                    </div>
                    <div>
                        <div class="text-xs text-slate-500">PED</div>
                        @if($readonly)
                            <div class="font-semibold">{{ $data['trauma']['chalecos_extricacion']['ped'] ?? '—' }}</div>
                        @else
                            <input type="text" name="data[trauma][chalecos_extricacion][ped]" value="{{ $data['trauma']['chalecos_extricacion']['ped'] ?? '' }}" class="w-full text-sm border rounded p-1">
                        @endif
                    </div>
                    <div>
                        <div class="text-xs text-slate-500">G2</div>
                        @if($readonly)
                            <div class="font-semibold">{{ $data['trauma']['chalecos_extricacion']['g2'] ?? '—' }}</div>
                        @else
                            <input type="text" name="data[trauma][chalecos_extricacion][g2]" value="{{ $data['trauma']['chalecos_extricacion']['g2'] ?? '' }}" class="w-full text-sm border rounded p-1">
                        @endif
                    </div>
                </div>
            </div>

            {{-- Tablas Largas --}}
            <div class="bg-white p-2 rounded border border-slate-200">
                <div class="font-bold text-sm mb-1">Tablas Largas</div>
                <div class="grid grid-cols-2 gap-1">
                    <div>
                        <div class="text-xs text-slate-500">Techo</div>
                        @if($readonly)
                            <div class="font-semibold">{{ $data['trauma']['tablas_largas']['techo'] ?? '—' }}</div>
                        @else
                            <input type="text" name="data[trauma][tablas_largas][techo]" value="{{ $data['trauma']['tablas_largas']['techo'] ?? '' }}" class="w-full text-sm border rounded p-1">
                        @endif
                    </div>
                    <div></div>
                </div>
            </div>

            {{-- Tabla pediátrica --}}
            <div class="bg-white p-2 rounded border border-slate-200">
                <div class="font-bold text-sm mb-1">Tabla pediátrica</div>
                <div class="grid grid-cols-2 gap-1">
                    <div>
                        <div class="text-xs text-slate-500">Tabla corta</div>
                        @if($readonly)
                            <div class="font-semibold">{{ $data['trauma']['tabla_pediatrica']['tabla_corta'] ?? '—' }}</div>
                        @else
                            <input type="text" name="data[trauma][tabla_pediatrica][tabla_corta]" value="{{ $data['trauma']['tabla_pediatrica']['tabla_corta'] ?? '' }}" class="w-full text-sm border rounded p-1">
                        @endif
                    </div>
                    <div></div>
                </div>
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
                <div class="font-bold text-sm mb-1">Tabla Scoop</div>
                @if($readonly)
                    <div class="font-semibold">{{ $data['trauma']['tabla_scoop'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[trauma][tabla_scoop]" value="{{ $data['trauma']['tabla_scoop'] ?? '' }}" class="w-full text-sm border rounded p-1">
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

            <div class="bg-white p-2 rounded border border-slate-200">
                <div class="font-bold text-sm mb-1">Bolso TRIAGE</div>
                @if($readonly)
                    <div class="font-semibold">{{ $data['trauma']['bolso_triage'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[trauma][bolso_triage]" value="{{ $data['trauma']['bolso_triage'] ?? '' }}" class="w-full text-sm border rounded p-1">
                @endif
            </div>
        </div>
    </div>

    {{-- Indique la cantidad --}}
    <div class="bg-teal-800 text-white p-2 text-xs font-black uppercase tracking-widest text-center">Indique la cantidad</div>
    
    <div class="space-y-2 bg-sky-100 p-2">
        {{-- MANGUERAS --}}
        <div class="bg-teal-700 text-white p-1 text-xs font-bold text-center">MANGUERAS</div>
        <div class="grid grid-cols-5 gap-1 bg-white p-2 rounded border border-slate-200">
            <div>
                <div class="text-xs text-slate-500 text-center">38mm</div>
                @if($readonly)
                    <div class="font-semibold text-center">{{ $data['cantidades']['mangueras_38mm'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][mangueras_38mm]" value="{{ $data['cantidades']['mangueras_38mm'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </div>
            <div>
                <div class="text-xs text-slate-500 text-center">52mm</div>
                @if($readonly)
                    <div class="font-semibold text-center">{{ $data['cantidades']['mangueras_52mm'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][mangueras_52mm]" value="{{ $data['cantidades']['mangueras_52mm'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </div>
            <div>
                <div class="text-xs text-slate-500 text-center">75mm</div>
                @if($readonly)
                    <div class="font-semibold text-center">{{ $data['cantidades']['mangueras_75mm'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][mangueras_75mm]" value="{{ $data['cantidades']['mangueras_75mm'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </div>
            <div>
                <div class="text-xs text-slate-500 text-center">L D H</div>
                @if($readonly)
                    <div class="font-semibold text-center">{{ $data['cantidades']['mangueras_ldh'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][mangueras_ldh]" value="{{ $data['cantidades']['mangueras_ldh'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </div>
            <div>
                <div class="text-xs text-slate-500 text-center">Armada Base</div>
                @if($readonly)
                    <div class="font-semibold text-center">{{ $data['cantidades']['mangueras_armada_base'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][mangueras_armada_base]" value="{{ $data['cantidades']['mangueras_armada_base'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </div>
        </div>

        {{-- HERRADURAS --}}
        <div class="bg-teal-700 text-white p-1 text-xs font-bold text-center">HERRADURAS</div>
        <div class="grid grid-cols-4 gap-1 bg-white p-2 rounded border border-slate-200">
            <div>
                <div class="text-xs text-slate-500 text-center">Cantidad</div>
                @if($readonly)
                    <div class="font-semibold text-center">{{ $data['cantidades']['herraduras_cantidad'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][herraduras_cantidad]" value="{{ $data['cantidades']['herraduras_cantidad'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </div>
            <div>
                <div class="text-xs text-slate-500 text-center">Llaves de copla</div>
                @if($readonly)
                    <div class="font-semibold text-center">{{ $data['cantidades']['herraduras_llaves_copla'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][herraduras_llaves_copla]" value="{{ $data['cantidades']['herraduras_llaves_copla'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </div>
            <div>
                <div class="text-xs text-slate-500 text-center">Pitón Rosenbauer 52</div>
                @if($readonly)
                    <div class="font-semibold text-center">{{ $data['cantidades']['herraduras_piton_rosenbauer'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][herraduras_piton_rosenbauer]" value="{{ $data['cantidades']['herraduras_piton_rosenbauer'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </div>
            <div></div>
        </div>

        {{-- ATAQUES --}}
        <div class="bg-teal-700 text-white p-1 text-xs font-bold text-center">ATAQUES</div>
        <div class="grid grid-cols-4 gap-1 bg-white p-2 rounded border border-slate-200">
            <div>
                <div class="text-xs text-slate-500 text-center">52mm</div>
                @if($readonly)
                    <div class="font-semibold text-center">{{ $data['cantidades']['ataques_52mm'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][ataques_52mm]" value="{{ $data['cantidades']['ataques_52mm'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </div>
            <div>
                <div class="text-xs text-slate-500 text-center">75mm</div>
                @if($readonly)
                    <div class="font-semibold text-center">{{ $data['cantidades']['ataques_75mm'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][ataques_75mm]" value="{{ $data['cantidades']['ataques_75mm'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </div>
            <div>
                <div class="text-xs text-slate-500 text-center">Manguera L D H</div>
                @if($readonly)
                    <div class="font-semibold text-center">{{ $data['cantidades']['ataques_manguera_ldh'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][ataques_manguera_ldh]" value="{{ $data['cantidades']['ataques_manguera_ldh'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </div>
            <div></div>
        </div>

        {{-- TRASPASOS --}}
        <div class="bg-teal-700 text-white p-1 text-xs font-bold text-center">TRASPASOS</div>
        <div class="grid grid-cols-4 gap-1 bg-white p-2 rounded border border-slate-200">
            <div>
                <div class="text-xs text-slate-500 text-center">Cantidad</div>
                @if($readonly)
                    <div class="font-semibold text-center">{{ $data['cantidades']['traspasos_cantidad'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][traspasos_cantidad]" value="{{ $data['cantidades']['traspasos_cantidad'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </div>
            <div>
                <div class="text-xs text-slate-500 text-center">Llave de grifo</div>
                @if($readonly)
                    <div class="font-semibold text-center">{{ $data['cantidades']['traspasos_llave_grifo'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][traspasos_llave_grifo]" value="{{ $data['cantidades']['traspasos_llave_grifo'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </div>
            <div>
                <div class="text-xs text-slate-500 text-center">Trifurca</div>
                @if($readonly)
                    <div class="font-semibold text-center">{{ $data['cantidades']['traspasos_trifurca'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][traspasos_trifurca]" value="{{ $data['cantidades']['traspasos_trifurca'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </div>
            <div>
                <div class="text-xs text-slate-500 text-center">Traspaso grifo</div>
                @if($readonly)
                    <div class="font-semibold text-center">{{ $data['cantidades']['traspasos_traspaso_grifo'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][traspasos_traspaso_grifo]" value="{{ $data['cantidades']['traspasos_traspaso_grifo'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </div>
        </div>

        {{-- Protecciones --}}
        <div class="grid grid-cols-4 gap-1 bg-white p-2 rounded border border-slate-200">
            <div class="text-xs font-semibold">Protecciones duras para paciente</div>
            <div>
                <div class="text-xs text-slate-500 text-center">Cojines VETTER</div>
                @if($readonly)
                    <div class="font-semibold text-center">{{ $data['cantidades']['cojines_vetter'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][cojines_vetter]" value="{{ $data['cantidades']['cojines_vetter'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </div>
            <div>
                <div class="text-xs text-slate-500 text-center">Eslings</div>
                @if($readonly)
                    <div class="font-semibold text-center">{{ $data['cantidades']['eslings'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][eslings]" value="{{ $data['cantidades']['eslings'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </div>
            <div></div>
        </div>

        {{-- Tecle y caja --}}
        <div class="grid grid-cols-3 gap-1 bg-white p-2 rounded border border-slate-200">
            <div>
                <div class="text-xs text-slate-500 text-center">Tecle para cadena 2000kg</div>
                @if($readonly)
                    <div class="font-semibold text-center">{{ $data['cantidades']['tecle_cadena'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][tecle_cadena]" value="{{ $data['cantidades']['tecle_cadena'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </div>
            <div>
                <div class="text-xs text-slate-500 text-center">Caja de Herramientas</div>
                @if($readonly)
                    <div class="font-semibold text-center">{{ $data['cantidades']['caja_herramientas'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][caja_herramientas]" value="{{ $data['cantidades']['caja_herramientas'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </div>
            <div>
                <div class="text-xs text-slate-500 text-center">Cubre Airbag</div>
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
                <div class="text-xs text-slate-500 text-center">Biseladas</div>
                @if($readonly)
                    <div class="font-semibold text-center">{{ $data['cantidades']['cunas_biseladas'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][cunas_biseladas]" value="{{ $data['cantidades']['cunas_biseladas'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </div>
            <div>
                <div class="text-xs text-slate-500 text-center">Bloques</div>
                @if($readonly)
                    <div class="font-semibold text-center">{{ $data['cantidades']['cunas_bloques'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][cunas_bloques]" value="{{ $data['cantidades']['cunas_bloques'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </div>
            <div>
                <div class="text-xs text-slate-500 text-center">Escalonadas</div>
                @if($readonly)
                    <div class="font-semibold text-center">{{ $data['cantidades']['cunas_escalonadas'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][cunas_escalonadas]" value="{{ $data['cantidades']['cunas_escalonadas'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </div>
            <div>
                <div class="text-xs text-slate-500 text-center">Planas</div>
                @if($readonly)
                    <div class="font-semibold text-center">{{ $data['cantidades']['cunas_plan'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][cunas_plan]" value="{{ $data['cantidades']['cunas_plan'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </div>
            <div>
                <div class="text-xs text-slate-500 text-center">Combos de 2 libras</div>
                @if($readonly)
                    <div class="font-semibold text-center">{{ $data['cantidades']['cunas_combos_2l'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][cunas_combos_2l]" value="{{ $data['cantidades']['cunas_combos_2l'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </div>
        </div>

        {{-- CAJONERAS LATERALES --}}
        <div class="bg-teal-700 text-white p-1 text-xs font-bold text-center">CAJONERAS LATERALES</div>
        <div class="grid grid-cols-4 gap-1 bg-white p-2 rounded border border-slate-200">
            <div>
                <div class="text-xs text-slate-500 text-center">Barretilla</div>
                @if($readonly)
                    <div class="font-semibold text-center">{{ $data['cantidades']['cajoneras_barretilla'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][cajoneras_barretilla]" value="{{ $data['cantidades']['cajoneras_barretilla'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </div>
            <div>
                <div class="text-xs text-slate-500 text-center">Napoleón</div>
                @if($readonly)
                    <div class="font-semibold text-center">{{ $data['cantidades']['cajoneras_napoleon'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][cajoneras_napoleon]" value="{{ $data['cantidades']['cajoneras_napoleon'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </div>
            <div>
                <div class="text-xs text-slate-500 text-center">Set Stab Fast XL</div>
                @if($readonly)
                    <div class="font-semibold text-center">{{ $data['cantidades']['cajoneras_stab_fast'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][cajoneras_stab_fast]" value="{{ $data['cantidades']['cajoneras_stab_fast'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </div>
            <div>
                <div class="text-xs text-slate-500 text-center">First Responder Jack</div>
                @if($readonly)
                    <div class="font-semibold text-center">{{ $data['cantidades']['cajoneras_jack'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][cajoneras_jack]" value="{{ $data['cantidades']['cajoneras_jack'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </div>
        </div>

        {{-- ESCALAS --}}
        <div class="bg-teal-700 text-white p-1 text-xs font-bold text-center">ESCALAS</div>
        <div class="grid grid-cols-3 gap-1 bg-white p-2 rounded border border-slate-200">
            <div>
                <div class="text-xs text-slate-500 text-center">2 cuerpos 12m</div>
                @if($readonly)
                    <div class="font-semibold text-center">{{ $data['cantidades']['escalas_2c_12m'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][escalas_2c_12m]" value="{{ $data['cantidades']['escalas_2c_12m'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </div>
            <div>
                <div class="text-xs text-slate-500 text-center">2 cuerpos 8m</div>
                @if($readonly)
                    <div class="font-semibold text-center">{{ $data['cantidades']['escalas_2c_8m'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][escalas_2c_8m]" value="{{ $data['cantidades']['escalas_2c_8m'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </div>
            <div>
                <div class="text-xs text-slate-500 text-center">Escala plegable</div>
                @if($readonly)
                    <div class="font-semibold text-center">{{ $data['cantidades']['escalas_plegable'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][escalas_plegable]" value="{{ $data['cantidades']['escalas_plegable'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </div>
        </div>

        {{-- Set lona cubre pilares --}}
        <div class="grid grid-cols-3 gap-1 bg-white p-2 rounded border border-slate-200">
            <div class="text-xs font-semibold">Set lona cubre pilares</div>
            <div>
                <div class="text-xs text-slate-500 text-center">Rozón</div>
                @if($readonly)
                    <div class="font-semibold text-center">{{ $data['cantidades']['lona_pilares_rozon'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][lona_pilares_rozon]" value="{{ $data['cantidades']['lona_pilares_rozon'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </div>
            <div>
                <div class="text-xs text-slate-500 text-center">Chuzo</div>
                @if($readonly)
                    <div class="font-semibold text-center">{{ $data['cantidades']['lona_pilares_chuzo'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][lona_pilares_chuzo]" value="{{ $data['cantidades']['lona_pilares_chuzo'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </div>
        </div>

        {{-- Bicheros --}}
        <div class="grid grid-cols-3 gap-1 bg-white p-2 rounded border border-slate-200">
            <div class="text-xs font-semibold">Bicheros</div>
            <div>
                <div class="text-xs text-slate-500 text-center">Conos</div>
                @if($readonly)
                    <div class="font-semibold text-center">{{ $data['cantidades']['bicheros_conos'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][bicheros_conos]" value="{{ $data['cantidades']['bicheros_conos'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
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

        {{-- Focos LED --}}
        <div class="grid grid-cols-3 gap-1 bg-white p-2 rounded border border-slate-200">
            <div class="text-xs font-semibold">Focos LED con cable y trípode</div>
            <div>
                <div class="text-xs text-slate-500 text-center">Hacha suela</div>
                @if($readonly)
                    <div class="font-semibold text-center">{{ $data['cantidades']['focos_led_hacha_suela'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][focos_led_hacha_suela]" value="{{ $data['cantidades']['focos_led_hacha_suela'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </div>
            <div>
                <div class="text-xs text-slate-500 text-center">TNT</div>
                @if($readonly)
                    <div class="font-semibold text-center">{{ $data['cantidades']['focos_led_tnt'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][focos_led_tnt]" value="{{ $data['cantidades']['focos_led_tnt'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </div>
        </div>

        {{-- Carrete cable --}}
        <div class="grid grid-cols-3 gap-1 bg-white p-2 rounded border border-slate-200">
            <div class="text-xs font-semibold">Carrete cable eléctrico</div>
            <div>
                <div class="text-xs text-slate-500 text-center">Hacha bombero</div>
                @if($readonly)
                    <div class="font-semibold text-center">{{ $data['cantidades']['carrete_hacha_bombero'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][carrete_hacha_bombero]" value="{{ $data['cantidades']['carrete_hacha_bombero'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </div>
            <div>
                <div class="text-xs text-slate-500 text-center">Halligan</div>
                @if($readonly)
                    <div class="font-semibold text-center">{{ $data['cantidades']['carrete_halligan'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][carrete_halligan]" value="{{ $data['cantidades']['carrete_halligan'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </div>
        </div>

        {{-- Cadenas Holmatro --}}
        <div class="grid grid-cols-3 gap-1 bg-white p-2 rounded border border-slate-200">
            <div class="text-xs font-semibold">Cadenas y puntas Holmatro</div>
            <div>
                <div class="text-xs text-slate-500 text-center">Extintor de agua</div>
                @if($readonly)
                    <div class="font-semibold text-center">{{ $data['cantidades']['cadenas_extintor_agua'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][cadenas_extintor_agua]" value="{{ $data['cantidades']['cadenas_extintor_agua'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </div>
            <div>
                <div class="text-xs text-slate-500 text-center">Pitón monitor</div>
                @if($readonly)
                    <div class="font-semibold text-center">{{ $data['cantidades']['cadenas_piton_monitor'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][cadenas_piton_monitor]" value="{{ $data['cantidades']['cadenas_piton_monitor'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </div>
        </div>

        {{-- Corta parabrisas --}}
        <div class="grid grid-cols-4 gap-1 bg-white p-2 rounded border border-slate-200">
            <div class="text-xs font-semibold">Corta parabrisas manual</div>
            <div>
                <div class="text-xs text-slate-500 text-center">Force</div>
                @if($readonly)
                    <div class="font-semibold text-center">{{ $data['cantidades']['corta_parabrisas_force'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][corta_parabrisas_force]" value="{{ $data['cantidades']['corta_parabrisas_force'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </div>
            <div>
                <div class="text-xs text-slate-500 text-center">Cámara Termal</div>
                @if($readonly)
                    <div class="font-semibold text-center">{{ $data['cantidades']['corta_parabrisas_camara'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][corta_parabrisas_camara]" value="{{ $data['cantidades']['corta_parabrisas_camara'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </div>
            <div>
                <div class="text-xs text-slate-500 text-center">Bomba Espalda</div>
                @if($readonly)
                    <div class="font-semibold text-center">{{ $data['cantidades']['corta_parabrisas_bomba'] ?? '—' }}</div>
                @else
                    <input type="text" name="data[cantidades][corta_parabrisas_bomba]" value="{{ $data['cantidades']['corta_parabrisas_bomba'] ?? '' }}" class="w-full text-sm border rounded p-1 text-center">
                @endif
            </div>
        </div>
    </div>

    {{-- Material del Techo --}}
    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-3 rounded">
        <div class="font-bold text-sm">Material del Techo</div>
        <div class="text-xs text-slate-600 italic">(4 palas, 2 pasatiras, 2 McLeod, 3 rastrillos cegadores, 2 Chorizos, 1 Filtro de aspiración)</div>
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
    </div>
</div>
