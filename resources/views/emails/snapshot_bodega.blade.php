<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #1e293b; color: white; padding: 20px; text-align: center; }
        .content { background: #f8fafc; padding: 20px; border: 1px solid #e2e8f0; }
        .stats { display: flex; justify-content: space-around; margin: 20px 0; }
        .stat { text-align: center; }
        .stat-value { font-size: 24px; font-weight: bold; color: #059669; }
        .stat-label { font-size: 12px; color: #64748b; }
        .footer { text-align: center; font-size: 12px; color: #94a3b8; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Snapshot Bodega - {{ $bodega->nombre }}</h2>
            <p>{{ $generatedAt->format('d/m/Y H:i') }}</p>
        </div>
        
        <div class="content">
            <p>Se adjunta el snapshot del inventario de <strong>{{ $bodega->nombre }}</strong>.</p>
            
            <div class="stats">
                <div class="stat">
                    <div class="stat-value">{{ $itemsCount }}</div>
                    <div class="stat-label">Ítems en Stock</div>
                </div>
                <div class="stat">
                    <div class="stat-value">{{ $movimientosCount }}</div>
                    <div class="stat-label">Movimientos Hoy</div>
                </div>
            </div>
            
            <p><strong>Generado por:</strong> {{ $generatedBy }}</p>
            <p><strong>Fecha:</strong> {{ $generatedAt->format('d/m/Y H:i') }}</p>
        </div>
        
        <div class="footer">
            <p>Sistema de Gestión de Guardia Nocturna</p>
            <p>Este es un email automático, por favor no responder.</p>
        </div>
    </div>
</body>
</html>
