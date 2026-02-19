<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte Preventiva - {{ $event->title }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 10pt; margin: 20px; }
        h1 { font-size: 16pt; margin-bottom: 10px; }
        h2 { font-size: 12pt; margin: 15px 0 10px; border-bottom: 1px solid #333; padding-bottom: 5px; }
        .header-info { margin-bottom: 20px; }
        .header-info table { width: 100%; border-collapse: collapse; }
        .header-info td { padding: 3px 0; }
        .header-info .label { font-weight: bold; color: #666; width: 150px; }
        .stats { display: flex; gap: 20px; margin-bottom: 20px; }
        .stat-box { border: 1px solid #ddd; padding: 10px; text-align: center; flex: 1; }
        .stat-box .number { font-size: 18pt; font-weight: bold; color: #333; }
        .stat-box .label { font-size: 8pt; color: #666; text-transform: uppercase; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background-color: #f5f5f5; border: 1px solid #ddd; padding: 8px; text-align: left; font-size: 9pt; text-transform: uppercase; }
        td { border: 1px solid #ddd; padding: 6px 8px; font-size: 9pt; }
        tr:nth-child(even) { background-color: #fafafa; }
        .present { background-color: #d4edda !important; }
        .absent { background-color: #f8f9fa; }
        .badge { display: inline-block; padding: 2px 6px; border-radius: 3px; font-size: 8pt; font-weight: bold; }
        .badge-refuerzo { background-color: #cce5ff; color: #004085; }
        .badge-titular { background-color: #e2e3e5; color: #383d41; }
        .badge-present { background-color: #d4edda; color: #155724; }
        .badge-pending { background-color: #fff3cd; color: #856404; }
        .shift-header { background-color: #f8f9fa; padding: 8px; margin-top: 15px; border: 1px solid #ddd; }
        .shift-header h3 { margin: 0; font-size: 11pt; }
        .shift-header .meta { font-size: 8pt; color: #666; }
    </style>
</head>
<body>
    <h1><i class="fas fa-shield-alt"></i> Reporte de Preventiva</h1>
    
    <div class="header-info">
        <table>
            <tr>
                <td class="label">Evento:</td>
                <td>{{ $event->title }}</td>
            </tr>
            <tr>
                <td class="label">Período:</td>
                <td>{{ $event->start_date->format('d/m/Y') }} - {{ $event->end_date->format('d/m/Y') }}</td>
            </tr>
            <tr>
                <td class="label">Zona horaria:</td>
                <td>{{ $event->timezone }}</td>
            </tr>
            <tr>
                <td class="label">Generado:</td>
                <td>{{ now()->format('d/m/Y H:i:s') }}</td>
            </tr>
        </table>
    </div>

    <div class="stats">
        <div class="stat-box">
            <div class="number">{{ $totalAssignments }}</div>
            <div class="label">Total Asignaciones</div>
        </div>
        <div class="stat-box">
            <div class="number">{{ $totalAttendance }}</div>
            <div class="label">Asistencias</div>
        </div>
        <div class="stat-box">
            <div class="number">{{ $totalRefuerzos }}</div>
            <div class="label">Refuerzos</div>
        </div>
        <div class="stat-box">
            <div class="number">{{ $totalAssignments > 0 ? round(($totalAttendance / $totalAssignments) * 100, 1) : 0 }}%</div>
            <div class="label">Tasa Asistencia</div>
        </div>
    </div>

    @foreach($shifts as $shift)
        @php
            $shiftAssignments = $shift->assignments->count();
            $shiftAttendance = $shift->assignments->whereNotNull('attendance')->count();
            $shiftRefuerzos = $shift->assignments->where('es_refuerzo', true)->count();
        @endphp
        
        <div class="shift-header">
            <h3>{{ $shift->shift_date->format('d/m/Y') }} - {{ $shift->label ?: 'Turno ' . ($shift->sort_order + 1) }}</h3>
            <div class="meta">
                Horario: {{ substr($shift->start_time, 0, 5) }} a {{ substr($shift->end_time, 0, 5) }} | 
                Presentes: {{ $shiftAttendance }}/{{ $shiftAssignments }}
                @if($shiftRefuerzos > 0) | Refuerzos: {{ $shiftRefuerzos }} @endif
            </div>
        </div>

        @if($shift->assignments->count() > 0)
            <table>
                <thead>
                    <tr>
                        <th style="width: 25%">Bombero</th>
                        <th style="width: 12%">RUT</th>
                        <th style="width: 15%">Cargo</th>
                        <th style="width: 10%">Tipo</th>
                        <th style="width: 12%">Hora Entrada</th>
                        <th style="width: 15%">Asistencia</th>
                        <th style="width: 11%">Hora Confirm.</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($shift->assignments->sortByDesc('attendance') as $assignment)
                        @php
                            $f = $assignment->firefighter;
                            $hasAttendance = (bool) $assignment->attendance;
                        @endphp
                        <tr class="{{ $hasAttendance ? 'present' : 'absent' }}">
                            <td>
                                <strong>{{ $f?->apellido_paterno ?? 'N/A' }}</strong><br>
                                <small style="color: #666;">{{ $f?->nombres ?? '' }}</small>
                            </td>
                            <td style="font-family: monospace;">{{ $f?->rut ?? 'N/A' }}</td>
                            <td>{{ $f?->cargo_texto ?? '-' }}</td>
                            <td>
                                @if($assignment->es_refuerzo)
                                    <span class="badge badge-refuerzo">REFUERZO</span>
                                @else
                                    <span class="badge badge-titular">TITULAR</span>
                                @endif
                            </td>
                            <td>
                                @if($assignment->entrada_hora)
                                    {{ $assignment->entrada_hora->format('H:i:s') }}
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @if($hasAttendance)
                                    <span class="badge badge-present">✓ PRESENTE</span>
                                @else
                                    <span class="badge badge-pending">Pendiente</span>
                                @endif
                            </td>
                            <td>
                                @if($assignment->attendance?->confirmed_at)
                                    {{ $assignment->attendance->confirmed_at->format('H:i:s') }}
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p style="color: #666; font-style: italic;">Sin asignaciones para este turno.</p>
        @endif
    @endforeach

    <div style="margin-top: 30px; padding-top: 10px; border-top: 1px solid #ddd; font-size: 8pt; color: #666; text-align: center;">
        GuardiaAPP - Sistema de Gestión de Cuerpo de Bomberos - Reporte generado el {{ now()->format('d/m/Y H:i:s') }}
    </div>
</body>
</html>
