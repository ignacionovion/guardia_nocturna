<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Camas - Guardia Nocturna</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #1e293b;
            background: #ffffff;
        }
        
        .header {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: white;
            padding: 30px;
            text-align: center;
            border-bottom: 4px solid #dc2626;
        }
        
        .header h1 {
            font-size: 24px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 8px;
        }
        
        .header .subtitle {
            font-size: 12px;
            opacity: 0.9;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .header .datetime {
            font-size: 11px;
            opacity: 0.8;
            margin-top: 10px;
        }
        
        .summary {
            display: flex;
            justify-content: space-around;
            padding: 25px 20px;
            background: #f8fafc;
            border-bottom: 2px solid #e2e8f0;
        }
        
        .summary-item {
            text-align: center;
        }
        
        .summary-item .number {
            font-size: 32px;
            font-weight: 800;
            display: block;
            margin-bottom: 5px;
        }
        
        .summary-item .label {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #64748b;
            font-weight: 600;
        }
        
        .summary-item.available .number { color: #10b981; }
        .summary-item.occupied .number { color: #3b82f6; }
        .summary-item.maintenance .number { color: #f59e0b; }
        
        .content {
            padding: 25px 30px;
        }
        
        .section-title {
            font-size: 14px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #0f172a;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 2px solid #dc2626;
        }
        
        .beds-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
        }
        
        .bed-card {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 12px;
            background: #ffffff;
        }
        
        .bed-card.occupied {
            border-left: 4px solid #3b82f6;
            background: #eff6ff;
        }
        
        .bed-card.available {
            border-left: 4px solid #10b981;
            background: #ecfdf5;
        }
        
        .bed-card.maintenance {
            border-left: 4px solid #f59e0b;
            background: #fffbeb;
        }
        
        .bed-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }
        
        .bed-number {
            font-size: 16px;
            font-weight: 800;
            color: #0f172a;
        }
        
        .bed-status {
            font-size: 9px;
            padding: 3px 8px;
            border-radius: 12px;
            text-transform: uppercase;
            font-weight: 700;
            letter-spacing: 0.5px;
        }
        
        .bed-status.available {
            background: #10b981;
            color: white;
        }
        
        .bed-status.occupied {
            background: #3b82f6;
            color: white;
        }
        
        .bed-status.maintenance {
            background: #f59e0b;
            color: white;
        }
        
        .bed-info {
            font-size: 10px;
            color: #64748b;
        }
        
        .bed-info .firefighter {
            font-weight: 700;
            color: #1e293b;
            font-size: 11px;
        }
        
        .footer {
            background: #0f172a;
            color: white;
            padding: 15px 30px;
            font-size: 9px;
            text-align: center;
            position: fixed;
            bottom: 0;
            width: 100%;
        }
        
        .footer .generated-by {
            opacity: 0.8;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .stats-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        
        .stats-table th,
        .stats-table td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .stats-table th {
            background: #f1f5f9;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 700;
            color: #475569;
        }
        
        .stats-table td {
            font-size: 11px;
        }
        
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
        }
        
        .badge-available { background: #d1fae5; color: #065f46; }
        .badge-occupied { background: #dbeafe; color: #1e40af; }
        .badge-maintenance { background: #fef3c7; color: #92400e; }
    </style>
</head>
<body>
    <div class="header">
        <h1>🏨 Reporte de Camas</h1>
        <div class="subtitle">Sistema de Guardia Nocturna</div>
        <div class="datetime">
            Generado: {{ $generatedAt->format('d/m/Y H:i') }} | Por: {{ $generatedBy }}
        </div>
    </div>
    
    <div class="summary">
        <div class="summary-item available">
            <span class="number">{{ $availableCount }}</span>
            <span class="label">Disponibles</span>
        </div>
        <div class="summary-item occupied">
            <span class="number">{{ $occupiedCount }}</span>
            <span class="label">Ocupadas</span>
        </div>
        <div class="summary-item maintenance">
            <span class="number">{{ $maintenanceCount }}</span>
            <span class="label">En Mantención</span>
        </div>
    </div>
    
    <div class="content">
        <div class="section-title">📋 Detalle de Camas</div>
        
        <div class="beds-grid">
            @foreach($beds as $bed)
                <div class="bed-card {{ $bed->status }}">
                    <div class="bed-header">
                        <span class="bed-number">Cama #{{ $bed->number }}</span>
                        <span class="bed-status {{ $bed->status }}">
                            @if($bed->status === 'available')
                                Disponible
                            @elseif($bed->status === 'occupied')
                                Ocupada
                            @else
                                Mantención
                            @endif
                        </span>
                    </div>
                    
                    <div class="bed-info">
                        @if($bed->currentAssignment && $bed->currentAssignment->firefighter)
                            <div class="firefighter">
                                {{ $bed->currentAssignment->firefighter->nombres }} 
                                {{ $bed->currentAssignment->firefighter->apellido_paterno }}
                            </div>
                            <div>RUT: {{ $bed->currentAssignment->firefighter->rut ?? 'N/A' }}</div>
                            <div>Asignada: {{ $bed->currentAssignment->assigned_at?->format('d/m/Y H:i') ?? 'N/A' }}</div>
                        @else
                            <div>{{ $bed->description ?? 'Sin descripción' }}</div>
                            @if($bed->status === 'maintenance')
                                <div style="color: #f59e0b; font-weight: 600;">En mantención</div>
                            @endif
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
        
        <div class="section-title" style="margin-top: 30px;">📊 Resumen General</div>
        
        <table class="stats-table">
            <thead>
                <tr>
                    <th>Estado</th>
                    <th>Cantidad</th>
                    <th>Porcentaje</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><span class="badge badge-available">Disponibles</span></td>
                    <td>{{ $availableCount }}</td>
                    <td>{{ $beds->count() > 0 ? round(($availableCount / $beds->count()) * 100, 1) : 0 }}%</td>
                </tr>
                <tr>
                    <td><span class="badge badge-occupied">Ocupadas</span></td>
                    <td>{{ $occupiedCount }}</td>
                    <td>{{ $beds->count() > 0 ? round(($occupiedCount / $beds->count()) * 100, 1) : 0 }}%</td>
                </tr>
                <tr>
                    <td><span class="badge badge-maintenance">En Mantención</span></td>
                    <td>{{ $maintenanceCount }}</td>
                    <td>{{ $beds->count() > 0 ? round(($maintenanceCount / $beds->count()) * 100, 1) : 0 }}%</td>
                </tr>
                <tr style="font-weight: 700; background: #f8fafc;">
                    <td>Total</td>
                    <td>{{ $beds->count() }}</td>
                    <td>100%</td>
                </tr>
            </tbody>
        </table>
    </div>
    
    <div class="footer">
        <div class="generated-by">
            Sistema de Gestión de Guardia Nocturna | Reporte generado automáticamente
        </div>
    </div>
</body>
</html>
