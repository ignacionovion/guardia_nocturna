<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        @page { size: A4 landscape; margin: 15mm; }
        * { font-family: DejaVu Sans, Arial, Helvetica, sans-serif; box-sizing: border-box; }
        body { font-size: 9px; color: #1e293b; margin: 0; padding: 0; }
        
        /* Marca de agua con logo */
        .watermark {
            position: fixed;
            bottom: -30mm;
            right: -40mm;
            width: 180mm;
            height: 180mm;
            opacity: 0.08;
            z-index: -1;
            pointer-events: none;
        }
        
        .header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 3px double #1e40af;
            padding-bottom: 10px;
        }
        
        .header-title {
            font-size: 16px;
            font-weight: 900;
            color: #1e40af;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 5px;
        }
        
        .header-subtitle {
            font-size: 11px;
            color: #64748b;
            font-weight: 600;
        }
        
        .event-info {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin-bottom: 15px;
            font-size: 9px;
        }
        
        .info-item {
            background: #f1f5f9;
            padding: 4px 12px;
            border-radius: 4px;
            border-left: 3px solid #1e40af;
        }
        
        .info-label { color: #64748b; font-weight: 600; }
        .info-value { color: #1e293b; font-weight: 700; }
        
        table { 
            width: 100%; 
            border-collapse: collapse; 
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        th { 
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%); 
            color: #fff; 
            font-weight: 700; 
            text-align: center; 
            padding: 8px 6px;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border: 1px solid #1e40af;
        }
        
        .time-col { 
            width: 12%; 
            background: linear-gradient(135deg, #e2e8f0 0%, #cbd5e1 100%); 
            font-weight: 700; 
            text-align: center; 
            color: #1e293b;
            border: 1px solid #94a3b8;
            font-size: 9px;
        }
        
        .time-range {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 2px;
        }
        
        .time-start { font-size: 10px; font-weight: 800; color: #1e40af; }
        .time-separator { font-size: 7px; color: #64748b; }
        .time-end { font-size: 10px; font-weight: 800; color: #1e40af; }
        
        td { 
            border: 1px solid #cbd5e1; 
            padding: 6px; 
            vertical-align: top; 
            font-size: 8px;
            background: #fff;
        }
        
        .date-head { 
            background: linear-gradient(135deg, #3b82f6 0%, #60a5fa 100%); 
            color: #fff; 
            font-weight: 700;
            font-size: 10px;
        }
        
        .assignment {
            padding: 3px 0;
            border-bottom: 1px dotted #e2e8f0;
        }
        .assignment:last-child { border-bottom: none; }
        
        .firefighter-name {
            font-weight: 700;
            color: #1e293b;
            font-size: 8px;
            line-height: 1.3;
        }
        
        .firefighter-rut {
            font-size: 7px;
            color: #64748b;
        }
        
        .badge {
            display: inline-block;
            font-size: 6px;
            padding: 1px 4px;
            border-radius: 2px;
            margin-left: 4px;
            font-weight: 700;
            text-transform: uppercase;
        }
        
        .badge-ref { background: #dbeafe; color: #1e40af; }
        .badge-reemp { background: #fce7f3; color: #be185d; }
        
        .empty-cell {
            color: #94a3b8;
            font-style: italic;
            text-align: center;
            font-size: 9px;
        }
        
        tbody tr:nth-child(even) { background: #f8fafc; }
        tbody tr:nth-child(even) td { background: #f8fafc; }
        
        .footer {
            position: fixed;
            bottom: 10mm;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 8px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 5px;
        }
        
        .page-number:after { content: counter(page); }
    </style>
</head>
<body>
    <!-- Marca de agua con logo -->
    <img src="{{ public_path('brand/Logo png Alta Def.png') }}" class="watermark" alt="">
    
    <div class="header">
        <div class="header-title">Calendario Guardias Preventivas</div>
        <div class="header-subtitle">{{ strtoupper($event->title) }}</div>
    </div>
    
    <div class="event-info">
        <div class="info-item">
            <span class="info-label">Período: </span>
            <span class="info-value">{{ $event->start_date->format('d/m/Y') }} - {{ $event->end_date->format('d/m/Y') }}</span>
        </div>
        <div class="info-item">
            <span class="info-label">Zona Horaria: </span>
            <span class="info-value">{{ $event->timezone }}</span>
        </div>
        <div class="info-item">
            <span class="info-label">Generado: </span>
            <span class="info-value">{{ now()->format('d/m/Y H:i') }}</span>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th class="time-col">Horario</th>
                @foreach($dates as $d)
                    <th class="date-head">
                        {{ $d->locale('es')->isoFormat('dddd') }}<br>
                        <span style="font-size: 11px;">{{ $d->format('d M Y') }}</span>
                    </th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($templates as $tpl)
                <tr>
                    <td class="time-col">
                        <div class="time-range">
                            <span class="time-start">{{ substr((string) $tpl->start_time, 0, 5) }}</span>
                            <span class="time-separator">a</span>
                            <span class="time-end">{{ substr((string) $tpl->end_time, 0, 5) }}</span>
                        </div>
                    </td>
                    @foreach($dates as $d)
                        @php
                            $key = $d->toDateString() . '|' . (int) $tpl->sort_order;
                            $shift = $shiftMap->get($key);
                            $assignments = $shift ? $shift->assignments : collect();
                        @endphp
                        <td>
                            @if($assignments->isEmpty())
                                <div class="empty-cell">—</div>
                            @else
                                @foreach($assignments as $a)
                                    @php
                                        $f = $a->firefighter;
                                        $nombre = trim(($f?->apellido_paterno ?? '') . ' ' . ($f?->nombres ?? ''));
                                    @endphp
                                    <div class="assignment">
                                        <span class="firefighter-name">
                                            {{ strtoupper($nombre) }}
                                            @if($a->es_refuerzo)
                                                <span class="badge badge-ref">REF</span>
                                            @elseif($a->reemplaza_a_bombero_id)
                                                <span class="badge badge-reemp">REEMP</span>
                                            @endif
                                        </span>
                                        @if($f?->rut)
                                            <div class="firefighter-rut">{{ $f->rut }}</div>
                                        @endif
                                    </div>
                                @endforeach
                            @endif
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
    
    <div class="footer">
        GuardiaAPP - Sistema de Gestión de Cuerpo de Bomberos
    </div>
</body>
</html>
