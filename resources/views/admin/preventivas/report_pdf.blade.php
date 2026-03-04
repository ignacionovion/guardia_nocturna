<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte Preventiva - {{ $event->title }}</title>
    <style>
        @page { size: A4 landscape; margin: 12mm; }
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 9pt; margin: 0; color: #1e293b; }
        
        /* Marca de agua */
        .watermark {
            position: fixed;
            bottom: -40mm;
            right: -50mm;
            width: 200mm;
            height: 200mm;
            opacity: 0.06;
            z-index: -1;
            pointer-events: none;
        }
        
        .header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 3px double #1e40af;
            padding-bottom: 10px;
        }
        
        h1 {
            font-size: 18pt;
            margin: 0;
            color: #1e40af;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .subtitle {
            font-size: 11pt;
            color: #64748b;
            margin-top: 5px;
            font-weight: 600;
        }
        
        .header-info {
            margin-bottom: 15px;
            background: #f8fafc;
            padding: 10px;
            border-radius: 6px;
        }
        
        .header-info table { width: 100%; border-collapse: collapse; }
        .header-info td { padding: 4px 0; font-size: 9pt; }
        .header-info .label { font-weight: 700; color: #64748b; width: 120px; }
        .header-info .value { color: #1e293b; font-weight: 600; }
        
        .stats {
            display: flex;
            gap: 12px;
            margin-bottom: 15px;
            flex-wrap: wrap;
        }
        
        .stat-box {
            flex: 1;
            min-width: 100px;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 10px;
            text-align: center;
        }
        
        .stat-box .number {
            font-size: 16pt;
            font-weight: 800;
            color: #1e40af;
        }
        
        .stat-box .label {
            font-size: 7pt;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 3px;
        }
        
        .shift-header {
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
            color: #fff;
            padding: 10px 12px;
            margin-top: 15px;
            border-radius: 6px 6px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .shift-header h3 {
            margin: 0;
            font-size: 11pt;
            font-weight: 700;
        }
        
        .shift-header .meta {
            font-size: 8pt;
            opacity: 0.9;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 0;
            font-size: 8pt;
        }
        
        th {
            background: #f1f5f9;
            border: 1px solid #cbd5e1;
            padding: 8px 6px;
            text-align: left;
            font-weight: 700;
            color: #475569;
            text-transform: uppercase;
            font-size: 7pt;
            letter-spacing: 0.5px;
        }
        
        td {
            border: 1px solid #e2e8f0;
            padding: 6px;
            vertical-align: middle;
        }
        
        tr:nth-child(even) { background: #f8fafc; }
        
        .firefighter-name {
            font-weight: 700;
            color: #1e293b;
        }
        
        .firefighter-sub {
            font-size: 7pt;
            color: #64748b;
        }
        
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 7pt;
            font-weight: 700;
            text-transform: uppercase;
        }
        
        .badge-titular { background: #e2e8f0; color: #475569; }
        .badge-refuerzo { background: #dbeafe; color: #1e40af; }
        .badge-reemplazo { background: #fce7f3; color: #be185d; }
        .badge-reemplazado { background: #fee2e2; color: #dc2626; text-decoration: line-through; }
        
        .status-present {
            background: #dcfce7;
            color: #166534;
            padding: 3px 8px;
            border-radius: 4px;
            font-weight: 700;
            font-size: 7pt;
        }
        
        .status-pending {
            background: #fef3c7;
            color: #92400e;
            padding: 3px 8px;
            border-radius: 4px;
            font-weight: 700;
            font-size: 7pt;
        }
        
        .status-reemplazado {
            background: #fee2e2;
            color: #991b1b;
            padding: 3px 8px;
            border-radius: 4px;
            font-weight: 700;
            font-size: 7pt;
        }
        
        .reemplaza-info {
            font-size: 7pt;
            color: #64748b;
        }
        
        .empty-shift {
            padding: 15px;
            text-align: center;
            color: #94a3b8;
            font-style: italic;
            background: #f8fafc;
            border: 1px dashed #e2e8f0;
            margin-top: 0;
        }
        
        .footer {
            position: fixed;
            bottom: 8mm;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 8pt;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 5px;
        }
    </style>
</head>
<body>
    <!-- Marca de agua -->
    <img src="{{ public_path('brand/Logo png Alta Def.png') }}" class="watermark" alt="">

    <div class="header">
        <h1>Reporte de Preventiva</h1>
        <div class="subtitle">{{ $event->title }}</div>
    </div>
    
    <div class="header-info">
        <table>
            <tr>
                <td class="label">Período:</td>
                <td class="value">{{ $event->start_date->format('d/m/Y') }} - {{ $event->end_date->format('d/m/Y') }}</td>
                <td class="label">Zona horaria:</td>
                <td class="value">{{ $event->timezone }}</td>
            </tr>
            <tr>
                <td class="label">Estado:</td>
                <td class="value">{{ strtoupper($event->status) }}</td>
                <td class="label">Generado:</td>
                <td class="value">{{ now()->format('d/m/Y H:i:s') }}</td>
            </tr>
        </table>
    </div>

    <div class="stats">
        <div class="stat-box">
            <div class="number">{{ $totalAssignments }}</div>
            <div class="label">Total Asignaciones</div>
        </div>
        <div class="stat-box">
            <div class="number">{{ $totalTitulares }}</div>
            <div class="label">Titulares</div>
        </div>
        <div class="stat-box">
            <div class="number">{{ $totalReemplazos }}</div>
            <div class="label">Reemplazos</div>
        </div>
        <div class="stat-box">
            <div class="number">{{ $totalRefuerzos }}</div>
            <div class="label">Refuerzos</div>
        </div>
        <div class="stat-box">
            <div class="number">{{ $effectiveAttendance }}</div>
            <div class="label">Asist. Efectiva</div>
        </div>
        <div class="stat-box" style="background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%); border-color: #86efac;">
            <div class="number" style="color: #166534;">{{ $attendanceRate }}%</div>
            <div class="label" style="color: #166534;">Tasa Asistencia</div>
        </div>
    </div>

    @foreach($shifts as $shift)
        @php
            // Calcular asistencias efectivas del turno
            $shiftAssignments = $shift->assignments;
            $shiftTotal = $shiftAssignments->count();
            
            // Mapeo para detectar reemplazos
            $reemplazoIds = [];
            foreach($shiftAssignments as $a) {
                if ($a->reemplaza_a_bombero_id) {
                    $reemplazoIds[] = $a->reemplaza_a_bombero_id;
                }
            }
            
            $shiftEffectiveAttendance = 0;
            foreach($shiftAssignments as $a) {
                if ($a->es_refuerzo) {
                    if ($a->attendance) $shiftEffectiveAttendance++;
                } elseif ($a->reemplaza_a_bombero_id) {
                    if ($a->attendance) $shiftEffectiveAttendance++;
                } else {
                    // Titular
                    if ($a->attendance) {
                        $shiftEffectiveAttendance++;
                    } elseif (in_array($a->bombero_id, $reemplazoIds)) {
                        // Fue reemplazado, buscar si el reemplazo asistió
                        $reemplazo = $shiftAssignments->firstWhere('reemplaza_a_bombero_id', $a->bombero_id);
                        if ($reemplazo && $reemplazo->attendance) {
                            $shiftEffectiveAttendance++;
                        }
                    }
                }
            }
        @endphp
        
        <div class="shift-header">
            <div>
                <h3>{{ $shift->shift_date->format('d/m/Y') }} - {{ $shift->label ?: 'Turno ' . ($shift->sort_order + 1) }}</h3>
                <div class="meta">Horario: {{ substr($shift->start_time, 0, 5) }} a {{ substr($shift->end_time, 0, 5) }}</div>
            </div>
            <div style="text-align: right;">
                <div style="font-size: 12pt; font-weight: 800;">{{ $shiftEffectiveAttendance }}/{{ $shiftTotal }}</div>
                <div style="font-size: 7pt; opacity: 0.9;">Presentes Efectivos</div>
            </div>
        </div>

        @if($shift->assignments->count() > 0)
            <table>
                <thead>
                    <tr>
                        <th style="width: 20%">Bombero</th>
                        <th style="width: 12%">RUT</th>
                        <th style="width: 10%">Cargo</th>
                        <th style="width: 10%">Tipo</th>
                        <th style="width: 18%">Reemplaza A / Info</th>
                        <th style="width: 10%">Hora Entrada</th>
                        <th style="width: 12%">Asistencia</th>
                        <th style="width: 8%">Confirm.</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($shift->assignments->sortByDesc('attendance') as $assignment)
                        @php
                            $f = $assignment->firefighter;
                            $hasAttendance = (bool) $assignment->attendance;
                            $esReemplazo = (bool) $assignment->reemplaza_a_bombero_id;
                            $reemplazaA = $assignment->replacedFirefighter;
                            $fueReemplazado = !$assignment->es_refuerzo && !$esReemplazo && in_array($assignment->bombero_id, $reemplazoIds);
                            $reemplazoQueLoCubre = $fueReemplazado ? $shiftAssignments->firstWhere('reemplaza_a_bombero_id', $assignment->bombero_id) : null;
                        @endphp
                        <tr>
                            <td>
                                <div class="firefighter-name">{{ $f?->apellido_paterno ?? 'N/A' }}</div>
                                <div class="firefighter-sub">{{ $f?->nombres ?? '' }}</div>
                            </td>
                            <td style="font-family: monospace; font-size: 8pt;">{{ $f?->rut ?? 'N/A' }}</td>
                            <td>{{ $f?->cargo_texto ?? '-' }}</td>
                            <td>
                                @if($assignment->es_refuerzo)
                                    <span class="badge badge-refuerzo">Refuerzo</span>
                                @elseif($esReemplazo)
                                    <span class="badge badge-reemplazo">Reemplazo</span>
                                @else
                                    <span class="badge badge-titular">Titular</span>
                                @endif
                            </td>
                            <td>
                                @if($esReemplazo && $reemplazaA)
                                    <span class="reemplaza-info">
                                        Reemplaza a:<br>
                                        <strong>{{ $reemplazaA->apellido_paterno }}, {{ $reemplazaA->nombres }}</strong>
                                    </span>
                                @elseif($fueReemplazado && $reemplazoQueLoCubre)
                                    <span class="reemplaza-info">
                                        <span style="color: #dc2626;">Reemplazado por:</span><br>
                                        <strong>{{ $reemplazoQueLoCubre->firefighter?->apellido_paterno }}, {{ $reemplazoQueLoCubre->firefighter?->nombres }}</strong>
                                    </span>
                                @else
                                    <span style="color: #94a3b8;">—</span>
                                @endif
                            </td>
                            <td>
                                @if($assignment->entrada_hora)
                                    {{ $assignment->entrada_hora->format('H:i:s') }}
                                @else
                                    <span style="color: #94a3b8;">-</span>
                                @endif
                            </td>
                            <td>
                                @if($hasAttendance)
                                    <span class="status-present">✓ PRESENTE</span>
                                @elseif($fueReemplazado && $reemplazoQueLoCubre && $reemplazoQueLoCubre->attendance)
                                    <span class="status-present">✓ CUBIERTO</span>
                                @elseif($fueReemplazado)
                                    <span class="status-reemplazado">⚠ Sin Reemplazo</span>
                                @else
                                    <span class="status-pending">Pendiente</span>
                                @endif
                            </td>
                            <td>
                                @if($assignment->attendance?->confirmed_at)
                                    {{ $assignment->attendance->confirmed_at->format('H:i') }}
                                @else
                                    <span style="color: #94a3b8;">-</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="empty-shift">Sin asignaciones para este turno.</div>
        @endif
    @endforeach

    <div class="footer">
        GuardiaAPP - Sistema de Gestión de Cuerpo de Bomberos - Reporte generado el {{ now()->format('d/m/Y H:i:s') }}
    </div>
</body>
</html>
