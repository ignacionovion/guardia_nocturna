<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Snapshot Guardia - {{ $guardia?->name ?? 'Guardia' }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; margin: 0; padding: 20px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #1e293b; padding-bottom: 10px; }
        .header h1 { margin: 0; color: #1e293b; font-size: 18px; }
        .header p { margin: 5px 0; color: #64748b; }
        .section { margin-bottom: 20px; }
        .section-title { background: #1e293b; color: white; padding: 8px 12px; font-weight: bold; font-size: 14px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #e2e8f0; padding: 8px; text-align: left; }
        th { background: #f1f5f9; font-weight: bold; }
        .stats-grid { display: flex; justify-content: space-between; margin: 15px 0; }
        .stat-item { text-align: center; padding: 10px; background: #f8fafc; border-radius: 4px; flex: 1; margin: 0 5px; }
        .stat-value { font-size: 20px; font-weight: bold; color: #1e293b; }
        .stat-label { font-size: 10px; color: #64748b; }
        .footer { margin-top: 30px; text-align: center; font-size: 10px; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 10px; }
        .status-badge { padding: 2px 8px; border-radius: 12px; font-size: 10px; font-weight: bold; }
        .status-constituye { background: #d1fae5; color: #065f46; }
        .status-reemplazo { background: #e0e7ff; color: #3730a3; }
        .status-permiso { background: #fef3c7; color: #92400e; }
        .status-ausente { background: #e5e7eb; color: #374151; }
        .status-licencia { background: #dbeafe; color: #1e40af; }
        .status-falta { background: #fee2e2; color: #991b1b; }
        .status-inhabilitado { background: #f3f4f6; color: #6b7280; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Snapshot Guardia Nocturna</h1>
        <p><strong>{{ $guardia?->name ?? 'Guardia' }}</strong></p>
        <p>Generado: {{ $generatedAt->format('d/m/Y H:i') }} por {{ $generatedBy }}</p>
        <p>Turno: {{ $shift?->id ? '#' . $shift->id : 'Sin turno activo' }}</p>
    </div>

    {{-- Estadísticas --}}
    <div class="section">
        <div class="section-title">Resumen de Dotación</div>
        <div class="stats-grid">
            <div class="stat-item">
                <div class="stat-value">{{ $stats['total'] }}</div>
                <div class="stat-label">TOTAL</div>
            </div>
            <div class="stat-item">
                <div class="stat-value">{{ $stats['constituye'] }}</div>
                <div class="stat-label">CONSTITUYE</div>
            </div>
            <div class="stat-item">
                <div class="stat-value">{{ $stats['reemplazo'] }}</div>
                <div class="stat-label">REEMPLAZOS</div>
            </div>
            <div class="stat-item">
                <div class="stat-value">{{ $stats['permiso'] }}</div>
                <div class="stat-label">PERMISOS</div>
            </div>
            <div class="stat-item">
                <div class="stat-value">{{ $stats['ausente'] }}</div>
                <div class="stat-label">AUSENTES</div>
            </div>
            <div class="stat-item">
                <div class="stat-value">{{ $stats['licencia'] }}</div>
                <div class="stat-label">LICENCIAS</div>
            </div>
        </div>
    </div>

    {{-- Listado de Bomberos --}}
    <div class="section">
        <div class="section-title">Dotación Actual</div>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nombre</th>
                    <th>Estado</th>
                    <th>Confirmación</th>
                    <th>Cargo</th>
                    <th>Servicio</th>
                </tr>
            </thead>
            <tbody>
                @forelse($bomberos as $index => $b)
                    @php
                        $statusClass = match($b->attendance_status ?? 'constituye') {
                            'constituye' => 'status-constituye',
                            'reemplazo' => 'status-reemplazo',
                            'permiso' => 'status-permiso',
                            'ausente' => 'status-ausente',
                            'licencia' => 'status-licencia',
                            'falta' => 'status-falta',
                            'inhabilitado' => 'status-inhabilitado',
                            default => 'status-constituye',
                        };
                        $statusLabel = strtoupper($b->attendance_status ?? 'CONSTITUYE');
                        $confirmed = $b->confirmed_at !== null;
                        $needsConfirmation = in_array($b->attendance_status ?? 'constituye', ['constituye', 'reemplazo', 'refuerzo']);
                    @endphp
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ trim(($b->firefighter?->nombres ?? '') . ' ' . ($b->firefighter?->apellido_paterno ?? '')) }}</td>
                        <td><span class="status-badge {{ $statusClass }}">{{ $statusLabel }}</span></td>
                        <td>
                            @if(!$needsConfirmation)
                                <span style="color: #9ca3af; font-size: 10px;">N/A</span>
                            @elseif($confirmed)
                                <span style="color: #059669; font-weight: bold;">✓ CONFIRMADO</span>
                            @else
                                <span style="color: #dc2626; font-weight: bold;">✗ PENDIENTE</span>
                            @endif
                        </td>
                        <td>{{ $b->firefighter?->cargo_texto ?? '-' }}</td>
                        <td>
                            @if($b->firefighter?->fecha_ingreso)
                                {{ \Carbon\Carbon::parse($b->firefighter->fecha_ingreso)->diff(now())->format('%y años %m meses') }}
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align: center;">No hay bomberos en la dotación</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Novedades --}}
    @if($novedades->count() > 0)
    <div class="section">
        <div class="section-title">Novedades del Día ({{ $novedades->count() }})</div>
        <table>
            <thead>
                <tr>
                    <th>Hora</th>
                    <th>Tipo</th>
                    <th>Descripción</th>
                    <th>Registró</th>
                </tr>
            </thead>
            <tbody>
                @foreach($novedades as $n)
                <tr>
                    <td>{{ $n->created_at->format('H:i') }}</td>
                    <td>{{ $n->tipo_novedad ?? 'General' }}</td>
                    <td>{{ \Illuminate\Support\Str::limit($n->observaciones, 60) }}</td>
                    <td>{{ $n->creator?->name ?? 'Sistema' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- Emergencias --}}
    @if($emergencias->count() > 0)
    <div class="section">
        <div class="section-title">Emergencias del Día ({{ $emergencias->count() }})</div>
        <table>
            <thead>
                <tr>
                    <th>Hora</th>
                    <th>Clave</th>
                    <th>Guardia</th>
                    <th>Unidades</th>
                </tr>
            </thead>
            <tbody>
                @foreach($emergencias as $e)
                <tr>
                    <td>{{ $e->created_at->format('H:i') }}</td>
                    <td>{{ $e->key?->code ?? 'Sin clave' }}</td>
                    <td>{{ $e->guardia?->name ?? '-' }}</td>
                    <td>{{ $e->units->pluck('name')->implode(', ') ?: '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- Academias --}}
    @if($academias->count() > 0)
    <div class="section">
        <div class="section-title">Academias Activas ({{ $academias->count() }})</div>
        <table>
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Fecha Inicio</th>
                    <th>Fecha Fin</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                @foreach($academias as $a)
                <tr>
                    <td>{{ $a->title ?? 'Academia' }}</td>
                    <td>{{ $a->start_date?->format('d/m/Y') ?? '-' }}</td>
                    <td>{{ $a->end_date?->format('d/m/Y') ?? '-' }}</td>
                    <td>{{ ucfirst($a->status) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <div class="footer">
        <p>Sistema de Gestión de Guardia Nocturna - Cuerpo de Bomberos</p>
        <p>Este documento fue generado automáticamente el {{ $generatedAt->format('d/m/Y H:i:s') }}</p>
    </div>
</body>
</html>
