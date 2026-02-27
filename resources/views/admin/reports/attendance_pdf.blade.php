<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte de Asistencia - {{ $from->format('d/m/Y') }} al {{ $to->format('d/m/Y') }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; margin: 0; padding: 15px; }
        .header { text-align: center; margin-bottom: 15px; border-bottom: 2px solid #1e293b; padding-bottom: 10px; }
        .header h1 { margin: 0; color: #1e293b; font-size: 16px; }
        .header p { margin: 3px 0; color: #64748b; font-size: 10px; }
        .stats-row { display: flex; justify-content: space-between; margin-bottom: 15px; }
        .stat-box { text-align: center; padding: 8px; background: #f8fafc; border-radius: 4px; flex: 1; margin: 0 3px; }
        .stat-value { font-size: 16px; font-weight: bold; }
        .stat-label { font-size: 9px; color: #64748b; }
        .section { margin-bottom: 15px; }
        .section-title { background: #1e293b; color: white; padding: 6px 10px; font-weight: bold; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; font-size: 10px; }
        th, td { border: 1px solid #e2e8f0; padding: 5px; text-align: left; }
        th { background: #f1f5f9; font-weight: bold; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .footer { margin-top: 20px; text-align: center; font-size: 9px; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 8px; }
        .percentage { font-weight: bold; }
        .percentage-high { color: #059669; }
        .percentage-medium { color: #d97706; }
        .percentage-low { color: #dc2626; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Reporte de Asistencia</h1>
        <p>Período: {{ $from->format('d/m/Y') }} - {{ $to->format('d/m/Y') }}</p>
        <p>Guardia: {{ $activeGuardia?->name ?? ($guardiaId ? 'Guardia #' . $guardiaId : 'Todas') }}</p>
    </div>

    {{-- Resumen General --}}
    <div class="stats-row">
        <div class="stat-box">
            <div class="stat-value" style="color: #059669;">{{ $generalStats['fulfilled'] ?? 0 }}</div>
            <div class="stat-label">CUMPLIDOS</div>
        </div>
        <div class="stat-box">
            <div class="stat-value" style="color: #dc2626;">{{ $generalStats['absences'] ?? 0 }}</div>
            <div class="stat-label">AUSENCIAS</div>
        </div>
        <div class="stat-box">
            <div class="stat-value" style="color: #d97706;">{{ $generalStats['permissions'] ?? 0 }}</div>
            <div class="stat-label">PERMISOS</div>
        </div>
        <div class="stat-box">
            <div class="stat-value" style="color: #2563eb;">{{ $generalStats['licenses'] ?? 0 }}</div>
            <div class="stat-label">LICENCIAS</div>
        </div>
        <div class="stat-box">
            <div class="stat-value" style="color: #64748b;">{{ $generalStats['disabled'] ?? 0 }}</div>
            <div class="stat-label">INHABILITADOS</div>
        </div>
        <div class="stat-box">
            <div class="stat-value">{{ $generalPercentage }}%</div>
            <div class="stat-label">CUMPLIMIENTO</div>
        </div>
    </div>

    {{-- Estadísticas por Guardia --}}
    @if($view === 'general' && count($guardiaComparison) > 0)
    <div class="section">
        <div class="section-title">Comparación por Guardia</div>
        <table>
            <thead>
                <tr>
                    <th>Guardia</th>
                    <th class="text-center">Personal</th>
                    <th class="text-center">Cumplidos</th>
                    <th class="text-center">Total Turnos</th>
                    <th class="text-center">%</th>
                </tr>
            </thead>
            <tbody>
                @foreach($guardiaComparison as $g)
                @php
                    $pctClass = $g['percentage'] >= 80 ? 'percentage-high' : ($g['percentage'] >= 50 ? 'percentage-medium' : 'percentage-low');
                @endphp
                <tr>
                    <td>{{ $g['name'] }}</td>
                    <td class="text-center">{{ $g['personnel'] }}</td>
                    <td class="text-center">{{ $g['fulfilled'] }}</td>
                    <td class="text-center">{{ $g['total'] }}</td>
                    <td class="text-center percentage {{ $pctClass }}">{{ $g['percentage'] }}%</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- Estadísticas Detalladas por Bombero --}}
    <div class="section">
        <div class="section-title">Detalle por Bombero</div>
        <table>
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Nombre</th>
                    <th>Guardia</th>
                    <th class="text-center">Turnos</th>
                    <th class="text-center">Cumplidos</th>
                    <th class="text-center">Ausencias</th>
                    <th class="text-center">Permisos</th>
                    <th class="text-center">%</th>
                </tr>
            </thead>
            <tbody>
                @foreach($stats as $s)
                @php
                    $pctClass = $s['percentage'] >= 80 ? 'percentage-high' : ($s['percentage'] >= 50 ? 'percentage-medium' : 'percentage-low');
                @endphp
                <tr>
                    <td>{{ $s['code'] }}</td>
                    <td>{{ $s['name'] }}</td>
                    <td>{{ $s['guardia_name'] }}</td>
                    <td class="text-center">{{ $s['shift'] }}</td>
                    <td class="text-center">{{ $s['fulfilled'] }}</td>
                    <td class="text-center">{{ $s['absences'] }}</td>
                    <td class="text-center">{{ $s['permissions'] }}</td>
                    <td class="text-center percentage {{ $pctClass }}">{{ $s['percentage'] }}%</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Rankings --}}
    @if(count($rankings) > 0)
    <div class="section">
        <div class="section-title">Rankings Destacados</div>
        <table>
            <thead>
                <tr>
                    <th>Categoría</th>
                    <th>Bombero</th>
                    <th class="text-center">Valor</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rankings as $r)
                <tr>
                    <td>{{ $r['emoji'] }} {{ $r['label'] }}</td>
                    <td>{{ $r['name'] }}</td>
                    <td class="text-center">{{ $r['value'] }} {{ $r['unit'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <div class="footer">
        <p>Sistema de Gestión de Guardia Nocturna - Cuerpo de Bomberos</p>
        <p>Generado el {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>
</body>
</html>
