<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte de Conductores - {{ $month }}/{{ $year }}</title>
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
        .footer { margin-top: 20px; text-align: center; font-size: 9px; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 8px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Reporte de Conductores</h1>
        <p>Mes: {{ \Carbon\Carbon::create()->month($month)->locale('es')->monthName }} {{ $year }}</p>
        <p>Total Conductores: {{ $total }}</p>
    </div>

    {{-- Listado de Conductores --}}
    <div class="section">
        <div class="section-title">Actividad del Mes</div>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nombre</th>
                    <th>Guardia</th>
                    <th class="text-center">Turnos Presente</th>
                    <th class="text-center">Días Únicos</th>
                </tr>
            </thead>
            <tbody>
                @foreach($drivers as $index => $d)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $d['name'] }}</td>
                    <td>{{ $d['guardia'] }}</td>
                    <td class="text-center">{{ $d['present_shifts'] }}</td>
                    <td class="text-center">{{ $d['unique_days'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="footer">
        <p>Sistema de Gestión de Guardia Nocturna - Cuerpo de Bomberos</p>
        <p>Generado el {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>
</body>
</html>
