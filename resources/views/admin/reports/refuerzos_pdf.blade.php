<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Refuerzos</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 10pt;
            color: #334155;
            line-height: 1.4;
        }
        
        /* Header Profesional */
        .header {
            background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
            color: white;
            padding: 25px 30px;
            margin-bottom: 20px;
        }
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header-left h1 {
            font-size: 24pt;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 5px;
        }
        .header-left p {
            font-size: 10pt;
            opacity: 0.9;
        }
        .header-right {
            text-align: right;
        }
        .header-right .logo {
            font-size: 14pt;
            font-weight: 800;
            margin-bottom: 5px;
        }
        .header-right .date {
            font-size: 9pt;
            opacity: 0.8;
        }
        
        /* Stats Cards */
        .stats-container {
            display: flex;
            gap: 15px;
            margin: 20px 30px;
        }
        .stat-card {
            flex: 1;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
        }
        .stat-value {
            font-size: 24pt;
            font-weight: 800;
            color: #0ea5e9;
            margin-bottom: 5px;
        }
        .stat-label {
            font-size: 8pt;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        /* Info Box */
        .info-box {
            background: #f0f9ff;
            border-left: 4px solid #0ea5e9;
            padding: 15px 20px;
            margin: 20px 30px;
            border-radius: 0 8px 8px 0;
        }
        .info-box p {
            font-size: 9pt;
            color: #0369a1;
        }
        .info-box strong {
            color: #0c4a6e;
        }
        
        /* Table Styles */
        .table-container {
            margin: 20px 30px;
        }
        .table-title {
            font-size: 12pt;
            font-weight: 800;
            color: #1e293b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 2px solid #0ea5e9;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9pt;
        }
        thead {
            background: #0ea5e9;
            color: white;
        }
        th {
            padding: 10px 8px;
            text-align: left;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 8pt;
            letter-spacing: 0.3px;
        }
        th.center { text-align: center; }
        
        tbody tr {
            border-bottom: 1px solid #e2e8f0;
        }
        tbody tr:nth-child(even) {
            background: #f8fafc;
        }
        tbody tr:hover {
            background: #f0f9ff;
        }
        td {
            padding: 8px;
            vertical-align: middle;
        }
        td.center { text-align: center; }
        
        /* Badges */
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 7pt;
            font-weight: 700;
            text-transform: uppercase;
        }
        .badge-sky { background: #e0f2fe; color: #0369a1; }
        .badge-emerald { background: #d1fae5; color: #065f46; }
        .badge-amber { background: #fef3c7; color: #92400e; }
        .badge-slate { background: #f1f5f9; color: #475569; }
        
        /* Avatar */
        .avatar {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 28px;
            height: 28px;
            background: #e0f2fe;
            color: #0284c7;
            border-radius: 50%;
            font-weight: 700;
            font-size: 9pt;
            margin-right: 8px;
        }
        
        /* Footer */
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
            padding: 10px 30px;
            font-size: 8pt;
            color: #64748b;
            display: flex;
            justify-content: space-between;
        }
        
        /* Page break */
        .page-break {
            page-break-after: always;
        }
        
        /* Summary Section */
        .summary-grid {
            display: flex;
            gap: 20px;
            margin: 20px 30px;
        }
        .summary-box {
            flex: 1;
            padding: 20px;
            border-radius: 8px;
            color: white;
        }
        .summary-box.sky {
            background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
        }
        .summary-box.violet {
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
        }
        .summary-box.emerald {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }
        .summary-title {
            font-size: 8pt;
            font-weight: 700;
            text-transform: uppercase;
            opacity: 0.8;
            margin-bottom: 8px;
        }
        .summary-value {
            font-size: 20pt;
            font-weight: 800;
        }
        .summary-label {
            font-size: 8pt;
            opacity: 0.9;
            margin-top: 3px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="header-content">
            <div class="header-left">
                <h1>Reporte de Refuerzos</h1>
                <p>Período: {{ $from->format('d/m/Y') }} — {{ $to->format('d/m/Y') }}</p>
            </div>
            <div class="header-right">
                <div class="logo">GUARDIA APP</div>
                <div class="date">Generado: {{ $generatedAt->format('d/m/Y H:i') }}</div>
            </div>
        </div>
    </div>
    
    <!-- Stats Cards -->
    <div class="stats-container">
        <div class="stat-card">
            <div class="stat-value">{{ $total }}</div>
            <div class="stat-label">Total Refuerzos</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $refuerzos->pluck('firefighter_id')->unique()->count() }}</div>
            <div class="stat-label">Voluntarios Únicos</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $refuerzos->pluck('firefighter.guardia_id')->unique()->count() }}</div>
            <div class="stat-label">Guardias Activas</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $refuerzos->where('attendance_status', 'cumplido')->count() }}</div>
            <div class="stat-label">Asistencias OK</div>
        </div>
    </div>
    
    <!-- Info Box -->
    <div class="info-box">
        <p><strong>Filtro aplicado:</strong> Guardia: {{ $guardiaNombre }} | Período: {{ $from->format('d/m/Y') }} al {{ $to->format('d/m/Y') }}</p>
    </div>
    
    <!-- Summary by Guardia -->
    <div class="table-container">
        <div class="table-title">Resumen por Guardia</div>
        <table>
            <thead>
                <tr>
                    <th>Guardia</th>
                    <th class="center">Total Refuerzos</th>
                    <th class="center">Voluntarios Únicos</th>
                    <th class="center">Asistencias</th>
                    <th class="center">% Cumplimiento</th>
                </tr>
            </thead>
            <tbody>
                @foreach($refuerzos->groupBy(fn($r) => $r->firefighter?->guardia?->name ?? 'Sin Guardia')->sortDesc() as $guardiaName => $group)
                @php
                    $total = $group->count();
                    $asistencias = $group->where('attendance_status', 'cumplido')->count();
                    $pct = $total > 0 ? round(($asistencias / $total) * 100, 1) : 0;
                @endphp
                <tr>
                    <td>
                        <span class="badge badge-sky">{{ $guardiaName }}</span>
                    </td>
                    <td class="center"><strong>{{ $total }}</strong></td>
                    <td class="center">{{ $group->pluck('firefighter_id')->unique()->count() }}</td>
                    <td class="center">{{ $asistencias }}</td>
                    <td class="center">
                        <span class="badge {{ $pct >= 80 ? 'badge-emerald' : ($pct >= 60 ? 'badge-amber' : 'badge-slate') }}">
                            {{ $pct }}%
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    
    <!-- Detailed List -->
    <div class="table-container">
        <div class="table-title">Detalle de Refuerzos</div>
        <table>
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Guardia</th>
                    <th>Voluntario</th>
                    <th class="center">Turno</th>
                    <th class="center">Estado</th>
                </tr>
            </thead>
            <tbody>
                @foreach($refuerzos->sortBy('start_time') as $refuerzo)
                <tr>
                    <td>
                        {{ $refuerzo->start_time->format('d/m/Y') }}<br>
                        <small style="color: #64748b;">{{ $refuerzo->start_time->locale('es')->dayName }}</small>
                    </td>
                    <td>
                        <span class="badge badge-sky">{{ $refuerzo->firefighter?->guardia?->name ?? 'N/A' }}</span>
                    </td>
                    <td>
                        <div style="display: flex; align-items: center;">
                            <span class="avatar">
                                {{ substr($refuerzo->firefighter?->nombres ?? 'N', 0, 1) }}{{ substr($refuerzo->firefighter?->apellido_paterno ?? 'A', 0, 1) }}
                            </span>
                            <div>
                                <strong>{{ $refuerzo->firefighter?->full_name ?? 'Sin nombre' }}</strong><br>
                                <small style="color: #64748b;">{{ $refuerzo->firefighter?->rut ?? '' }}</small>
                            </div>
                        </div>
                    </td>
                    <td class="center">{{ $refuerzo->shift?->name ?? 'N/A' }}</td>
                    <td class="center">
                        @if($refuerzo->attendance_status === 'cumplido')
                            <span class="badge badge-emerald">✓ Cumplido</span>
                        @elseif($refuerzo->attendance_status === 'ausente')
                            <span class="badge badge-slate">✗ Ausente</span>
                        @else
                            <span class="badge badge-amber">⏳ Pendiente</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    
    <!-- Footer -->
    <div class="footer">
        <span>Guardia Nocturna - Sistema de Gestión</span>
        <span>Página 1 de 1</span>
    </div>
</body>
</html>
