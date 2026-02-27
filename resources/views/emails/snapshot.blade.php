{{-- resources/views/emails/snapshot.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .header { background: #1e293b; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; }
        .stats { display: flex; justify-content: space-around; margin: 20px 0; }
        .stat-box { background: #f1f5f9; padding: 15px; border-radius: 8px; text-align: center; flex: 1; margin: 0 10px; }
        .stat-value { font-size: 24px; font-weight: bold; color: #1e293b; }
        .stat-label { font-size: 12px; color: #64748b; }
        .footer { background: #f8fafc; padding: 15px; text-align: center; font-size: 12px; color: #64748b; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Guardia Nocturna - Snapshot</h1>
        <p>{{ $guardia?->name ?? 'Guardia' }} - {{ $generatedAt->format('d/m/Y H:i') }}</p>
    </div>
    
    <div class="content">
        <p>Estimados,</p>
        <p>Se adjunta el snapshot del estado actual de la guardia, generado por <strong>{{ $generatedBy }}</strong>.</p>
        
        <h3>Resumen de Dotación</h3>
        <div class="stats">
            <div class="stat-box">
                <div class="stat-value">{{ $stats['total'] }}</div>
                <div class="stat-label">Total</div>
            </div>
            <div class="stat-box">
                <div class="stat-value">{{ $stats['constituye'] }}</div>
                <div class="stat-label">Constituye</div>
            </div>
            <div class="stat-box">
                <div class="stat-value">{{ $stats['reemplazo'] }}</div>
                <div class="stat-label">Reemplazos</div>
            </div>
            <div class="stat-box">
                <div class="stat-value">{{ $stats['permiso'] + $stats['ausente'] + $stats['licencia'] }}</div>
                <div class="stat-label">No Presentes</div>
            </div>
        </div>
        
        <h3>Actividad del Día</h3>
        <ul>
            <li><strong>Novedades:</strong> {{ $novedadesCount }} registradas</li>
            <li><strong>Emergencias:</strong> {{ $emergenciasCount }} atendidas</li>
            <li><strong>Academias:</strong> {{ $academiasCount }} activas</li>
        </ul>
        
        <p>El detalle completo se encuentra en el PDF adjunto.</p>
    </div>
    
    <div class="footer">
        <p>Sistema de Gestión de Guardia Nocturna - Cuerpo de Bomberos</p>
        <p>Este es un correo automático, por favor no responda a este mensaje.</p>
    </div>
</body>
</html>
