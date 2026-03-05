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
        <tr><td colspan="12" class="subsection-header">ERA MSA G 1</td></tr>
        <tr>
            @foreach(['1','2','3','4','5','6','7','OBAC','Radios','','Radio 1','Radio 2'] as $label)
                <th class="col-header">{{ $label }}</th>
            @endforeach
        </tr>
        <tr>
            @for($i=1; $i<=7; $i++)
                <td class="value-cell">{{ $data['cabina']['era_msa_g1']['num_'.$i] ?? '—' }}</td>
            @endfor
            <td class="value-cell">{{ $data['cabina']['era_msa_g1']['obac_m7'] ?? '—' }}</td>
            <td class="value-cell">{{ $data['cabina']['era_msa_g1']['radios'] ?? '—' }}</td>
            <td class="col-header">-</td>
            <td class="value-cell">{{ $data['cabina']['era_msa_g1']['radio_1'] ?? '—' }}</td>
            <td class="value-cell">{{ $data['cabina']['era_msa_g1']['radio_2'] ?? '—' }}</td>
        </tr>
        <tr>
            <td colspan="3" class="label-cell">Linterna NIGHTSTICK</td>
            <td colspan="3">
                <div style="font-size: 6pt;">Funciona</div>
                @php $f = $data['cabina']['linterna_nightstick']['funcionamiento'] ?? ''; @endphp
                @if($f==='si')<span class="check-yes">SÍ</span>@elseif($f==='no')<span class="check-no">NO</span>@else<span class="value-empty">—</span>@endif
            </td>
            <td colspan="3"><div style="font-size: 6pt;">Cantidad</div>{{ $data['cabina']['linterna_nightstick']['cantidad'] ?? '—' }}</td>
            <td colspan="3"><div style="font-size: 6pt;">Línea vida B&R</div>{{ $data['cabina']['linterna_nightstick']['linea_vida'] ?? '—' }}</td>
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
        @php $herramientas = ['camara_termal'=>'Cámara Termal','baston_tastik'=>'Bastón Tastik','detector_gas'=>'Detector Gas TIF8900','motosierra_cutter'=>'Motosierra CUTTER','taladro_makita'=>'Taladro MAKITA','motobomba_rosenbauer'=>'Motobomba Rosenbauer','aspirador_nautilus'=>'Aspirador NAUTILUS','motoamoladora_m14'=>'Motoamoladora M14','motosierra_stihl'=>'MOTOSIERRA STIHL','motor_electrogeno'=>'Motor electrógeno RS14','focos_led'=>'Focos LED 100w','foco_inalambrico'=>'Foco inalámbrico Makita','winche_b3'=>'Winche B-3','ventilador_rosenbauer'=>'Ventilador Rosenbauer']; @endphp
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

    {{-- CANTIDADES --}}
    <table>
        <tr><th colspan="12" class="section-header">INDIQUE LA CANTIDAD</th></tr>
        
        {{-- Mochila Trauma --}}
        <tr><td colspan="12" class="subsection-header">Mochila de Trauma y Cilindro O2</td></tr>
        <tr>
            <td colspan="4" class="label-cell">NIVEL O: {{ $data['cantidades']['mochila_trauma']['nivelo'] ?? '—' }}</td>
            <td colspan="4" class="label-cell">Kit Inmovilización: {{ $data['cantidades']['mochila_trauma']['kit_inmovilizacion'] ?? '—' }}</td>
            <td colspan="4" class="label-cell">Conos: {{ $data['cantidades']['mochila_trauma']['conos'] ?? '—' }}</td>
        </tr>
        
        {{-- Mangueras --}}
        <tr><td colspan="12" class="subsection-header">MANGUERAS</td></tr>
        <tr>
            <td class="label-cell">52:</td><td class="value-cell">{{ $data['cantidades']['mangueras_52'] ?? '—' }}</td>
            <td class="label-cell">75:</td><td class="value-cell">{{ $data['cantidades']['mangueras_75'] ?? '—' }}</td>
            <td class="label-cell">LDH:</td><td class="value-cell">{{ $data['cantidades']['mangueras_ldh'] ?? '—' }}</td>
            <td class="label-cell">Armada:</td><td class="value-cell">{{ $data['cantidades']['mangueras_armada_base'] ?? '—' }}</td>
            <td colspan="4"></td>
        </tr>
        
        {{-- Paquete Circular --}}
        <tr><td colspan="12" class="subsection-header">PAQUETE CIRCULAR</td></tr>
        <tr>
            <td class="label-cell">Herraduras:</td><td class="value-cell">{{ $data['cantidades']['paquete_herraduras'] ?? '—' }}</td>
            <td class="label-cell">Carretes:</td><td class="value-cell" colspan="9">{{ $data['cantidades']['paquete_carretes'] ?? '—' }}</td>
        </tr>
        
        {{-- Ataques --}}
        <tr><td colspan="12" class="subsection-header">ATAQUES</td></tr>
        <tr>
            <td class="label-cell">52:</td><td class="value-cell">{{ $data['cantidades']['ataques_52'] ?? '—' }}</td>
            <td class="label-cell">75:</td><td class="value-cell">{{ $data['cantidades']['ataques_75'] ?? '—' }}</td>
            <td class="label-cell">Cilindros MSA:</td><td class="value-cell" colspan="7">{{ $data['cantidades']['ataques_cilindros_msa'] ?? '—' }}</td>
        </tr>
        
        {{-- Traspasos --}}
        <tr><td colspan="12" class="subsection-header">TRASPASOS</td></tr>
        <tr>
            <td class="label-cell">Llave grifo:</td><td class="value-cell">{{ $data['cantidades']['traspasos_llave_grifo'] ?? '—' }}</td>
            <td class="label-cell">Traspaso grifo:</td><td class="value-cell" colspan="9">{{ $data['cantidades']['traspasos_traspaso_grifo'] ?? '—' }}</td>
        </tr>
        
        {{-- Llaves de Copla --}}
        <tr><td colspan="12" class="subsection-header">LLAVES DE COPLA</td></tr>
        <tr>
            <td class="label-cell">Llave piso:</td><td class="value-cell">{{ $data['cantidades']['llaves_llave_piso'] ?? '—' }}</td>
            <td class="label-cell">Manguera 75:</td><td class="value-cell" colspan="9">{{ $data['cantidades']['llaves_manguera_75'] ?? '—' }}</td>
        </tr>
        
        {{-- Escalas, Combo, Bicheros, Caja, Gremio, Filtro, Kit, Bidón, Extintor --}}
        <tr><td colspan="12" class="subsection-header">EQUIPAMIENTO ADICIONAL</td></tr>
        <tr>
            <td class="label-cell">Escalas Puntas:</td><td class="value-cell">{{ $data['cantidades']['escalas_puntas_taladro'] ?? '—' }}</td>
            <td class="label-cell">Napoleón:</td><td class="value-cell">{{ $data['cantidades']['escalas_napoleon'] ?? '—' }}</td>
            <td class="label-cell">Bidón Motosierra:</td><td class="value-cell">{{ $data['cantidades']['combo_bidon_motosierra'] ?? '—' }}</td>
            <td class="label-cell">Hacha Bombero:</td><td class="value-cell">{{ $data['cantidades']['combo_hacha_bombero'] ?? '—' }}</td>
            <td class="label-cell">Barretilla:</td><td class="value-cell">{{ $data['cantidades']['bicheros_barretilla'] ?? '—' }}</td>
            <td class="label-cell">Halligan:</td><td class="value-cell">{{ $data['cantidades']['bicheros_halligan'] ?? '—' }}</td>
        </tr>
        <tr>
            <td class="label-cell">Hacha Suela:</td><td class="value-cell">{{ $data['cantidades']['caja_hacha_suela'] ?? '—' }}</td>
            <td class="label-cell">TNT:</td><td class="value-cell">{{ $data['cantidades']['caja_tnt'] ?? '—' }}</td>
            <td class="label-cell">Kit Entrada:</td><td class="value-cell">{{ $data['cantidades']['gremio_kit_entrada'] ?? '—' }}</td>
            <td class="label-cell">Trifurca:</td><td class="value-cell">{{ $data['cantidades']['gremio_trifurca'] ?? '—' }}</td>
            <td class="label-cell">Filtro Siamesa:</td><td class="value-cell">{{ $data['cantidades']['filtro_siamesa'] ?? '—' }}</td>
            <td class="label-cell">Pitones 50:</td><td class="value-cell">{{ $data['cantidades']['filtro_pitones_50'] ?? '—' }}</td>
        </tr>
        <tr>
            <td class="label-cell">Pitón DIN 50:</td><td class="value-cell">{{ $data['cantidades']['kit_piton_din_50'] ?? '—' }}</td>
            <td class="label-cell">Pitones 70:</td><td class="value-cell">{{ $data['cantidades']['kit_pitones_70'] ?? '—' }}</td>
            <td class="label-cell">Bidón Chorizos:</td><td class="value-cell">{{ $data['cantidades']['bidon_chorizos'] ?? '—' }}</td>
            <td class="label-cell">Pasatiras:</td><td class="value-cell">{{ $data['cantidades']['bidon_pasatiras'] ?? '—' }}</td>
            <td class="label-cell">Ext. Carretes:</td><td class="value-cell">{{ $data['cantidades']['extintor_carretes'] ?? '—' }}</td>
            <td class="label-cell">Pitón Espuma:</td><td class="value-cell">{{ $data['cantidades']['extintor_piton_espuma'] ?? '—' }}</td>
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

