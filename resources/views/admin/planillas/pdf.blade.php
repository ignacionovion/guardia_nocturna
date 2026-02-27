<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Planilla {{ $planilla->unidad }} - {{ $planilla->fecha_revision->format('Y-m-d') }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 10px; }
        h1 { font-size: 16px; text-align: center; }
        h2 { font-size: 12px; background-color: #f0f0f0; padding: 5px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        th { background-color: #f5f5f5; font-weight: bold; }
        .header { text-align: center; margin-bottom: 20px; }
        .info { margin-bottom: 20px; }
        .info p { margin: 3px 0; }
        .check-yes { color: green; font-weight: bold; }
        .check-no { color: red; font-weight: bold; }
        .section { margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>PLANILLA DE REVISIÓN DE NIVELES</h1>
        <h2>Unidad: {{ $planilla->unidad }}</h2>
    </div>
    
    <div class="info">
        <p><strong>Fecha de revisión:</strong> {{ $planilla->fecha_revision->format('d/m/Y H:i') }}</p>
        <p><strong>Estado:</strong> {{ $planilla->estado === 'finalizado' ? 'Finalizado' : 'En edición' }}</p>
        <p><strong>Registrada por:</strong> {{ $planilla->creador?->name ?? trim((string)($planilla->bombero?->nombres ?? '') . ' ' . (string)($planilla->bombero?->apellido_paterno ?? '')) ?: '—' }}</p>
    </div>

    @if(!empty($planilla->data['cabina']))
    <div class="section">
        <h2>SECCIÓN CABINA</h2>
        <table>
            <thead>
                <tr>
                    <th>Ítem</th>
                    <th>Funciona</th>
                    <th>Cantidad</th>
                    <th>Novedades</th>
                </tr>
            </thead>
            <tbody>
                @foreach($planilla->data['cabina'] as $key => $item)
                <tr>
                    <td>{{ $key }}</td>
                    <td class="{{ ($item['funciona'] ?? '') === 'si' ? 'check-yes' : (($item['funciona'] ?? '') === 'no' ? 'check-no' : '') }}">
                        {{ ($item['funciona'] ?? '') === 'si' ? 'Sí' : (($item['funciona'] ?? '') === 'no' ? 'No' : '-') }}
                    </td>
                    <td>{{ $item['cantidad'] ?? '-' }}</td>
                    <td>{{ $item['novedades'] ?? '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    @if(!empty($planilla->data['trauma']))
    <div class="section">
        <h2>SECCIÓN TRAUMA</h2>
        <table>
            <thead>
                <tr>
                    <th>Ítem</th>
                    <th>Funciona</th>
                    <th>Cantidad</th>
                    <th>Novedades</th>
                </tr>
            </thead>
            <tbody>
                @foreach($planilla->data['trauma'] as $key => $item)
                <tr>
                    <td>{{ $key }}</td>
                    <td class="{{ ($item['funciona'] ?? '') === 'si' ? 'check-yes' : (($item['funciona'] ?? '') === 'no' ? 'check-no' : '') }}">
                        {{ ($item['funciona'] ?? '') === 'si' ? 'Sí' : (($item['funciona'] ?? '') === 'no' ? 'No' : '-') }}
                    </td>
                    <td>{{ $item['cantidad'] ?? '-' }}</td>
                    <td>{{ $item['novedades'] ?? '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    @if(!empty($planilla->data['cantidades']))
    <div class="section">
        <h2>SECCIÓN CANTIDADES</h2>
        <table>
            <thead>
                <tr>
                    <th>Ítem</th>
                    <th>Cantidad</th>
                    <th>Novedades</th>
                </tr>
            </thead>
            <tbody>
                @foreach($planilla->data['cantidades'] as $key => $item)
                <tr>
                    <td>{{ $key }}</td>
                    <td>{{ $item['cantidad'] ?? '-' }}</td>
                    <td>{{ $item['novedades'] ?? '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <div style="margin-top: 30px; text-align: center; font-size: 8px; color: #666;">
        Generado el {{ now()->format('d/m/Y H:i') }} - Sistema de Guardia Nocturna
    </div>
</body>
</html>
