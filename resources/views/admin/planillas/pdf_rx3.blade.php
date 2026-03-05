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
        {{-- Bastón Tastik y Linternas APASO --}}
        <tr>
            <td colspan="3" class="label-cell">Bastón Tastik</td>
            <td colspan="2">
                <div style="font-size: 6pt;">Funciona</div>
                @php $f = $data['cabina']['baston_tastik']['funcionamiento'] ?? ''; @endphp
                @if($f==='si')<span class="check-yes">SÍ</span>@elseif($f==='no')<span class="check-no">NO</span>@else<span class="value-empty">—</span>@endif
            </td>
            <td colspan="1" class="col-header">-</td>
            <td colspan="3" class="label-cell">Linternas APASO L 3000</td>
            <td colspan="3"><div style="font-size: 6pt;">Nivel aire</div>{{ $data['cabina']['linternas_apaso']['nivel_aire'] ?? '—' }}</td>
        </tr>
        {{-- ERA MSA G1 --}}
        <tr>
            <td colspan="3" class="label-cell">ERA MSA G1</td>
            <td colspan="2"><div style="font-size: 6pt;">Cantidad</div>{{ $data['cabina']['era_msa_g1']['cantidad'] ?? '—' }}</td>
            <td colspan="1" class="col-header">-</td>
            <td colspan="2">
                <div style="font-size: 6pt;">Funciona</div>
                @php $f = $data['cabina']['era_msa_g1']['funcionamiento'] ?? ''; @endphp
                @if($f==='si')<span class="check-yes">SÍ</span>@elseif($f==='no')<span class="check-no">NO</span>@else<span class="value-empty">—</span>@endif
            </td>
            <td colspan="1" class="col-header">-</td>
            <td colspan="3"><div style="font-size: 6pt;">Nivel aire</div>{{ $data['cabina']['era_msa_g1']['nivel_aire'] ?? '—' }}</td>
        </tr>
        {{-- Chaquetillas y Tablet --}}
        <tr>
            <td colspan="3" class="label-cell">Chaquetillas STEX</td>
            <td colspan="3">{{ $data['cabina']['chaquetillas_stex']['cantidad'] ?? '—' }}</td>
            <td colspan="6" class="label-cell">Línea de vida B&R</td>
        </tr>
        <tr>
            <td colspan="3" class="label-cell">Tablet RX-3</td>
            <td colspan="2">
                <div style="font-size: 6pt;">Batería</div>
                @php $f = $data['cabina']['tablet_rx3']['bateria'] ?? ''; @endphp
                @if($f==='si')<span class="check-yes">SÍ</span>@elseif($f==='no')<span class="check-no">NO</span>@else<span class="value-empty">—</span>@endif
            </td>
            <td colspan="1" class="col-header">-</td>
            <td colspan="2"><div style="font-size: 6pt;">Radios</div>{{ $data['cabina']['tablet_rx3']['radios'] ?? '—' }}</td>
            <td colspan="1" class="col-header">-</td>
            <td colspan="3" class="label-cell">Cortacinturón</td>
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
        @php $herramientas = ['demoledor_makita'=>'Demoledor MAKITA','combi_lukas'=>'Combi Lukas E-Draulik','cortadora_plasma'=>'Cortadora de Plasma','dremel'=>'DREMEL','sierra_sable'=>'Sierra sable MAKITA','pistola_airgun'=>'Pistola neumática AIRGUN','taladro_makita'=>'Taladro MAKITA','esmeril_angular'=>'Esmeril angular 125mm','sierra_circular'=>'Sierra circular 7 1/4"','rotomartillo'=>'Rotomartillo DEWALT','control_vetter'=>'Control VETTER baja presión','grupo_electrogeno'=>'Grupo electrógeno 30 KVA','motor_weber'=>'Motor WEBER 4T','cizalla_weber'=>'Cizalla WEBER','cizalla_lukas'=>'Cizalla LUKAS','winche_rx3'=>'Winche RX-3']; @endphp
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
            <td colspan="3" class="label-cell">2 Bolsos Oxigenoterapia</td>
            <td colspan="2"><div style="font-size: 6pt;">NIVEL O. 1</div>{{ $data['trauma']['bolsos_oxigenoterapia']['nivelo1'] ?? '—' }}</td>
            <td colspan="2"><div style="font-size: 6pt;">NIVEL O. 2</div>{{ $data['trauma']['bolsos_oxigenoterapia']['nivelo2'] ?? '—' }}</td>
            <td colspan="2" class="label-cell">Cajas guantes</td>
            <td colspan="3">{{ $data['trauma']['cajas_guantes'] ?? '—' }}</td>
        </tr>
        <tr>
            <td colspan="3" class="label-cell">Chalecos extricación</td>
            <td colspan="2"><div style="font-size: 6pt;">Bolso TRIAGE</div>{{ $data['trauma']['chalecos_extricacion']['bolso_triage'] ?? '—' }}</td>
            <td colspan="2"><div style="font-size: 6pt;">Tabla corta</div>{{ $data['trauma']['chalecos_extricacion']['tabla_corta'] ?? '—' }}</td>
            <td colspan="2" class="label-cell">Férulas</td>
            <td colspan="3" class="col-header">-</td>
        </tr>
        <tr>
            <td colspan="3" class="label-cell">Maleta Quemados</td>
            <td colspan="3">{{ $data['trauma']['maleta_quemados'] ?? '—' }}</td>
            <td colspan="2" class="label-cell">Tablas Largas</td>
            <td colspan="4">{{ $data['trauma']['tablas_largas'] ?? '—' }}</td>
        </tr>
        <tr>
            <td colspan="6" class="col-header">-</td>
            <td colspan="2" class="label-cell">Laterales</td>
            <td colspan="4">{{ $data['trauma']['laterales'] ?? '—' }}</td>
        </tr>
        <tr>
            <td colspan="6" class="col-header">-</td>
            <td colspan="2" class="label-cell">Pulpos</td>
            <td colspan="4">{{ $data['trauma']['pulpos'] ?? '—' }}</td>
        </tr>
    </table>

    {{-- CANTIDADES --}}
    <table>
        <tr><th colspan="12" class="section-header">INDIQUE LA CANTIDAD</th></tr>
        
        {{-- Cilindros y Equipamiento --}}
        <tr>
            <td colspan="4" class="label-cell">Cilindros cojines levante</td>
            <td colspan="4" class="label-cell">Cojines Paratech:</td><td class="value-cell">{{ $data['cantidades']['cojines_paratech'] ?? '—' }}</td>
            <td colspan="3" class="label-cell">Tirfor Rescate:</td><td class="value-cell">{{ $data['cantidades']['tirfor_rescate'] ?? '—' }}</td>
        </tr>
        <tr>
            <td colspan="4" class="label-cell">Focos 1000W y trípode</td>
            <td colspan="4" class="label-cell">Caja herramientas:</td><td class="value-cell">{{ $data['cantidades']['caja_herramientas'] ?? '—' }}</td>
            <td colspan="3" class="label-cell">Cubre Airbag:</td><td class="value-cell">{{ $data['cantidades']['cubre_airbag'] ?? '—' }}</td>
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
        
        {{-- ESLINGS --}}
        <tr><td colspan="12" class="subsection-header">ESLINGS</td></tr>
        <tr>
            <td colspan="4" class="label-cell">Naranjas - Barretilla:</td><td class="value-cell">{{ $data['cantidades']['eslings_naranjas_barretilla'] ?? '—' }}</td>
            <td colspan="3" class="label-cell">Halligan:</td><td class="value-cell">{{ $data['cantidades']['eslings_naranjas_halligan'] ?? '—' }}</td>
            <td colspan="2" class="label-cell">Napoleón:</td><td class="value-cell" colspan="2">{{ $data['cantidades']['eslings_azules_napoleon'] ?? '—' }}</td>
        </tr>
        <tr>
            <td colspan="4" class="label-cell">Azules - TNT:</td><td class="value-cell">{{ $data['cantidades']['eslings_azules_tnt'] ?? '—' }}</td>
            <td colspan="3" class="label-cell">Ojo a ojo - Hacha:</td><td class="value-cell">{{ $data['cantidades']['eslings_ojo_hacha'] ?? '—' }}</td>
            <td colspan="2" class="label-cell">Estacas:</td><td class="value-cell" colspan="2">{{ $data['cantidades']['eslings_ojo_estacas'] ?? '—' }}</td>
        </tr>
        
        {{-- CADENAS WEBER --}}
        <tr>
            <td colspan="4" class="label-cell">Cadenas WEBER</td>
            <td colspan="4" class="label-cell">Soporte RAM:</td><td class="value-cell">{{ $data['cantidades']['cadenas_soporte_weber'] ?? '—' }}</td>
            <td colspan="3" class="label-cell">Muela LUKAS:</td><td class="value-cell">{{ $data['cantidades']['cadenas_muela_lukas'] ?? '—' }}</td>
        </tr>
        
        {{-- PARATECH --}}
        <tr>
            <td colspan="4" class="label-cell">Estabilizadores PARATECH</td>
            <td colspan="4" class="label-cell">Extensiones:</td><td class="value-cell">{{ $data['cantidades']['paratech_extensiones'] ?? '—' }}</td>
            <td colspan="3" class="label-cell">Bases:</td><td class="value-cell">{{ $data['cantidades']['paratech_bases'] ?? '—' }}</td>
        </tr>
        <tr>
            <td colspan="4" class="label-cell">Puntas</td>
            <td colspan="4" class="label-cell">Llaveros:</td><td class="value-cell">{{ $data['cantidades']['puntas_llaveros'] ?? '—' }}</td>
            <td colspan="3" class="label-cell">Eslings:</td><td class="value-cell">{{ $data['cantidades']['puntas_eslings'] ?? '—' }}</td>
        </tr>
        
        {{-- PLATAFORMA --}}
        <tr>
            <td colspan="4" class="label-cell">Plataforma Rescate</td>
            <td colspan="4" class="label-cell">Escalas:</td><td class="value-cell">{{ $data['cantidades']['plataforma_escalas'] ?? '—' }}</td>
            <td colspan="3" class="label-cell">Conos:</td><td class="value-cell">{{ $data['cantidades']['plataforma_conos'] ?? '—' }}</td>
        </tr>
        
        {{-- VETTER --}}
        <tr>
            <td colspan="4" class="label-cell">Colchón Vetter</td>
            <td colspan="4" class="label-cell">Apertura puertas:</td><td class="value-cell">{{ $data['cantidades']['vetter_apertura_puertas'] ?? '—' }}</td>
            <td colspan="3" class="label-cell">Palas:</td><td class="value-cell">{{ $data['cantidades']['vetter_palas'] ?? '—' }}</td>
        </tr>
        
        {{-- PAQUETE CIRCULAR --}}
        <tr>
            <td colspan="4" class="label-cell">Paquete Circular</td>
            <td colspan="4" class="label-cell">Llave grifo:</td><td class="value-cell">{{ $data['cantidades']['paquete_llave_grifo'] ?? '—' }}</td>
            <td colspan="3" class="label-cell">Traspaso grifo:</td><td class="value-cell">{{ $data['cantidades']['paquete_traspaso_grifo'] ?? '—' }}</td>
        </tr>
        
        {{-- MANGUERA --}}
        <tr>
            <td colspan="4" class="label-cell">Manguera alimentación</td>
            <td colspan="4" class="label-cell">Traspasos:</td><td class="value-cell">{{ $data['cantidades']['manguera_traspasos'] ?? '—' }}</td>
            <td colspan="3" class="label-cell">Llaves copla:</td><td class="value-cell">{{ $data['cantidades']['manguera_llaves_copla'] ?? '—' }}</td>
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
        <tr>
            <td class="label-cell">Aceite Cadena:</td><td class="value-cell" colspan="11">{{ $data['general']['nivel_aceite_cadena'] ?? '—' }}</td>
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
