<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Planilla {{ $planilla->unidad }} - {{ $planilla->fecha_revision->format('Y-m-d') }}</title>
    <style>
        @page { size: A4 landscape; margin: 8mm; }
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 7pt; margin: 0; color: #1e293b; }
        .watermark { position: fixed; bottom: -30mm; right: -40mm; width: 180mm; height: 180mm; opacity: 0.06; z-index: -1; pointer-events: none; }
        .header { text-align: center; margin-bottom: 10px; border-bottom: 2px double #1e40af; padding-bottom: 8px; }
        .header-title { font-size: 12pt; font-weight: 900; color: #1e40af; text-transform: uppercase; }
        .header-subtitle { font-size: 10pt; font-weight: 800; color: #0f172a; }
        .info-section { display: flex; gap: 20px; margin-bottom: 10px; font-size: 8pt; }
        .info-label { font-weight: 700; color: #64748b; }
        .info-value { font-weight: 600; color: #1e293b; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 8px; font-size: 7pt; }
        th, td { border: 1px solid #94a3b8; padding: 3px 4px; text-align: center; vertical-align: middle; }
        .section-header { background: linear-gradient(135deg, #0f766e 0%, #14b8a6 100%); color: #fff; font-weight: 700; text-transform: uppercase; text-align: left; font-size: 8pt; }
        .subsection-header { background: #ccfbf1; color: #0f766e; font-weight: 700; font-size: 7pt; }
        .col-header { background: #f1f5f9; font-weight: 600; color: #475569; }
        .label-cell { background: #f8fafc; text-align: left; font-weight: 600; color: #475569; }
        .value-cell { font-weight: 600; color: #1e293b; }
        .value-empty { color: #94a3b8; }
        .check-yes { color: #166534; font-weight: 700; }
        .check-no { color: #991b1b; font-weight: 700; }
        .footer { position: fixed; bottom: 5mm; left: 0; right: 0; text-align: center; font-size: 7pt; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 3px; }
        .firma-section { margin-top: 20px; width: 100%; }
        .firma-table { width: 100%; border: none; }
        .firma-table td { width: 50%; border: none; padding: 0 40px; vertical-align: top; }
        .firma-line { border-top: 1px solid #1e293b; margin-top: 30px; padding-top: 5px; font-size: 8pt; text-align: center; }
    </style>
</head>
<body>
    <img src="{{ public_path('brand/Logo png Alta Def.png') }}" class="watermark" alt="">

    <div class="header">
        <div class="header-title">PLANILLA DE REVISIÓN DE NIVELES</div>
        <div class="header-subtitle">Unidad: {{ $planilla->unidad }}</div>
    </div>
    
    <div class="info-section">
        <div><span class="info-label">Fecha:</span> <span class="info-value">{{ $planilla->fecha_revision->format('d/m/Y H:i') }}</span></div>
        <div><span class="info-label">Estado:</span> <span class="info-value">{{ $planilla->estado === 'finalizado' ? 'Finalizado' : 'En edición' }}</span></div>
        <div><span class="info-label">Registrada por:</span> <span class="info-value">{{ $planilla->creador?->name ?? '—' }}</span></div>
    </div>

    @php $data = $planilla->data ?? []; @endphp

    {{-- CABINA --}}
    <table>
        <tr><th colspan="12" class="section-header">CABINA</th></tr>
        {{-- Linterna y Radios --}}
        <tr>
            <td colspan="3" class="label-cell">Linterna NIGHTSTICK</td>
            <td colspan="2">
                <div style="font-size: 6pt;">Funciona</div>
                @php $f = $data['cabina']['linterna_nightstick']['funcionamiento'] ?? ''; @endphp
                @if($f==='si')<span class="check-yes">SÍ</span>@elseif($f==='no')<span class="check-no">NO</span>@else<span class="value-empty">—</span>@endif
            </td>
            <td colspan="1" class="col-header">-</td>
            <td colspan="2" class="label-cell">Radios Baofeng</td>
            <td colspan="2"><div style="font-size: 6pt;">Nº</div>{{ $data['cabina']['radios_baofeng']['numero'] ?? '—' }}</td>
            <td colspan="2"><div style="font-size: 6pt;">Bat</div>{{ $data['cabina']['radios_baofeng']['bateria'] ?? '—' }}</td>
        </tr>
        {{-- ERA SCOTT --}}
        <tr>
            <td colspan="3" class="label-cell">ERA SCOTT 4.5</td>
            <td colspan="2"><div style="font-size: 6pt;">Cantidad</div>{{ $data['cabina']['era_scott_4_5']['cantidad'] ?? '—' }}</td>
            <td colspan="1" class="col-header">-</td>
            <td colspan="2">
                <div style="font-size: 6pt;">Funciona</div>
                @php $f = $data['cabina']['era_scott_4_5']['funcionamiento'] ?? ''; @endphp
                @if($f==='si')<span class="check-yes">SÍ</span>@elseif($f==='no')<span class="check-no">NO</span>@else<span class="value-empty">—</span>@endif
            </td>
            <td colspan="4"><div style="font-size: 6pt;">Nivel aire</div>{{ $data['cabina']['era_scott_4_5']['nivel_aire'] ?? '—' }}</td>
        </tr>
        {{-- Chaquetillas y Tablet --}}
        <tr>
            <td colspan="3" class="label-cell">Chaquetillas STEX</td>
            <td colspan="3">{{ $data['cabina']['chaquetillas_stex']['cantidad'] ?? '—' }}</td>
            <td colspan="6" class="label-cell">Lona organizadora material</td>
        </tr>
        <tr>
            <td colspan="3" class="label-cell">Tablet BR-3</td>
            <td colspan="2">
                <div style="font-size: 6pt;">Batería</div>
                @php $f = $data['cabina']['tablet_br3']['bateria'] ?? ''; @endphp
                @if($f==='si')<span class="check-yes">SÍ</span>@elseif($f==='no')<span class="check-no">NO</span>@else<span class="value-empty">—</span>@endif
            </td>
            <td colspan="1" class="col-header">-</td>
            <td colspan="6" class="label-cell">Maleta SCI</td>
        </tr>
    </table>

    {{-- HERRAMIENTAS --}}
    <table>
        <tr><th colspan="6" class="section-header">CHECK LIST HERRAMIENTAS</th></tr>
        <tr>
            <th class="col-header" style="width: 40%">Herramienta</th>
            <th class="col-header">Funciona</th>
            <th class="col-header" style="width: 40%">Novedades</th>
        </tr>
        @php $herramientas = ['ripper'=>'RIPPER (corta parabrisas)','baston_tastik'=>'Bastón Tastik','generador_honda'=>'Generador eléctrico Honda','motosierra_stihl'=>'Motosierra Stihl MS170','motor_holmatro'=>'Motor HOLMATRO','combi_lukas'=>'Combi Lukas E-Draulik','motoamoladora'=>'Motoamoladora MAKITA','sierra_sable'=>'Sierra sable HILTI','dremel'=>'Dremel y accesorios','martillo_neumatico'=>'Martillo Neumático','taladro_makita'=>'Taladro inalámbrico MAKITA','control_vetter'=>'Control cojines VETTER','esmeril_angular'=>'Esmeril angular']; @endphp
        @foreach($herramientas as $key=>$label)
        <tr>
            <td class="label-cell">{{ $label }}</td>
            <td>
                @php $v = $data['herramientas'][$key]['funciona'] ?? ''; @endphp
                @if($v==='si')<span class="check-yes">SÍ</span>@elseif($v==='no')<span class="check-no">NO</span>@else<span class="value-empty">—</span>@endif
            </td>
            <td class="value-cell">{{ $data['herramientas'][$key]['novedades'] ?? '—' }}</td>
        </tr>
        @endforeach
    </table>

    {{-- TRAUMA --}}
    <table>
        <tr><th colspan="12" class="section-header">TRAUMA</th></tr>
        <tr>
            <td colspan="3" class="label-cell">Collares cervicales</td>
            <td colspan="2"><div style="font-size: 6pt;">Adulto</div>{{ $data['trauma']['collares_cervicales']['adulto'] ?? '—' }}</td>
            <td colspan="2"><div style="font-size: 6pt;">Ped.</div>{{ $data['trauma']['collares_cervicales']['ped'] ?? '—' }}</td>
            <td colspan="5" class="col-header">-</td>
        </tr>
        <tr>
            <td colspan="3" class="label-cell">DEA</td>
            <td colspan="2">
                <div style="font-size: 6pt;">Batería</div>
                @php $f = $data['trauma']['dea']['bateria'] ?? ''; @endphp
                @if($f==='si')<span class="check-yes">SÍ</span>@elseif($f==='no')<span class="check-no">NO</span>@else<span class="value-empty">—</span>@endif
            </td>
            <td colspan="2"><div style="font-size: 6pt;">%</div>{{ $data['trauma']['dea']['pct'] ?? '—' }}</td>
            <td colspan="2" class="label-cell">Mochila Trauma</td>
            <td colspan="3">{{ $data['trauma']['mochila_trauma'] ?? '—' }}</td>
        </tr>
        <tr>
            <td colspan="3" class="label-cell">Bolso Oxigenoterapia</td>
            <td colspan="2"><div style="font-size: 6pt;">NIVEL O. 1</div>{{ $data['trauma']['bolso_oxigenoterapia']['nivelo1'] ?? '—' }}</td>
            <td colspan="2"><div style="font-size: 6pt;">NIVEL O. 2</div>{{ $data['trauma']['bolso_oxigenoterapia']['nivelo2'] ?? '—' }}</td>
            <td colspan="2" class="label-cell">Cajas guantes</td>
            <td colspan="3">{{ $data['trauma']['cajas_guantes'] ?? '—' }}</td>
        </tr>
        <tr>
            <td colspan="3" class="label-cell">Chalecos extricación</td>
            <td colspan="2"><div style="font-size: 6pt;">Adulto</div>{{ $data['trauma']['chalecos_extricacion']['adulto'] ?? '—' }}</td>
            <td colspan="2"><div style="font-size: 6pt;">PED</div>{{ $data['trauma']['chalecos_extricacion']['ped'] ?? '—' }}</td>
            <td colspan="2"><div style="font-size: 6pt;">G2</div>{{ $data['trauma']['chalecos_extricacion']['g2'] ?? '—' }}</td>
            <td colspan="3" class="label-cell">Férulas</td>
        </tr>
        <tr>
            <td colspan="3" class="label-cell">Tablas Largas</td>
            <td colspan="2"><div style="font-size: 6pt;">Techo</div>{{ $data['trauma']['tablas_largas']['techo'] ?? '—' }}</td>
            <td colspan="2" class="col-header">-</td>
            <td colspan="2" class="label-cell">Tabla Scoop</td>
            <td colspan="3">{{ $data['trauma']['tabla_scoop'] ?? '—' }}</td>
        </tr>
        <tr>
            <td colspan="3" class="label-cell">Tabla pediátrica</td>
            <td colspan="2"><div style="font-size: 6pt;">Tabla corta</div>{{ $data['trauma']['tabla_pediatrica']['tabla_corta'] ?? '—' }}</td>
            <td colspan="2" class="col-header">-</td>
            <td colspan="2" class="label-cell">Laterales</td>
            <td colspan="3">{{ $data['trauma']['laterales'] ?? '—' }}</td>
        </tr>
        <tr>
            <td colspan="6" class="col-header">-</td>
            <td colspan="2" class="label-cell">Pulpos</td>
            <td colspan="4">{{ $data['trauma']['pulpos'] ?? '—' }}</td>
        </tr>
        <tr>
            <td colspan="6" class="col-header">-</td>
            <td colspan="2" class="label-cell">Bolso TRIAGE</td>
            <td colspan="4">{{ $data['trauma']['bolso_triage'] ?? '—' }}</td>
        </tr>
    </table>

    {{-- CANTIDADES --}}
    <table>
        <tr><th colspan="12" class="section-header">INDIQUE LA CANTIDAD</th></tr>
        
        {{-- MANGUERAS --}}
        <tr><td colspan="12" class="subsection-header">MANGUERAS</td></tr>
        <tr>
            <td class="label-cell">38mm:</td><td class="value-cell">{{ $data['cantidades']['mangueras_38mm'] ?? '—' }}</td>
            <td class="label-cell">52mm:</td><td class="value-cell">{{ $data['cantidades']['mangueras_52mm'] ?? '—' }}</td>
            <td class="label-cell">75mm:</td><td class="value-cell">{{ $data['cantidades']['mangueras_75mm'] ?? '—' }}</td>
            <td class="label-cell">LDH:</td><td class="value-cell">{{ $data['cantidades']['mangueras_ldh'] ?? '—' }}</td>
            <td class="label-cell">Armada:</td><td class="value-cell">{{ $data['cantidades']['mangueras_armada_base'] ?? '—' }}</td>
            <td colspan="2"></td>
        </tr>
        
        {{-- HERRADURAS --}}
        <tr><td colspan="12" class="subsection-header">HERRADURAS</td></tr>
        <tr>
            <td class="label-cell">Cantidad:</td><td class="value-cell">{{ $data['cantidades']['herraduras_cantidad'] ?? '—' }}</td>
            <td class="label-cell">Llaves copla:</td><td class="value-cell">{{ $data['cantidades']['herraduras_llaves_copla'] ?? '—' }}</td>
            <td colspan="4" class="label-cell">Pitón Rosenbauer 52</td>
            <td colspan="4"></td>
        </tr>
        
        {{-- ATAQUES --}}
        <tr><td colspan="12" class="subsection-header">ATAQUES</td></tr>
        <tr>
            <td class="label-cell">52mm:</td><td class="value-cell">{{ $data['cantidades']['ataques_52mm'] ?? '—' }}</td>
            <td class="label-cell">75mm:</td><td class="value-cell">{{ $data['cantidades']['ataques_75mm'] ?? '—' }}</td>
            <td colspan="4" class="label-cell">Manguera L D H:</td>
            <td colspan="4" class="value-cell">{{ $data['cantidades']['ataques_manguera_ldh'] ?? '—' }}</td>
        </tr>
        
        {{-- TRASPASOS --}}
        <tr><td colspan="12" class="subsection-header">TRASPASOS</td></tr>
        <tr>
            <td class="label-cell">Cantidad:</td><td class="value-cell">{{ $data['cantidades']['traspasos_cantidad'] ?? '—' }}</td>
            <td class="label-cell">Llave grifo:</td><td class="value-cell">{{ $data['cantidades']['traspasos_llave_grifo'] ?? '—' }}</td>
            <td class="label-cell">Trifurca:</td><td class="value-cell">{{ $data['cantidades']['traspasos_trifurca'] ?? '—' }}</td>
            <td colspan="2" class="label-cell">Traspaso grifo:</td>
            <td colspan="4" class="value-cell">{{ $data['cantidades']['traspasos_traspaso_grifo'] ?? '—' }}</td>
        </tr>
        
        {{-- PROTECCIONES --}}
        <tr><td colspan="12" class="subsection-header">PROTECCIONES Y EQUIPAMIENTO</td></tr>
        <tr>
            <td class="label-cell">Cojines VETTER:</td><td class="value-cell">{{ $data['cantidades']['cojines_vetter'] ?? '—' }}</td>
            <td class="label-cell">Eslings:</td><td class="value-cell">{{ $data['cantidades']['eslings'] ?? '—' }}</td>
            <td class="label-cell">Tecle cadena:</td><td class="value-cell">{{ $data['cantidades']['tecle_cadena'] ?? '—' }}</td>
            <td class="label-cell">Caja herramientas:</td><td class="value-cell">{{ $data['cantidades']['caja_herramientas'] ?? '—' }}</td>
            <td class="label-cell">Cubre airbag:</td><td class="value-cell">{{ $data['cantidades']['cubre_airbag'] ?? '—' }}</td>
            <td colspan="2"></td>
        </tr>
        
        {{-- CUÑAS --}}
        <tr><td colspan="12" class="subsection-header">CUÑAS</td></tr>
        <tr>
            <td class="label-cell">Biseladas:</td><td class="value-cell">{{ $data['cantidades']['cunas_biseladas'] ?? '—' }}</td>
            <td class="label-cell">Bloques:</td><td class="value-cell">{{ $data['cantidades']['cunas_bloques'] ?? '—' }}</td>
            <td class="label-cell">Escalonadas:</td><td class="value-cell">{{ $data['cantidades']['cunas_escalonadas'] ?? '—' }}</td>
            <td class="label-cell">Planas:</td><td class="value-cell">{{ $data['cantidades']['cunas_plan'] ?? '—' }}</td>
            <td class="label-cell">Combos 2L:</td><td class="value-cell">{{ $data['cantidades']['cunas_combos_2l'] ?? '—' }}</td>
            <td colspan="2"></td>
        </tr>
        
        {{-- CAJONERAS --}}
        <tr><td colspan="12" class="subsection-header">CAJONERAS LATERALES</td></tr>
        <tr>
            <td class="label-cell">Barretilla:</td><td class="value-cell">{{ $data['cantidades']['cajoneras_barretilla'] ?? '—' }}</td>
            <td class="label-cell">Napoleón:</td><td class="value-cell">{{ $data['cantidades']['cajoneras_napoleon'] ?? '—' }}</td>
            <td class="label-cell">Stab Fast:</td><td class="value-cell">{{ $data['cantidades']['cajoneras_stab_fast'] ?? '—' }}</td>
            <td class="label-cell">Jack:</td><td class="value-cell">{{ $data['cantidades']['cajoneras_jack'] ?? '—' }}</td>
            <td colspan="4"></td>
        </tr>
        
        {{-- ESCALAS --}}
        <tr><td colspan="12" class="subsection-header">ESCALAS</td></tr>
        <tr>
            <td class="label-cell">2 cuerpos 12m:</td><td class="value-cell">{{ $data['cantidades']['escalas_2c_12m'] ?? '—' }}</td>
            <td class="label-cell">2 cuerpos 8m:</td><td class="value-cell">{{ $data['cantidades']['escalas_2c_8m'] ?? '—' }}</td>
            <td class="label-cell">Plegable:</td><td class="value-cell">{{ $data['cantidades']['escalas_plegable'] ?? '—' }}</td>
            <td colspan="6"></td>
        </tr>
        
        {{-- OTROS --}}
        <tr>
            <td class="label-cell">Lona pilares Rozón:</td><td class="value-cell">{{ $data['cantidades']['lona_pilares_rozon'] ?? '—' }}</td>
            <td class="label-cell">Chuzo:</td><td class="value-cell">{{ $data['cantidades']['lona_pilares_chuzo'] ?? '—' }}</td>
            <td class="label-cell">Bicheros Conos:</td><td class="value-cell">{{ $data['cantidades']['bicheros_conos'] ?? '—' }}</td>
            <td class="label-cell">Halligan:</td><td class="value-cell">{{ $data['cantidades']['bicheros_halligan'] ?? '—' }}</td>
            <td class="label-cell">Focos LED Hacha:</td><td class="value-cell">{{ $data['cantidades']['focos_led_hacha_suela'] ?? '—' }}</td>
            <td colspan="2"></td>
        </tr>
        <tr>
            <td class="label-cell">Focos LED TNT:</td><td class="value-cell">{{ $data['cantidades']['focos_led_tnt'] ?? '—' }}</td>
            <td class="label-cell">Carrete Hacha Bombero:</td><td class="value-cell">{{ $data['cantidades']['carrete_hacha_bombero'] ?? '—' }}</td>
            <td class="label-cell">Carrete Halligan:</td><td class="value-cell">{{ $data['cantidades']['carrete_halligan'] ?? '—' }}</td>
            <td class="label-cell">Cadenas Extintor:</td><td class="value-cell">{{ $data['cantidades']['cadenas_extintor_agua'] ?? '—' }}</td>
            <td class="label-cell">Pitón monitor:</td><td class="value-cell">{{ $data['cantidades']['cadenas_piton_monitor'] ?? '—' }}</td>
            <td colspan="2"></td>
        </tr>
        <tr>
            <td class="label-cell">Corta parabrisas Force:</td><td class="value-cell">{{ $data['cantidades']['corta_parabrisas_force'] ?? '—' }}</td>
            <td class="label-cell">Cámara:</td><td class="value-cell">{{ $data['cantidades']['corta_parabrisas_camara'] ?? '—' }}</td>
            <td class="label-cell">Bomba Espalda:</td><td class="value-cell">{{ $data['cantidades']['corta_parabrisas_bomba'] ?? '—' }}</td>
            <td colspan="6"></td>
        </tr>
    </table>

    {{-- GENERAL --}}
    <table>
        <tr><th colspan="12" class="section-header">GENERAL UNIDAD</th></tr>
        <tr>
            <td class="label-cell">Alarmas:</td>
            <td>
                @php $a = $data['general']['alarmas'] ?? ''; @endphp
                @if($a==='si')<span class="check-yes">SÍ</span>@elseif($a==='no')<span class="check-no">NO</span>@else<span class="value-empty">—</span>@endif
            </td>
            <td class="label-cell">Aseo:</td>
            <td>
                @php $a = $data['general']['aseo'] ?? ''; @endphp
                @if($a==='si')<span class="check-yes">SÍ</span>@elseif($a==='no')<span class="check-no">NO</span>@else<span class="value-empty">—</span>@endif
            </td>
            <td class="label-cell">Comb. Bidón:</td><td class="value-cell">{{ $data['general']['nivel_combustible_bidon'] ?? '—' }}</td>
            <td class="label-cell">Aceite Mezcla:</td><td class="value-cell">{{ $data['general']['nivel_aceite_mezcla'] ?? '—' }}</td>
            <td class="label-cell">Comb. Unidad:</td><td class="value-cell">{{ $data['general']['nivel_combustible_unidad'] ?? '—' }}</td>
            <td class="label-cell">Agua:</td><td class="value-cell">{{ $data['general']['nivel_agua_unidad'] ?? '—' }}</td>
        </tr>
    </table>

    {{-- Observaciones --}}
    <div style="margin-top: 10px;">
        <div style="font-weight: 700; font-size: 7pt; color: #475569;">OBSERVACIONES GENERALES</div>
        <div style="border: 1px solid #cbd5e1; padding: 6px; background: #f8fafc; min-height: 30px; font-size: 7pt;">
            {{ $data['observaciones_generales'] ?? 'Sin observaciones' }}
        </div>
    </div>

    {{-- Firmas --}}
    <div class="firma-section">
        <table class="firma-table">
            <tr>
                <td>
                    <div class="firma-line">
                        <strong>Firma Responsable</strong><br>
                        <span style="font-size: 7pt; color: #64748b;">{{ $planilla->creador?->name ?? '—' }}</span>
                    </div>
                </td>
                <td>
                    <div class="firma-line">
                        <strong>Visto Bueno Jefe Guardia</strong>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <div class="footer">
        GuardiAPP - Sistema de Gestión de Cuerpo de Bomberos - {{ now()->format('d/m/Y H:i') }}
    </div>
</body>
</html>
