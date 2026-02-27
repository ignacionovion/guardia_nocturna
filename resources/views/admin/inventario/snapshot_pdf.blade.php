<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Snapshot Bodega - {{ $bodega->nombre }} - {{ $generatedAt->format('d/m/Y H:i') }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; margin: 0; padding: 15px; }
        .header { text-align: center; margin-bottom: 15px; border-bottom: 2px solid #1e293b; padding-bottom: 10px; }
        .header h1 { margin: 0; color: #1e293b; font-size: 16px; }
        .header p { margin: 3px 0; color: #64748b; font-size: 10px; }
        .stats-row { display: flex; justify-content: space-between; margin-bottom: 15px; }
        .stat-box { text-align: center; padding: 8px; background: #f8fafc; border-radius: 4px; flex: 1; margin: 0 3px; }
        .stat-value { font-size: 16px; font-weight: bold; color: #059669; }
        .stat-label { font-size: 9px; color: #64748b; }
        .section { margin-bottom: 15px; }
        .section-title { background: #1e293b; color: white; padding: 6px 10px; font-weight: bold; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; font-size: 10px; }
        th, td { border: 1px solid #e2e8f0; padding: 5px; text-align: left; }
        th { background: #f1f5f9; font-weight: bold; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .footer { margin-top: 20px; text-align: center; font-size: 9px; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 8px; }
        .badge { display: inline-block; padding: 2px 6px; border-radius: 3px; font-size: 8px; font-weight: bold; text-transform: uppercase; }
        .badge-egreso { background: #fee2e2; color: #dc2626; }
        .badge-ingreso { background: #dcfce7; color: #16a34a; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Snapshot Bodega</h1>
        <p>{{ $bodega->nombre }}</p>
        <p>Generado: {{ $generatedAt->format('d/m/Y H:i') }} por {{ $generatedBy }}</p>
    </div>

    {{-- Resumen --}}
    <div class="stats-row">
        <div class="stat-box">
            <div class="stat-value">{{ $items->count() }}</div>
            <div class="stat-label">ÍTEMS EN STOCK</div>
        </div>
        <div class="stat-box">
            <div class="stat-value">{{ $movimientosHoy->count() }}</div>
            <div class="stat-label">MOVIMIENTOS HOY</div>
        </div>
        <div class="stat-box">
            <div class="stat-value">{{ $movimientosHoy->where('tipo', 'egreso')->count() }}</div>
            <div class="stat-label">RETIROS HOY</div>
        </div>
        <div class="stat-box">
            <div class="stat-value">{{ $movimientosHoy->where('tipo', 'ingreso')->count() }}</div>
            <div class="stat-label">INGRESOS HOY</div>
        </div>
    </div>

    {{-- Stock Actual --}}
    <div class="section">
        <div class="section-title">Stock Actual</div>
        <table>
            <thead>
                <tr>
                    <th>Ítem</th>
                    <th>Categoría</th>
                    <th class="text-center">Unidad</th>
                    <th class="text-right">Stock</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $item)
                <tr>
                    <td>{{ $item->display_name }}</td>
                    <td>{{ $item->categoria ?? '—' }}</td>
                    <td class="text-center">{{ $item->unidad ?? '—' }}</td>
                    <td class="text-right font-bold">{{ $item->stock }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center">No hay ítems activos</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Movimientos del Día --}}
    @if($movimientosHoy->count() > 0)
    <div class="section">
        <div class="section-title">Movimientos del Día</div>
        <table>
            <thead>
                <tr>
                    <th>Hora</th>
                    <th>Tipo</th>
                    <th>Ítem</th>
                    <th class="text-center">Cantidad</th>
                    <th>Bombero</th>
                    <th>Nota</th>
                </tr>
            </thead>
            <tbody>
                @foreach($movimientosHoy as $mov)
                <tr>
                    <td>{{ $mov->created_at->format('H:i') }}</td>
                    <td>
                        <span class="badge {{ $mov->tipo === 'egreso' ? 'badge-egreso' : 'badge-ingreso' }}">
                            {{ strtoupper($mov->tipo) }}
                        </span>
                    </td>
                    <td>{{ $mov->item?->display_name ?? '—' }}</td>
                    <td class="text-center">{{ $mov->cantidad }}</td>
                    <td>{{ $mov->firefighter?->full_name ?? '—' }}</td>
                    <td>{{ $mov->nota ? Str::limit($mov->nota, 30) : '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <div class="footer">
        <p>Sistema de Gestión de Guardia Nocturna - Cuerpo de Bomberos</p>
        <p>Bodega: {{ $bodega->nombre }} | Ubicación: {{ $bodega->ubicacion ?? 'No especificada' }}</p>
    </div>
</body>
</html>
